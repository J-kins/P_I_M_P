class UserService {
    constructor(apiService) {
        this.api = apiService;
    }

    //TODO: User profile management methods
    //TODO: User authentication methods
    //TODO: User preference methods
    //TODO: User search history methods
}

window.UserService = new UserService(window.ApiService);
