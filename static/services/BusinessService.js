class BusinessService {
    constructor(apiService) {
        this.api = apiService;
    }

    //TODO: Business profile management methods
    //TODO: Business accreditation methods  
    //TODO: Business location methods
    //TODO: Business document methods
    //TODO: Business subscription methods
}

window.BusinessService = new BusinessService(window.ApiService);
