/**
 * API Client for BiTrader
 */
const API = {
    // Base URL for API endpoints
    baseUrl: '/backend/api',
    
    /**
     * Perform a fetch request to the API
     * 
     * @param {string} endpoint - API endpoint
     * @param {string} method - HTTP method (GET, POST, PUT, DELETE)
     * @param {object} data - Request data (for POST/PUT)
     * @param {boolean} useToken - Whether to include auth token
     * @returns {Promise} - Fetch promise
     */
    async request(endpoint, method = 'GET', data = null, useToken = true) {
        // Build request options
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
            },
        };
        
        // Add auth token if required
        if (useToken) {
            const token = localStorage.getItem('token');
            if (token) {
                options.headers['Authorization'] = `Bearer ${token}`;
            }
        }
        
        // Add body for POST/PUT requests
        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }
        
        try {
            // Use our direct endpoint files for auth, use the router for other endpoints
            let url;
            if (endpoint === 'auth/login') {
                url = `${this.baseUrl}/direct-login.php`;
            } else if (endpoint === 'auth/register') {
                url = `${this.baseUrl}/direct-register.php`;
            } else {
                url = `${this.baseUrl}/endpoint.php?route=${endpoint}`;
            }
            
            const response = await fetch(url, options);
            const result = await response.json();
            
            if (!response.ok) {
                throw { 
                    status: response.status, 
                    message: result.error || 'Unknown error occurred'
                };
            }
            
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    /**
     * User login
     * 
     * @param {string} username - Username or email
     * @param {string} password - Password
     * @returns {Promise} - Login response with user details and token
     */
    login(username, password) {
        return this.request('auth/login', 'POST', { username, password }, false);
    },
    
    /**
     * User registration
     * 
     * @param {object} userData - User registration data
     * @returns {Promise} - Registration response
     */
    register(userData) {
        return this.request('auth/register', 'POST', userData, false);
    },
    
    /**
     * Verify authentication token
     * 
     * @returns {Promise} - Verification response
     */
    verifyToken() {
        return this.request('auth/verify', 'GET');
    },
    
    // Add more API methods as needed
}; 