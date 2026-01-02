class StateManager {
    constructor() {
        this.state = new Map();
        this.subscribers = new Map();
    }

    set(key, value) {
        //TODO: Set state value
    }

    get(key) {
        //TODO: Get state value
    }

    subscribe(key, callback) {
        //TODO: Subscribe to state changes
    }

    unsubscribe(key, callback) {
        //TODO: Unsubscribe from state changes
    }
}

window.StateManager = new StateManager();
