/**
 * P.I.M.P - Business Management
 * Admin business management functionality
 */

class BusinessManagement {
    constructor(container, pimp) {
        this.container = container || document.querySelector('.business-management');
        this.pimp = pimp || window.PIMP;
        this.api = window.ApiService;
        this.businesses = [];
        this.currentPage = 1;
        this.filters = {};
        this.init();
    }

    async init() {
        if (!this.container) {
            console.error('BusinessManagement: Container not found');
            return;
        }

        this.setupEventHandlers();
        await this.loadBusinesses();
    }

    setupEventHandlers() {
        // Search
        const searchInput = this.container.querySelector('[data-business-search]');
        if (searchInput) {
            searchInput.addEventListener('input', this.pimp?.debounce((e) => {
                this.searchBusinesses(e.target.value);
            }, 300) || ((e) => {
                setTimeout(() => this.searchBusinesses(e.target.value), 300);
            }));
        }

        // Status filter
        this.container.querySelectorAll('[data-business-filter]').forEach(filter => {
            filter.addEventListener('change', (e) => {
                const key = filter.getAttribute('data-business-filter');
                this.filters[key] = e.target.value;
                this.loadBusinesses();
            });
        });

        // Action buttons
        this.container.querySelectorAll('[data-business-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = btn.getAttribute('data-business-action');
                const businessId = btn.getAttribute('data-business-id');
                this.handleBusinessAction(action, businessId);
            });
        });
    }

    async loadBusinesses(filters = {}) {
        try {
            this.showLoading();

            this.filters = { ...this.filters, ...filters };
            const response = await this.api.admin.getBusinesses(this.filters, this.currentPage);

            if (response.success) {
                this.businesses = response.data.businesses || response.data || [];
                this.renderBusinessTable();
                this.renderPagination(response.data.pagination || {});
            } else {
                this.showError(response.message || 'Failed to load businesses');
            }
        } catch (error) {
            console.error('Error loading businesses:', error);
            this.showError('Error loading businesses');
        } finally {
            this.hideLoading();
        }
    }

    renderBusinessTable() {
        const tableBody = this.container.querySelector('.businesses-table tbody, [data-businesses-table]');
        if (!tableBody) return;

        if (this.businesses.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="100%">No businesses found</td></tr>';
            return;
        }

        tableBody.innerHTML = this.businesses.map(business => `
            <tr data-business-id="${business.id}">
                <td>
                    <div class="business-logo">
                        ${business.logo_url ? 
                            `<img src="${business.logo_url}" alt="${business.business_name}">` : 
                            `<i class="fas fa-building"></i>`
                        }
                    </div>
                </td>
                <td>
                    <div class="business-name">${business.business_name}</div>
                    <div class="business-type">${business.business_type}</div>
                </td>
                <td>
                    <div class="business-location">
                        <i class="fas fa-map-marker-alt"></i>
                        ${business.city}, ${business.state}
                    </div>
                </td>
                <td>
                    <span class="status-badge status-${business.status}">
                        ${business.status}
                    </span>
                </td>
                <td>
                    <span class="accreditation-badge accreditation-${business.accreditation_level}">
                        ${business.accreditation_level}
                    </span>
                </td>
                <td>
                    <div class="business-rating">
                        <i class="fas fa-star"></i>
                        ${business.rating || '0.0'}
                        <span class="review-count">(${business.total_reviews || 0})</span>
                    </div>
                </td>
                <td>${this.formatDate(business.created_at)}</td>
                <td>
                    <div class="business-actions">
                        <button class="btn-icon" data-action="view" data-business-id="${business.id}" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon" data-action="approve" data-business-id="${business.id}" title="Approve">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn-icon" data-action="reject" data-business-id="${business.id}" title="Reject">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="btn-icon" data-action="suspend" data-business-id="${business.id}" title="Suspend">
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
                const businessId = btn.getAttribute('data-business-id');
                this.handleBusinessAction(action, businessId);
            });
        });
    }

    async handleBusinessAction(action, businessId) {
        switch (action) {
            case 'view':
                await this.viewBusinessDetails(businessId);
                break;
            case 'approve':
                await this.approveBusiness(businessId);
                break;
            case 'reject':
                await this.rejectBusiness(businessId);
                break;
            case 'suspend':
                await this.suspendBusiness(businessId);
                break;
        }
    }

    async approveBusiness(businessId) {
        try {
            const response = await this.api.business.updateStatus(businessId, 'active');

            if (response.success) {
                this.pimp?.showNotification('Business approved successfully', 'success', 2000);
                await this.loadBusinesses();
            } else {
                this.showError(response.message || 'Failed to approve business');
            }
        } catch (error) {
            console.error('Error approving business:', error);
            this.showError('Error approving business');
        }
    }

    async rejectBusiness(businessId, reason = '') {
        if (!reason) {
            reason = prompt('Please provide a reason for rejection:');
            if (!reason) return;
        }

        try {
            const response = await this.api.business.updateStatus(businessId, 'rejected');

            if (response.success) {
                this.pimp?.showNotification('Business rejected', 'success', 2000);
                await this.loadBusinesses();
            } else {
                this.showError(response.message || 'Failed to reject business');
            }
        } catch (error) {
            console.error('Error rejecting business:', error);
            this.showError('Error rejecting business');
        }
    }

    async suspendBusiness(businessId) {
        if (!confirm('Are you sure you want to suspend this business?')) {
            return;
        }

        try {
            const response = await this.api.business.updateStatus(businessId, 'suspended');

            if (response.success) {
                this.pimp?.showNotification('Business suspended', 'success', 2000);
                await this.loadBusinesses();
            } else {
                this.showError(response.message || 'Failed to suspend business');
            }
        } catch (error) {
            console.error('Error suspending business:', error);
            this.showError('Error suspending business');
        }
    }

    async viewBusinessDetails(businessId) {
        try {
            const response = await this.api.business.get(businessId);

            if (response.success && response.data) {
                const business = response.data;
                
                // Show in modal or navigate
                if (typeof window.ModalComponent !== 'undefined') {
                    const modal = document.querySelector('#businessDetailsModal');
                    if (modal && modal.modalComponent) {
                        modal.modalComponent.setContent(this.renderBusinessDetails(business));
                        modal.modalComponent.show();
                    }
                } else {
                    window.location.href = `/admin/businesses/${businessId}`;
                }
            } else {
                this.showError('Failed to load business details');
            }
        } catch (error) {
            console.error('Error loading business details:', error);
            this.showError('Error loading business details');
        }
    }

    renderBusinessDetails(business) {
        return `
            <div class="business-details">
                <h3>${business.business_name}</h3>
                <p><strong>Type:</strong> ${business.business_type}</p>
                <p><strong>Status:</strong> ${business.status}</p>
                <p><strong>Accreditation:</strong> ${business.accreditation_level}</p>
                <p><strong>Location:</strong> ${business.address}, ${business.city}, ${business.state}</p>
                <p><strong>Rating:</strong> ${business.rating || '0.0'} (${business.total_reviews || 0} reviews)</p>
                <p><strong>Created:</strong> ${this.formatDate(business.created_at)}</p>
            </div>
        `;
    }

    async searchBusinesses(query) {
        if (!query || query.length < 2) {
            this.filters.search = '';
            await this.loadBusinesses();
            return;
        }

        this.filters.search = query;
        this.currentPage = 1;
        await this.loadBusinesses();
    }

    renderPagination(pagination) {
        const paginationContainer = this.container.querySelector('.pagination, [data-pagination]');
        if (!paginationContainer || !pagination.total_pages) return;

        let html = '<div class="pagination">';
        
        html += `<button class="pagination-btn" ${this.currentPage === 1 ? 'disabled' : ''} data-page="${this.currentPage - 1}">
            <i class="fas fa-chevron-left"></i>
        </button>`;

        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === 1 || i === pagination.total_pages || (i >= this.currentPage - 2 && i <= this.currentPage + 2)) {
                html += `<button class="pagination-btn ${i === this.currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
            } else if (i === this.currentPage - 3 || i === this.currentPage + 3) {
                html += '<span class="pagination-ellipsis">...</span>';
            }
        }

        html += `<button class="pagination-btn" ${this.currentPage === pagination.total_pages ? 'disabled' : ''} data-page="${this.currentPage + 1}">
            <i class="fas fa-chevron-right"></i>
        </button>`;

        html += '</div>';

        paginationContainer.innerHTML = html;

        paginationContainer.querySelectorAll('[data-page]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const page = parseInt(e.target.closest('[data-page]').getAttribute('data-page'));
                if (page && page !== this.currentPage) {
                    this.currentPage = page;
                    this.loadBusinesses();
                }
            });
        });
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
    const container = document.querySelector('.business-management');
    if (container && !container.businessManagement) {
        container.businessManagement = new BusinessManagement(container);
    }
});

// Export
if (typeof window !== 'undefined') {
    window.BusinessManagement = BusinessManagement;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = BusinessManagement;
}
