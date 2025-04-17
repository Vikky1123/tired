/**
 * BiTrader Debug Login Script
 * 
 * This script enhances the login process with detailed debugging
 * to help diagnose issues with API connectivity and authentication.
 */

// Self-executing function to avoid global scope pollution
(function() {
    // Debugging variables
    const DEBUG = true;
    const API_ENDPOINTS = {
        login: 'http://localhost/PROJECT-BITRADER/backend/direct-login.php',
        register: 'http://localhost/PROJECT-BITRADER/backend/direct-register.php'
    };
    
    // Initialize the debug functionality when DOM is ready
    function initDebugLogin() {
        // Add debug info container to the page
        addDebugContainer();
        
        // Override the existing login form submission
        const signinForm = document.querySelector('#signin-tab-pane .auth-form');
        const submitButton = signinForm ? signinForm.querySelector('button[type="submit"]') : null;
        
        if (signinForm && submitButton) {
            logDebug('Debug login initialized, overriding login form submission');
            
            // Clear any previous click listeners
            const newButton = submitButton.cloneNode(true);
            submitButton.parentNode.replaceChild(newButton, submitButton);
            
            // Add our debug login handler
            newButton.addEventListener('click', function(event) {
                event.preventDefault();
                handleDebugLogin();
            });
            
            // Also handle form submit
            signinForm.addEventListener('submit', function(event) {
                event.preventDefault();
                handleDebugLogin();
            });
        } else {
            logDebug('ERROR: Could not find login form or submit button');
        }
    }
    
    // Handle login with detailed debugging
    function handleDebugLogin() {
        clearDebugLog();
        logDebug('Login attempt started');
        
        try {
            // Clear any previous error messages
            clearMessages();
            
            // Get login credentials
            const username = document.getElementById('signin-username').value.trim();
            const password = document.getElementById('signin-password').value;
            
            // Log debug info
            logDebug(`Username provided: ${username ? 'Yes' : 'No'}`);
            logDebug(`Password provided: ${password ? 'Yes' : 'No'}`);
            
            // Validate
            if (!username || !password) {
                showMessage('Please enter both username and password', 'error');
                logDebug('ERROR: Missing username or password');
                return;
            }
            
            // Check for special characters that could cause issues
            if (/[<>'"&]/.test(username)) {
                logDebug('WARNING: Username contains special characters that might cause issues');
            }
            
            // Show spinner
            showSpinner();
            
            // Make login request with explicit content type
            const apiUrl = API_ENDPOINTS.login;
            logDebug(`Attempting to connect to API: ${apiUrl}`);
            
            // Check network connectivity first
            checkConnectivity()
                .then(isConnected => {
                    if (!isConnected) {
                        throw new Error('No internet connection detected');
                    }
                    
                    logDebug('Network connectivity confirmed, sending login request');
                    logDebug('Request payload: ' + JSON.stringify({
                        username: username,
                        password: '****' // Masked for security
                    }));
                    
                    // Send the actual login request
                    return fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            username: username,
                            password: password
                        })
                    });
                })
                .then(response => {
                    logDebug(`Response status: ${response.status} ${response.statusText}`);
                    
                    // Log response headers for debugging
                    const headers = {};
                    response.headers.forEach((value, name) => {
                        headers[name] = value;
                    });
                    logDebug('Response headers: ' + JSON.stringify(headers));
                    
                    // Always get text first, don't assume it's JSON
                    return response.text().then(text => {
                        // Check if empty response
                        if (!text || text.trim() === '') {
                            logDebug('ERROR: Empty response received from server');
                            return { 
                                status: response.status,
                                text: 'Empty response received',
                                isJson: false,
                                parseError: 'Empty response'
                            };
                        }
                        
                        logDebug('Raw response: ' + text.substring(0, 500) + (text.length > 500 ? '...' : ''));
                        
                        // Try to parse as JSON
                        let jsonData;
                        try {
                            jsonData = JSON.parse(text);
                            logDebug('Response successfully parsed as JSON');
                            return { 
                                status: response.status,
                                json: jsonData,
                                isJson: true
                            };
                        } catch (e) {
                            logDebug(`ERROR: Failed to parse response as JSON: ${e.message}`);
                            return { 
                                status: response.status,
                                text: text,
                                isJson: false,
                                parseError: e.message
                            };
                        }
                    });
                })
                .then(data => {
                    // If parsed successfully as JSON
                    if (data.isJson) {
                        logDebug(`Parsed JSON response: ${JSON.stringify(data.json)}`);
                        
                        if (data.json.success) {
                            logDebug('Login successful!');
                            showMessage('Login successful! Redirecting...', 'success');
                            
                            // Store auth data
                            try {
                                localStorage.setItem('authToken', data.json.data.token);
                                localStorage.setItem('userData', JSON.stringify(data.json.data.user));
                                logDebug('Auth data stored in localStorage');
                                
                                // Display token info
                                const token = data.json.data.token;
                                logDebug(`Token: ${token.substring(0, 15)}...${token.substring(token.length - 10)}`);
                                logDebug(`Token length: ${token.length} characters`);
                                
                                // Check token structure (without revealing sensitive data)
                                const tokenParts = token.split('.');
                                if (tokenParts.length === 3) {
                                    logDebug('Token appears to be in valid JWT format (header.payload.signature)');
                                } else {
                                    logDebug('WARNING: Token does not appear to be in standard JWT format');
                                }
                            } catch (e) {
                                logDebug(`ERROR storing auth data: ${e.message}`);
                                showMessage('Warning: Could not store login data locally. You may need to login again later.', 'warning');
                            }
                            
                            // Check redirect URL
                            const dashboardPath = '../../coinex/dashboard/index.php';
                            logDebug(`Will redirect to: ${dashboardPath}`);
                            
                            // Redirect to dashboard after a short delay
                            setTimeout(() => {
                                try {
                                    window.location.href = dashboardPath;
                                    logDebug('Redirect initiated');
                                } catch (e) {
                                    logDebug(`ERROR during redirect: ${e.message}`);
                                    showMessage('Error during redirect. Please go to the dashboard manually.', 'error');
                                }
                            }, 3000); // Longer delay to see debug info
                        } else {
                            const errorMsg = data.json.message || 'Login failed. Please check your credentials.';
                            logDebug(`Login failed: ${errorMsg}`);
                            showMessage(errorMsg, 'error');
                            hideSpinner();
                        }
                    } else {
                        // If not JSON or couldn't parse
                        logDebug('ERROR: Response was not valid JSON');
                        
                        // Check for HTML response which might indicate a server error page
                        if (data.text && data.text.includes('<!DOCTYPE html>')) {
                            logDebug('Response appears to be an HTML page instead of JSON. This likely indicates a server error.');
                        }
                        
                        showMessage('Received invalid response from server. Please try again.', 'error');
                        hideSpinner();
                    }
                })
                .catch(error => {
                    logDebug(`ERROR during login process: ${error.message}`);
                    showMessage('Error connecting to server: ' + error.message, 'error');
                    hideSpinner();
                });
        } catch (error) {
            logDebug(`CRITICAL ERROR: ${error.message}`);
            showMessage('An unexpected error occurred. Please try again later.', 'error');
            hideSpinner();
        }
    }
    
    // Check internet connectivity
    function checkConnectivity() {
        return new Promise(resolve => {
            // Try a fetch to a reliable endpoint
            fetch('https://www.google.com/favicon.ico', { 
                mode: 'no-cors',
                cache: 'no-store',
                method: 'HEAD'
            })
            .then(() => {
                logDebug('Internet connectivity test successful');
                resolve(true);
            })
            .catch(error => {
                logDebug(`Internet connectivity test failed: ${error.message}`);
                resolve(false);
            });
        });
    }
    
    // Clear any previous messages
    function clearMessages() {
        const existingMsgs = document.querySelectorAll('.auth-message');
        existingMsgs.forEach(msg => msg.remove());
    }
    
    // Show message with improved styling
    function showMessage(message, type = 'info') {
        // Remove any existing message
        clearMessages();
        
        // Create new message
        const msgDiv = document.createElement('div');
        msgDiv.className = `auth-message alert alert-${type === 'error' ? 'danger' : (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'info'))}`;
        msgDiv.textContent = message;
        
        // Insert before the form
        const form = document.querySelector('#signin-tab-pane .auth-form');
        if (form && form.parentNode) {
            form.parentNode.insertBefore(msgDiv, form);
        } else {
            // Fallback location if form parent isn't available
            const container = document.querySelector('.auth-form-container');
            if (container) {
                container.insertBefore(msgDiv, container.firstChild);
            }
        }
        
        logDebug(`Message displayed: ${type.toUpperCase()} - ${message}`);
    }
    
    // Show spinner with improved styling
    function showSpinner() {
        // Remove any existing spinner
        hideSpinner();
        
        // Create and add spinner
        const spinner = document.createElement('div');
        spinner.className = 'auth-spinner';
        spinner.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        document.body.appendChild(spinner);
        
        logDebug('Loading spinner displayed');
        
        // Safety timeout to hide spinner after 30 seconds
        setTimeout(() => {
            if (document.querySelector('.auth-spinner')) {
                logDebug('WARNING: Request timeout after 30 seconds');
                hideSpinner();
            }
        }, 30000);
    }
    
    // Hide spinner
    function hideSpinner() {
        const spinner = document.querySelector('.auth-spinner');
        if (spinner) {
            spinner.remove();
            logDebug('Loading spinner hidden');
        }
    }
    
    // Add debug container to the page
    function addDebugContainer() {
        if (!DEBUG) return;
        
        // Remove any existing debug container
        const existingContainer = document.getElementById('debug-container');
        if (existingContainer) {
            existingContainer.remove();
        }
        
        // Create debug container
        const container = document.createElement('div');
        container.id = 'debug-container';
        container.style.cssText = 'position: fixed; bottom: 10px; right: 10px; width: 400px; max-height: 300px; overflow-y: auto; background: rgba(0,0,0,0.8); color: #00ff00; font-family: monospace; font-size: 12px; padding: 10px; border-radius: 5px; z-index: 9999; box-shadow: 0 0 10px rgba(0,0,0,0.5);';
        
        // Create header
        const header = document.createElement('div');
        header.style.cssText = 'display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #00ff00; padding-bottom: 5px;';
        header.innerHTML = '<h3 style="margin: 0; color: #00ff00;">Login Debug Console</h3>';
        
        // Add clear and close buttons
        const buttonContainer = document.createElement('div');
        
        const clearButton = document.createElement('button');
        clearButton.textContent = 'Clear';
        clearButton.style.cssText = 'background: #333; color: #fff; border: none; padding: 3px 8px; margin-right: 5px; cursor: pointer; border-radius: 3px;';
        clearButton.onclick = clearDebugLog;
        
        const closeButton = document.createElement('button');
        closeButton.textContent = 'Close';
        closeButton.style.cssText = 'background: #333; color: #fff; border: none; padding: 3px 8px; cursor: pointer; border-radius: 3px;';
        closeButton.onclick = () => { container.style.display = 'none'; };
        
        buttonContainer.appendChild(clearButton);
        buttonContainer.appendChild(closeButton);
        header.appendChild(buttonContainer);
        
        // Create log container
        const logContainer = document.createElement('div');
        logContainer.id = 'debug-log';
        logContainer.style.cssText = 'font-size: 11px; line-height: 1.4;';
        
        // Add elements to container
        container.appendChild(header);
        container.appendChild(logContainer);
        
        // Add to page
        document.body.appendChild(container);
        
        // Log initial message
        logDebug('Debug console initialized');
        logDebug(`Current URL: ${window.location.href}`);
        logDebug(`API Endpoints: ${JSON.stringify(API_ENDPOINTS)}`);
        
        // Check local storage
        try {
            logDebug('Checking localStorage...');
            const storageAvailable = typeof localStorage !== 'undefined';
            logDebug(`localStorage available: ${storageAvailable}`);
            
            if (storageAvailable) {
                const authToken = localStorage.getItem('authToken');
                const userData = localStorage.getItem('userData');
                
                logDebug(`Existing authToken: ${authToken ? 'Yes' : 'No'}`);
                logDebug(`Existing userData: ${userData ? 'Yes' : 'No'}`);
                
                if (authToken) {
                    logDebug(`Token length: ${authToken.length} characters`);
                }
                
                if (userData) {
                    try {
                        const parsedUser = JSON.parse(userData);
                        logDebug(`User data contains: ${Object.keys(parsedUser).join(', ')}`);
                    } catch (e) {
                        logDebug(`WARNING: Stored user data is not valid JSON: ${e.message}`);
                    }
                }
            }
        } catch (e) {
            logDebug(`Error checking localStorage: ${e.message}`);
        }
    }
    
    // Log debug message
    function logDebug(message) {
        if (!DEBUG) return;
        
        // Log to console
        console.log(`[BiTrader Debug] ${message}`);
        
        // Log to debug container if available
        const logContainer = document.getElementById('debug-log');
        if (logContainer) {
            const timestamp = new Date().toLocaleTimeString();
            const logEntry = document.createElement('div');
            
            // Style message based on type
            if (message.startsWith('ERROR:') || message.startsWith('ERROR ') || message.startsWith('CRITICAL ERROR:')) {
                logEntry.style.color = '#ff4d4d'; // Red for errors
            } else if (message.startsWith('WARNING:') || message.startsWith('WARNING ')) {
                logEntry.style.color = '#ffaa00'; // Orange for warnings
            } else if (message.startsWith('SUCCESS:') || message.startsWith('SUCCESS ')) {
                logEntry.style.color = '#00ff00'; // Green for success
            }
            
            logEntry.innerHTML = `<span style="color: #888;">[${timestamp}]</span> ${message}`;
            logContainer.appendChild(logEntry);
            
            // Auto-scroll to bottom
            logContainer.scrollTop = logContainer.scrollHeight;
        }
    }
    
    // Clear debug log
    function clearDebugLog() {
        const logContainer = document.getElementById('debug-log');
        if (logContainer) {
            logContainer.innerHTML = '';
            logDebug('Debug log cleared');
        }
    }
    
    // Check if we're on a login page
    function isLoginPage() {
        return window.location.pathname.includes('Signup-Signin') || 
               document.querySelector('#signin-tab-pane') !== null;
    }
    
    // Initialize when DOM is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (isLoginPage()) {
                initDebugLogin();
            }
        });
    } else {
        // DOM already loaded
        if (isLoginPage()) {
            initDebugLogin();
        }
    }
})(); 