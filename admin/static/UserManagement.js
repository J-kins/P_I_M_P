/**
 * P.I.M.P - User Management
 * Admin user management functionality
 */

class UserManagement {
    constructor(container, pimp) {
        this.container = container || document.querySelector('.user-management');
        this.pimp = pimp || window.PIMP;
        this.api = window.ApiService;
        this.users = [];
        this.currentPage = 1;
        this.filters = {};
        this.init();
    }

    async init() {
        if (!this.container) {
            console.error('UserManagement: Container not found');
            return;
        }

        this.setupEventHandlers();
        await this.loadUsers();
    }

    setupEventHandlers() {
        // Search
        const searchInput = this.container.querySelector('[data-user-search]');
        if (searchInput) {
            searchInput.addEventListener('input', this.pimp?.debounce((e) => {
                this.searchUsers(e.target.value);
            }, 300) || ((e) => {
                setTimeout(() => this.searchUsers(e.target.value), 300);
            }));
        }

        // Filters
        this.container.querySelectorAll('[data-user-filter]').forEach(filter => {
            filter.addEventListener('change', (e) => {
                const key = filter.getAttribute('data-user-filter');
                this.filters[key] = e.target.value;
                this.loadUsers();
            });
        });

        // Bulk actions
        const bulkActionBtn = this.container.querySelector('[data-bulk-action]');
        if (bulkActionBtn) {
            bulkActionBtn.addEventListener('click', () => this.handleBulkAction());
        }

        // Export button
        const exportBtn = this.container.querySelector('[data-export-users]');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.exportUserList());
        }
    }

    async loadUsers(filters = {}) {
        try {
            this.showLoading();

            this.filters = { ...this.filters, ...filters };
            const response = await this.api.admin.getUsers(this.filters, this.currentPage);

            if (response.success) {
                this.users = response.data.users || response.data || [];
                this.renderUserTable();
                this.renderPagination(response.data.pagination || {});
            } else {
                this.showError(response.message || 'Failed to load users');
            }
        } catch (error) {
            console.error('Error loading users:', error);
            this.showError('Error loading users');
        } finally {
            this.hideLoading();
        }
    }

    renderUserTable() {
        const tableBody = this.container.querySelector('.users-table tbody, [data-users-table]');
        if (!tableBody) return;

        if (this.users.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="100%">No users found</td></tr>';
            return;
        }

        tableBody.innerHTML = this.users.map(user => `
            <tr data-user-id="${user.id}">
                <td>
                    <input type="checkbox" class="user-checkbox" value="${user.id}">
                </td>
                <td>
                    <div class="user-avatar">
                        ${user.avatar_url ? 
                            `<img src="${user.avatar_url}" alt="${user.first_name}">` : 
                            `<i class="fas fa-user-circle"></i>`
                        }
                    </div>
                </td>
                <td>
                    <div class="user-name">${user.first_name} ${user.last_name}</div>
                    <div class="user-email">${user.email}</div>
                </td>
                <td>
                    <span class="user-type-badge user-type-${user.user_type}">
                        ${user.user_type}
                    </span>
                </td>
                <td>
                    <span class="status-badge status-${user.status}">
                        ${user.status}
                    </span>
                </td>
                <td>${this.formatDate(user.created_at)}</td>
                <td>${user.last_login ? this.formatDate(user.last_login) : 'Never'}</td>
                <td>
                    <div class="user-actions">
                        <button class="btn-icon" data-action="view" data-user-id="${user.id}" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon" data-action="edit" data-user-id="${user.id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon" data-action="suspend" data-user-id="${user.id}" title="Suspend">
                            <i class="fas fa-ban"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        // Attach action handlers
        tableBody.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = btn.getAttribute('data-action');
                const userId = btn.getAttribute('data-user-id');
                this.handleUserAction(action, userId);
            });
        });
    }

    async handleUserAction(action, userId) {
        switch (action) {
            case 'view':
                this.viewUser(userId);
                break;
            case 'edit':
                this.editUser(userId);
                break;
            case 'suspend':
                await this.updateUserStatus(userId, 'suspended');
                break;
            case 'activate':
                await this.updateUserStatus(userId, 'active');
                break;
        }
    }

    async updateUserStatus(userId, status) {
        try {
            const response = await this.api.user.updateProfile({
                id: userId,
                status: status
            });

            if (response.success) {
                this.pimp?.showNotification(`User ${status} successfully`, 'success', 2000);
                await this.loadUsers();
            } else {
                this.showError(response.message || 'Failed to update user status');
            }
        } catch (error) {
            console.error('Error updating user status:', error);
            this.showError('Error updating user status');
        }
    }

    async searchUsers(query) {
        if (!query || query.length < 2) {
            this.filters.search = '';
            await this.loadUsers();
            return;
        }

        this.filters.search = query;
        this.currentPage = 1;
        await this.loadUsers();
    }

    viewUser(userId) {
        // Open user details modal or navigate to user page
        const user = this.users.find(u => u.id == userId);
        if (user && typeof window.ModalComponent !== 'undefined') {
            // Show user details in modal
            const modal = document.querySelector('#userDetailsModal');
            if (modal && modal.modalComponent) {
                modal.modalComponent.setContent(this.renderUserDetails(user));
                modal.modalComponent.show();
            }
        }
    }

    editUser(userId) {
        // Navigate to edit page or open edit modal
        window.location.href = `/admin/users/${userId}/edit`;
    }

    renderUserDetails(user) {
        return `
            <div class="user-details">
                <h3>${user.first_name} ${user.last_name}</h3>
                <p><strong>Email:</strong> ${user.email}</p>
                <p><strong>Type:</strong> ${user.user_type}</p>
                <p><strong>Status:</strong> ${user.status}</p>
                <p><strong>Created:</strong> ${this.formatDate(user.created_at)}</p>
                <p><strong>Last Login:</strong> ${user.last_login ? this.formatDate(user.last_login) : 'Never'}</p>
            </div>
        `;
    }

    renderPagination(pagination) {
        const paginationContainer = this.container.querySelector('.pagination, [data-pagination]');
        if (!paginationContainer || !pagination.total_pages) return;

        let html = '<div class="pagination">';
        
        // Previous button
        html += `<button class="pagination-btn" ${this.currentPage === 1 ? 'disabled' : ''} data-page="${this.currentPage - 1}">
            <i class="fas fa-chevron-left"></i>
        </button>`;

        // Page numbers
        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === 1 || i === pagination.total_pages || (i >= this.currentPage - 2 && i <= this.currentPage + 2)) {
                html += `<button class="pagination-btn ${i === this.currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
            } else if (i === this.currentPage - 3 || i === this.currentPage + 3) {
                html += '<span class="pagination-ellipsis">...</span>';
            }
        }

        // Next button
        html += `<button class="pagination-btn" ${this.currentPage === pagination.total_pages ? 'disabled' : ''} data-page="${this.currentPage + 1}">
            <i class="fas fa-chevron-right"></i>
        </button>`;

        html += '</div>';

        paginationContainer.innerHTML = html;

        // Attach page change handlers
        paginationContainer.querySelectorAll('[data-page]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const page = parseInt(e.target.closest('[data-page]').getAttribute('data-page'));
                if (page && page !== this.currentPage) {
                    this.currentPage = page;
                    this.loadUsers();
                }
            });
        });
    }

    async handleBulkAction() {
        const selected = Array.from(this.container.querySelectorAll('.user-checkbox:checked'))
            .map(cb => cb.value);

        if (selected.length === 0) {
            this.pimp?.showNotification('Please select users', 'warning', 2000);
            return;
        }

        const action = this.container.querySelector('[data-bulk-action-select]')?.value;
        if (!action) return;

        // Implement bulk actions
        console.log('Bulk action:', action, selected);
    }

    async exportUserList() {
        try {
            const response = await this.api.admin.getUsers(this.filters, 1, 1000);
            const users = response.data.users || response.data || [];

            const csv = this.convertToCSV(users);
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `users-export-${Date.now()}.csv`;
            link.click();
            URL.revokeObjectURL(url);

            this.pimp?.showNotification('User list exported', 'success', 2000);
        } catch (error) {
            console.error('Error exporting users:', error);
            this.showError('Failed to export user list');
        }
    }

    convertToCSV(data) {
        if (data.length === 0) return '';

        const headers = Object.keys(data[0]);
        const rows = data.map(row => 
            headers.map(header => `"${row[header] || ''}"`).join(',')
        );

        return [headers.join(','), ...rows].join('\n');
    }

    showLoading() {
        const loading = this.container.querySelector('.loading, [data-loading]');
        if (loading) loading.style.display = 'block';
    }

    hideLoading() {
        const loading = this.container.querySelector('.loading, [data-loading]');
        if (loading) loading.style.display = 'none';
    }

    showError(message) {
        if (this.pimp) {
            this.pimp.showNotification(message, 'error', 5000);
        }
    }

    formatDate(date) {
        if (!date) return 'N/A';
        return new Date(date).toLocaleDateString();
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.user-management');
    if (container && !container.userManagement) {
        container.userManagement = new UserManagement(container);
    }
});

// Export
if (typeof window !== 'undefined') {
    window.UserManagement = UserManagement;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = UserManagement;
}
