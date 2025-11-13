/**
 * P.I.M.P - WebSocket Service
 * Handles real-time communication using Socket.IO
 */

class WebSocketService {
    constructor(pimp) {
        this.pimp = pimp || window.PIMP;
        this.socket = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000;
        this.isConnected = false;
        this.eventHandlers = new Map();
        this.rooms = new Set();
        this.config = {
            wsUrl: this.pimp?.config?.wsUrl || window.location.origin,
            socketPath: this.pimp?.config?.socketPath || '/socket.io',
            autoConnect: true
        };
        this.init();
    }

    init() {
        // Check if Socket.IO is available
        if (typeof io === 'undefined') {
            console.warn('Socket.IO not loaded. WebSocket features will be unavailable.');
            return;
        }

        if (this.config.autoConnect) {
            this.connect();
        }
    }

    connect() {
        if (this.socket?.connected) {
            return;
        }

        try {
            const authToken = this.pimp?.authToken || localStorage.getItem('auth_token');
            
            this.socket = io(this.config.wsUrl, {
                path: this.config.socketPath,
                transports: ['websocket', 'polling'],
                auth: authToken ? { token: authToken } : {},
                reconnection: true,
                reconnectionDelay: this.reconnectDelay,
                reconnectionAttempts: this.maxReconnectAttempts
            });

            this.setupEventHandlers();
        } catch (error) {
            console.error('WebSocket connection error:', error);
            this.handleReconnect();
        }
    }

    setupEventHandlers() {
        if (!this.socket) return;

        // Connection events
        this.socket.on('connect', () => {
            this.isConnected = true;
            this.reconnectAttempts = 0;
            console.log('WebSocket connected');
            this.emitEvent('connected', { socketId: this.socket.id });

            // Rejoin rooms after reconnection
            this.rooms.forEach(roomId => {
                this.joinRoom(roomId);
            });
        });

        this.socket.on('disconnect', (reason) => {
            this.isConnected = false;
            console.log('WebSocket disconnected:', reason);
            this.emitEvent('disconnected', { reason });

            if (reason === 'io server disconnect') {
                // Server disconnected, reconnect manually
                this.socket.connect();
            }
        });

        this.socket.on('connect_error', (error) => {
            console.error('WebSocket connection error:', error);
            this.emitEvent('error', { error });
            this.handleReconnect();
        });

        this.socket.on('reconnect', (attemptNumber) => {
            console.log('WebSocket reconnected after', attemptNumber, 'attempts');
            this.reconnectAttempts = 0;
            this.emitEvent('reconnected', { attemptNumber });
        });

        this.socket.on('reconnect_attempt', (attemptNumber) => {
            console.log('WebSocket reconnection attempt', attemptNumber);
            this.reconnectAttempts = attemptNumber;
            this.emitEvent('reconnect_attempt', { attemptNumber });
        });

        this.socket.on('reconnect_failed', () => {
            console.error('WebSocket reconnection failed');
            this.emitEvent('reconnect_failed', {});
        });

        // Custom event handlers
        this.socket.onAny((eventName, ...args) => {
            this.handleEvent(eventName, ...args);
        });
    }

    disconnect() {
        if (this.socket) {
            this.socket.disconnect();
            this.socket = null;
            this.isConnected = false;
            this.rooms.clear();
        }
    }

    emit(event, data) {
        if (!this.socket || !this.isConnected) {
            console.warn('WebSocket not connected. Event not sent:', event);
            return false;
        }

        try {
            this.socket.emit(event, data);
            return true;
        } catch (error) {
            console.error('Error emitting WebSocket event:', error);
            return false;
        }
    }

    on(event, callback) {
        if (!this.socket) {
            // Store handler to attach when socket connects
            if (!this.eventHandlers.has(event)) {
                this.eventHandlers.set(event, []);
            }
            this.eventHandlers.get(event).push(callback);
            return;
        }

        this.socket.on(event, callback);

        // Store handler
        if (!this.eventHandlers.has(event)) {
            this.eventHandlers.set(event, []);
        }
        this.eventHandlers.get(event).push(callback);
    }

    off(event, callback) {
        if (this.socket) {
            if (callback) {
                this.socket.off(event, callback);
            } else {
                this.socket.off(event);
            }
        }

        // Remove from stored handlers
        if (this.eventHandlers.has(event)) {
            if (callback) {
                const handlers = this.eventHandlers.get(event);
                const index = handlers.indexOf(callback);
                if (index > -1) {
                    handlers.splice(index, 1);
                }
            } else {
                this.eventHandlers.delete(event);
            }
        }
    }

    handleEvent(eventName, ...args) {
        // Trigger stored event handlers
        if (this.eventHandlers.has(eventName)) {
            this.eventHandlers.get(eventName).forEach(handler => {
                try {
                    handler(...args);
                } catch (error) {
                    console.error(`Error in event handler for ${eventName}:`, error);
                }
            });
        }

        // Emit custom event for PIMP event system
        if (this.pimp?.emitEvent) {
            this.pimp.emitEvent(`ws:${eventName}`, { data: args });
        }
    }

    joinRoom(roomId) {
        if (!this.socket || !this.isConnected) {
            console.warn('WebSocket not connected. Cannot join room:', roomId);
            return false;
        }

        if (this.rooms.has(roomId)) {
            return true;
        }

        try {
            this.socket.emit('join_room', { room_id: roomId });
            this.rooms.add(roomId);
            return true;
        } catch (error) {
            console.error('Error joining room:', error);
            return false;
        }
    }

    leaveRoom(roomId) {
        if (!this.socket || !this.isConnected) {
            return false;
        }

        if (!this.rooms.has(roomId)) {
            return true;
        }

        try {
            this.socket.emit('leave_room', { room_id: roomId });
            this.rooms.delete(roomId);
            return true;
        } catch (error) {
            console.error('Error leaving room:', error);
            return false;
        }
    }

    handleReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            console.error('Max reconnection attempts reached');
            this.emitEvent('reconnect_failed', {});
            return;
        }

        this.reconnectAttempts++;
        const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1);

        setTimeout(() => {
            if (!this.isConnected) {
                this.connect();
            }
        }, delay);
    }

    emitEvent(event, data) {
        if (this.pimp?.emitEvent) {
            this.pimp.emitEvent(event, data);
        }
    }

    // Convenience methods for common events
    sendMessage(roomId, message) {
        return this.emit('message', {
            room_id: roomId,
            message
        });
    }

    sendTyping(roomId, isTyping) {
        return this.emit('typing', {
            room_id: roomId,
            typing: isTyping
        });
    }

    sendNotification(userId, notification) {
        return this.emit('notification', {
            user_id: userId,
            notification
        });
    }

    // Get connection status
    getStatus() {
        return {
            connected: this.isConnected,
            socketId: this.socket?.id || null,
            reconnectAttempts: this.reconnectAttempts,
            rooms: Array.from(this.rooms)
        };
    }
}

// Initialize and attach to window
if (typeof window !== 'undefined') {
    window.WebSocketService = new WebSocketService(window.PIMP);
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WebSocketService;
}
