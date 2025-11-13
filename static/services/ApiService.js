/**
 * P.I.M.P - API Service
 * Centralized API service matching PHP API Services
 */

class ApiService {
    constructor(pimp) {
        this.pimp = pimp || window.PIMP;
        this.baseUrl = this.pimp?.config?.apiBaseUrl || '/api/v1';
        this.cache = new Map();
        this.cacheTTL = 5 * 60 * 1000; // 5 minutes default
    }

    /**
     * Make API request with error handling
     */
    async request(endpoint, options = {}) {
        const url = endpoint.startsWith('http') ? endpoint : `${this.baseUrl}${endpoint}`;
        const method = options.method || 'GET';
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

        // Add auth token if available
        const authToken = this.pimp?.authToken || localStorage.getItem('auth_token');
        if (authToken) {
            headers['Authorization'] = `Bearer ${authToken}`;
        }

        const config = {
            method,
            headers,
            ...options
        };

        // Handle FormData (for file uploads)
        if (options.body instanceof FormData) {
            delete config.headers['Content-Type'];
            config.body = options.body;
        } else if (options.body && typeof options.body === 'object') {
            config.body = JSON.stringify(options.body);
        }

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('API Request Error:', error);
            throw error;
        }
    }

    // Business Services matching PHP BusinessAPIService
    business = {
        get: async (id) => {
            return await this.request(`/business/${id}`);
        },

        search: async (filters = {}, page = 1, limit = 20) => {
            const params = new URLSearchParams({
                page: page.toString(),
                limit: limit.toString(),
                ...filters
            });
            return await this.request(`/business/search?${params}`);
        },

        create: async (businessData) => {
            return await this.request('/business', {
                method: 'POST',
                body: businessData
            });
        },

        update: async (id, businessData) => {
            return await this.request(`/business/${id}`, {
                method: 'PUT',
                body: businessData
            });
        },

        getReviews: async (businessId, page = 1) => {
            return await this.request(`/business/${businessId}/reviews?page=${page}`);
        },

        getComplaints: async (businessId, page = 1) => {
            return await this.request(`/business/${businessId}/complaints?page=${page}`);
        },

        uploadDocument: async (businessId, file) => {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('business_id', businessId);
            return await this.request(`/business/${businessId}/documents`, {
                method: 'POST',
                body: formData
            });
        },

        getLocations: async (businessId) => {
            return await this.request(`/business/${businessId}/locations`);
        },

        updateHours: async (businessId, hours) => {
            return await this.request(`/business/${businessId}/hours`, {
                method: 'PUT',
                body: { hours }
            });
        },

        getAccreditations: async (businessId) => {
            return await this.request(`/business/${businessId}/accreditations`);
        },

        getStatistics: async (businessId) => {
            return await this.request(`/business/${businessId}/statistics`);
        },

        updateStatus: async (businessId, status) => {
            return await this.request(`/business/${businessId}/status`, {
                method: 'PUT',
                body: { status }
            });
        },

        updateAccreditation: async (businessId, accreditationLevel) => {
            return await this.request(`/business/${businessId}/accreditation`, {
                method: 'PUT',
                body: { accreditation_level: accreditationLevel }
            });
        },

        getFeatured: async (limit = 6) => {
            return await this.request(`/business/featured?limit=${limit}`);
        }
    };

    // Category Services matching PHP BusinessCategoryAPIService
    category = {
        getAll: async (filters = {}) => {
            const params = new URLSearchParams(filters);
            return await this.request(`/categories?${params}`);
        },

        getBySlug: async (slug) => {
            return await this.request(`/category/${slug}`);
        },

        getStatistics: async (categoryId) => {
            return await this.request(`/category/${categoryId}/statistics`);
        },

        search: async (query, limit = 10) => {
            return await this.request('/categories/search', {
                method: 'POST',
                body: { query, limit }
            });
        }
    };

    // User Services matching PHP UserAPIService
    user = {
        getProfile: async (userId = null) => {
            const endpoint = userId ? `/user/${userId}` : '/user/profile';
            return await this.request(endpoint);
        },

        updateProfile: async (userData) => {
            return await this.request('/user/profile', {
                method: 'PUT',
                body: userData
            });
        },

        getReviews: async (userId = null, page = 1) => {
            const endpoint = userId ? `/user/${userId}/reviews` : '/user/reviews';
            return await this.request(`${endpoint}?page=${page}`);
        },

        getComplaints: async (userId = null, page = 1) => {
            const endpoint = userId ? `/user/${userId}/complaints` : '/user/complaints';
            return await this.request(`${endpoint}?page=${page}`);
        },

        changePassword: async (currentPassword, newPassword) => {
            return await this.request('/user/password', {
                method: 'PUT',
                body: {
                    current_password: currentPassword,
                    new_password: newPassword
                }
            });
        },

        updatePreferences: async (preferences) => {
            return await this.request('/user/preferences', {
                method: 'PUT',
                body: preferences
            });
        },

        getSearchHistory: async () => {
            return await this.request('/user/search-history');
        },

        deleteSearchHistory: async () => {
            return await this.request('/user/search-history', {
                method: 'DELETE'
            });
        },

        saveBusiness: async (businessId, category = 'favorites') => {
            return await this.request('/user/saved-businesses', {
                method: 'POST',
                body: { business_id: businessId, category }
            });
        },

        removeSavedBusiness: async (businessId, category = 'favorites') => {
            return await this.request('/user/saved-businesses', {
                method: 'DELETE',
                body: { business_id: businessId, category }
            });
        },

        getSavedBusinesses: async (filters = {}) => {
            const params = new URLSearchParams(filters);
            return await this.request(`/user/saved-businesses?${params}`);
        },

        getStatistics: async (userId = null) => {
            const endpoint = userId ? `/user/${userId}/statistics` : '/user/statistics';
            return await this.request(endpoint);
        }
    };

    // Review Services matching PHP ReviewAPIService
    review = {
        create: async (businessId, reviewData) => {
            return await this.request('/review', {
                method: 'POST',
                body: {
                    business_id: businessId,
                    ...reviewData
                }
            });
        },

        update: async (reviewId, reviewData) => {
            return await this.request(`/review/${reviewId}`, {
                method: 'PUT',
                body: reviewData
            });
        },

        delete: async (reviewId) => {
            return await this.request(`/review/${reviewId}`, {
                method: 'DELETE'
            });
        },

        get: async (reviewId) => {
            return await this.request(`/review/${reviewId}`);
        },

        report: async (reviewId, reason) => {
            return await this.request(`/review/${reviewId}/report`, {
                method: 'POST',
                body: { reason }
            });
        },

        react: async (reviewId, action) => {
            return await this.request(`/review/${reviewId}/vote`, {
                method: 'POST',
                body: { vote_type: action }
            });
        },

        getResponses: async (reviewId) => {
            return await this.request(`/review/${reviewId}/responses`);
        },

        addResponse: async (reviewId, response) => {
            return await this.request(`/review/${reviewId}/response`, {
                method: 'POST',
                body: { response }
            });
        },

        getStatistics: async (businessId) => {
            return await this.request(`/review/statistics?business_id=${businessId}`);
        }
    };

    // Complaint Services matching PHP ComplaintAPIService
    complaint = {
        create: async (businessId, complaintData) => {
            return await this.request('/complaint', {
                method: 'POST',
                body: {
                    business_id: businessId,
                    ...complaintData
                }
            });
        },

        get: async (complaintId) => {
            return await this.request(`/complaint/${complaintId}`);
        },

        update: async (complaintId, complaintData) => {
            return await this.request(`/complaint/${complaintId}`, {
                method: 'PUT',
                body: complaintData
            });
        },

        addMessage: async (complaintId, message) => {
            return await this.request(`/complaint/${complaintId}/message`, {
                method: 'POST',
                body: { message }
            });
        },

        uploadEvidence: async (complaintId, file) => {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('complaint_id', complaintId);
            return await this.request(`/complaint/${complaintId}/evidence`, {
                method: 'POST',
                body: formData
            });
        },

        getThread: async (complaintId) => {
            return await this.request(`/complaint/${complaintId}/thread`);
        },

        updateStatus: async (complaintId, status) => {
            return await this.request(`/complaint/${complaintId}/status`, {
                method: 'PUT',
                body: { status }
            });
        }
    };

    // Chat Services matching PHP CommunicationAPIService
    chat = {
        getSessions: async () => {
            return await this.request('/chat/sessions');
        },

        getMessages: async (sessionId, page = 1) => {
            return await this.request(`/chat/${sessionId}/messages?page=${page}`);
        },

        sendMessage: async (sessionId, message) => {
            return await this.request(`/chat/${sessionId}/message`, {
                method: 'POST',
                body: { message }
            });
        },

        startChat: async (userId) => {
            return await this.request('/chat/start', {
                method: 'POST',
                body: { user_id: userId }
            });
        },

        markRead: async (sessionId) => {
            return await this.request(`/chat/${sessionId}/read`, {
                method: 'PUT'
            });
        },

        uploadFile: async (sessionId, file) => {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('session_id', sessionId);
            return await this.request(`/chat/${sessionId}/upload`, {
                method: 'POST',
                body: formData
            });
        }
    };

    // Notification Services
    notifications = {
        get: async (page = 1) => {
            return await this.request(`/notifications?page=${page}`);
        },

        markRead: async (notificationId) => {
            return await this.request(`/notifications/${notificationId}/read`, {
                method: 'PUT'
            });
        },

        markAllRead: async () => {
            return await this.request('/notifications/read-all', {
                method: 'PUT'
            });
        },

        getUnreadCount: async () => {
            return await this.request('/notifications/unread-count');
        },

        updatePreferences: async (preferences) => {
            return await this.request('/notifications/preferences', {
                method: 'PUT',
                body: preferences
            });
        }
    };

    // Admin Services matching PHP AdminApiService
    admin = {
        getDashboardStats: async () => {
            return await this.request('/admin/dashboard/stats');
        },

        getBusinesses: async (filters = {}, page = 1) => {
            const params = new URLSearchParams({
                page: page.toString(),
                ...filters
            });
            return await this.request(`/admin/businesses?${params}`);
        },

        getUsers: async (filters = {}, page = 1) => {
            const params = new URLSearchParams({
                page: page.toString(),
                ...filters
            });
            return await this.request(`/admin/users?${params}`);
        },

        getModerationQueue: async () => {
            return await this.request('/admin/moderation/queue');
        },

        moderateContent: async (contentType, contentId, action, notes = '') => {
            return await this.request('/admin/moderation', {
                method: 'POST',
                body: {
                    content_type: contentType,
                    content_id: contentId,
                    action,
                    notes
                }
            });
        },

        getSettings: async () => {
            return await this.request('/admin/settings');
        },

        updateSetting: async (key, value) => {
            return await this.request('/admin/settings', {
                method: 'PUT',
                body: { [key]: value }
            });
        },

        getAnalytics: async (reportType, period) => {
            return await this.request(`/admin/analytics?type=${reportType}&period=${period}`);
        },

        getSystemHealth: async () => {
            return await this.request('/admin/system/health');
        }
    };

    // Auth Services
    auth = {
        login: async (email, password) => {
            return await this.request('/auth/login', {
                method: 'POST',
                body: { email, password }
            });
        },

        register: async (userData) => {
            return await this.request('/auth/register', {
                method: 'POST',
                body: userData
            });
        },

        logout: async () => {
            return await this.request('/auth/logout', {
                method: 'POST'
            });
        },

        forgotPassword: async (email) => {
            return await this.request('/auth/forgot-password', {
                method: 'POST',
                body: { email }
            });
        },

        resetPassword: async (token, newPassword) => {
            return await this.request('/auth/reset-password', {
                method: 'POST',
                body: { token, password: newPassword }
            });
        },

        verifyEmail: async (token) => {
            return await this.request('/auth/verify-email', {
                method: 'POST',
                body: { token }
            });
        }
    };

    // Cache management
    withCache = async (key, fetcher, ttl = null) => {
        const cacheKey = key;
        const cached = this.cache.get(cacheKey);
        const now = Date.now();
        const cacheTime = ttl || this.cacheTTL;

        if (cached && (now - cached.timestamp) < cacheTime) {
            return cached.data;
        }

        const data = await fetcher();
        this.cache.set(cacheKey, {
            data,
            timestamp: now
        });

        return data;
    };

    clearCache = (pattern = null) => {
        if (!pattern) {
            this.cache.clear();
            return;
        }

        const regex = new RegExp(pattern);
        for (const key of this.cache.keys()) {
            if (regex.test(key)) {
                this.cache.delete(key);
            }
        }
    };
}

// Initialize and attach to window
if (typeof window !== 'undefined') {
    window.ApiService = new ApiService(window.PIMP);
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ApiService;
}
