class Constants {
    static API_ENDPOINTS = {
        BUSINESSES: '/api/v1/businesses',
        USERS: '/api/v1/users',
        REVIEWS: '/api/v1/reviews',
        COMPLAINTS: '/api/v1/complaints',
        CHAT: '/api/v1/chat',
        NOTIFICATIONS: '/api/v1/notifications',
        ADMIN: '/api/v1/admin'
    };

    static USER_ROLES = {
        CONSUMER: 'consumer',
        BUSINESS_OWNER: 'business_owner',
        ADMIN: 'admin',
        MODERATOR: 'moderator'
    };

    static BUSINESS_STATUS = {
        PENDING: 'pending',
        APPROVED: 'approved',
        REJECTED: 'rejected',
        SUSPENDED: 'suspended'
    };

    static COMPLAINT_STATUS = {
        NEW: 'new',
        IN_PROGRESS: 'in_progress',
        RESOLVED: 'resolved',
        CLOSED: 'closed'
    };

    static REVIEW_STATUS = {
        PENDING: 'pending',
        APPROVED: 'approved',
        REJECTED: 'rejected'
    };

    static NOTIFICATION_TYPES = {
        REVIEW: 'review',
        COMPLAINT: 'complaint',
        MESSAGE: 'message',
        SYSTEM: 'system'
    };
}

window.Constants = Constants;
