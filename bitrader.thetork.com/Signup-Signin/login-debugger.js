/**
 * BiTrader Login Debugger
 * This script helps diagnose login issues by testing API connectivity,
 * local storage, and redirects.
 * 
 * HIDDEN MODE: Only shows debug panel when triggered by:
 * - URL parameter: ?debug=true
 * - Keyboard shortcut: Ctrl+Shift+D
 */

// Self-executing function to avoid global scope pollution
(function() {
    // Configuration
    const config = {
        apiBaseUrl: 'http://localhost/PROJECT-BITRADER/backend',
        loginEndpoint: '/direct-login.php',
        registerEndpoint: '/direct-register.php',
        dashboardRedirect: '../../coinex/dashboard/index.php',
        debugMode: false // Default to hidden unless triggered
    };

    // Debug container reference
    let debugContainer = null;
    let isDebuggerActive = false;
    
    // Function to initialize debugger
    function initDebugger() {
        // Check for debug mode via URL parameter
        if (window.location.search.includes('debug=true')) {
            config.debugMode = true;
        }
        
        // Add keyboard shortcut listener for Ctrl+Shift+D
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                toggleDebugger();
            }
        });
        
        // Override the login form submission in both cases
        setupLoginHandler();
        
        // Only show debug interface if debug mode is active
        if (config.debugMode) {
            activateDebugger();
        }
    }
    
    // Function to toggle debugger visibility
    function toggleDebugger() {
        if (isDebuggerActive) {
            // Hide if already active
            if (debugContainer) {
                debugContainer.style.display = 'none';
            }
            isDebuggerActive = false;
        } else {
            // Show if not active
            activateDebugger();
        }
    }
    
    // Setup login form handler
    function setupLoginHandler() {
        const signinForm = document.querySelector('#signin-tab-pane .auth-form');
        const submitBtn = signinForm ? signinForm.querySelector('button[type="submit"]') : null;
        
        if (signinForm && submitBtn) {
            // Clone and replace submit button to clear any existing handlers
            const newBtn = submitBtn.cloneNode(true);
            submitBtn.parentNode.replaceChild(newBtn, submitBtn);
            
            // Add click handler that works in both regular and debug mode
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (config.debugMode) {
                    debugLogin();
                } else {
                    regularLogin();
                }
            });
            
            // Also handle form submit
            signinForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (config.debugMode) {
                    debugLogin();
                } else {
                    regularLogin();
                }
            });
        }
    }
    
    // Regular login function (no debug)
    function regularLogin() {
        // Get login credentials
        const username = document.getElementById('signin-username').value.trim();
        const password = document.getElementById('signin-password').value;
        
        // Basic validation
        if (!username || !password) {
            showMessage('Please enter both username and password', 'error');
            return;
        }
        
        // Show spinner
        showSpinner();
        
        // Send login request
        fetch(`${config.apiBaseUrl}${config.loginEndpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                password: password
            })
        })
        .then(response => {
            return response.text().then(text => {
                try {
                    return { json: JSON.parse(text), isJson: true };
                } catch (e) {
                    return { text: text, isJson: false };
                }
            });
        })
        .then(data => {
            if (data.isJson && data.json.success) {
                // Login successful
                showMessage('Login successful! Redirecting...', 'success');
                
                // Store auth data
                try {
                    localStorage.setItem('authToken', data.json.data.token);
                    localStorage.setItem('userData', JSON.stringify(data.json.data.user));
                } catch (e) {
                    console.error('Failed to store auth data:', e);
                }
                
                // Redirect to dashboard after a short delay
                setTimeout(() => {
                    window.location.href = config.dashboardRedirect;
                }, 1500);
            } else {
                // Login failed
                const errorMsg = data.isJson ? (data.json.message || 'Login failed') : 'Server returned an invalid response';
                showMessage(errorMsg, 'error');
                hideSpinner();
            }
        })
        .catch(error => {
            showMessage('Unable to connect to server. Please try again later.', 'error');
            hideSpinner();
        });
    }
    
    // Activate debugger UI and functionality
    function activateDebugger() {
        createDebugInterface();
        runInitialChecks();
        isDebuggerActive = true;
    }
    
    // Create debug interface
    function createDebugInterface() {
        // Create debug container if it doesn't exist
        if (!document.getElementById('login-debugger')) {
            debugContainer = document.createElement('div');
            debugContainer.id = 'login-debugger';
            debugContainer.className = 'login-debug-container';
            debugContainer.style.cssText = 'position: fixed; bottom: 0; right: 0; width: 350px; max-height: 400px; background: rgba(0,0,0,0.8); color: #fff; padding: 10px; font-family: monospace; font-size: 12px; overflow-y: auto; z-index: 9999; border-top-left-radius: 5px;';
            
            // Add header with controls
            const header = document.createElement('div');
            header.innerHTML = `
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <strong>BiTrader Login Debugger</strong>
                    <div>
                        <button id="clear-debug" style="background: #555; color: white; border: none; padding: 2px 5px; font-size: 10px; margin-right: 5px;">Clear</button>
                        <button id="close-debug" style="background: #f44; color: white; border: none; padding: 2px 5px; font-size: 10px;">×</button>
                    </div>
                </div>
                <div id="debug-log" style="max-height: 360px; overflow-y: auto;"></div>
            `;
            debugContainer.appendChild(header);
            
            // Add controls
            document.body.appendChild(debugContainer);
            
            // Add event listeners for controls
            document.getElementById('clear-debug').addEventListener('click', function() {
                document.getElementById('debug-log').innerHTML = '';
            });
            
            document.getElementById('close-debug').addEventListener('click', function() {
                debugContainer.style.display = 'none';
                isDebuggerActive = false;
            });
        } else {
            // Just make it visible if it already exists
            debugContainer = document.getElementById('login-debugger');
            debugContainer.style.display = 'block';
        }
        
        logDebug('Debug console activated - Press Ctrl+Shift+D to toggle');
    }
    
    // Log message to debugger
    function logDebug(message, type = 'info') {
        if (!isDebuggerActive) return;
        
        const log = document.getElementById('debug-log');
        if (!log) return;
        
        const colors = {
            'info': '#aaa',
            'success': '#4d4',
            'warning': '#fa0',
            'error': '#f44',
            'request': '#0af',
            'response': '#8f8'
        };
        
        const msgElement = document.createElement('div');
        msgElement.style.cssText = `margin-bottom: 5px; color: ${colors[type] || colors.info}; word-wrap: break-word;`;
        
        const timestamp = new Date().toLocaleTimeString();
        msgElement.innerHTML = `<span style="color: #888;">[${timestamp}]</span> ${message}`;
        
        log.appendChild(msgElement);
        log.scrollTop = log.scrollHeight;
        
        // Also log to console
        const consoleMethod = type === 'error' ? 'error' : type === 'warning' ? 'warn' : 'log';
        console[consoleMethod](`[Login Debugger] ${message}`);
    }
    
    // Run initial checks
    function runInitialChecks() {
        logDebug('Running initial checks...', 'info');
        
        // Check local storage
        try {
            localStorage.setItem('debug_test', 'test');
            if (localStorage.getItem('debug_test') === 'test') {
                logDebug('Local Storage: Available', 'success');
                localStorage.removeItem('debug_test');
            } else {
                logDebug('Local Storage: Error - can write but not read', 'error');
            }
        } catch (e) {
            logDebug(`Local Storage: Error - ${e.message}`, 'error');
        }
        
        // Check authentication status
        const token = localStorage.getItem('authToken');
        const userData = localStorage.getItem('userData');
        
        if (token) {
            logDebug('Auth Token: Found in localStorage', 'success');
        } else {
            logDebug('Auth Token: Not found', 'info');
        }
        
        if (userData) {
            try {
                const user = JSON.parse(userData);
                logDebug(`User Data: Found for ${user.username || 'unknown user'}`, 'success');
            } catch (e) {
                logDebug('User Data: Found but invalid JSON', 'error');
            }
        } else {
            logDebug('User Data: Not found', 'info');
        }
        
        // Test connectivity to API
        testApiConnectivity();
    }
    
    // Test connectivity to API
    function testApiConnectivity() {
        logDebug('Testing API connectivity...', 'info');
        
        // Use a HEAD request to check if server is reachable
        fetch(`${config.apiBaseUrl}/cors_test.php`, {
            method: 'HEAD',
            cache: 'no-cache'
        })
        .then(response => {
            if (response.ok) {
                logDebug(`API Server: Reachable (Status: ${response.status})`, 'success');
            } else {
                logDebug(`API Server: Error (Status: ${response.status})`, 'error');
            }
        })
        .catch(error => {
            logDebug(`API Server: Unreachable - ${error.message}`, 'error');
            logDebug('Possible causes: CORS issues, server down, or network problem', 'warning');
        });
    }
    
    // Debug login process
    function debugLogin() {
        logDebug('--- Starting Login Process ---', 'info');
        
        // Get login credentials
        const username = document.getElementById('signin-username').value.trim();
        const password = document.getElementById('signin-password').value;
        
        // Validate
        if (!username || !password) {
            logDebug('Validation Failed: Username and password required', 'error');
            showMessage('Please enter both username and password', 'error');
            return;
        }
        
        logDebug(`Attempting login for user: ${username}`, 'info');
        showSpinner();
        
        // Make login request
        const loginUrl = `${config.apiBaseUrl}${config.loginEndpoint}`;
        logDebug(`API Request: ${loginUrl}`, 'request');
        
        fetch(loginUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                password: password
            })
        })
        .then(response => {
            logDebug(`Response Status: ${response.status} ${response.statusText}`, response.ok ? 'success' : 'error');
            
            // Log response headers
            const headers = {};
            response.headers.forEach((value, name) => {
                headers[name] = value;
            });
            logDebug(`Response Headers: ${JSON.stringify(headers)}`, 'info');
            
            // Get response text
            return response.text().then(text => {
                if (!text || text.trim() === '') {
                    logDebug('Response Body: Empty', 'error');
                    return { 
                        status: response.status,
                        isEmpty: true
                    };
                }
                
                // Try to parse as JSON
                try {
                    const json = JSON.parse(text);
                    logDebug(`Response Body: ${JSON.stringify(json)}`, 'response');
                    return { 
                        status: response.status,
                        json: json,
                        isJson: true
                    };
                } catch (e) {
                    logDebug(`Response Body (not JSON): ${text.substring(0, 150)}${text.length > 150 ? '...' : ''}`, 'response');
                    logDebug(`JSON Parse Error: ${e.message}`, 'error');
                    return { 
                        status: response.status,
                        text: text,
                        isJson: false
                    };
                }
            });
        })
        .then(data => {
            // Process login response
            if (data.isEmpty) {
                logDebug('Login failed: Empty response from server', 'error');
                showMessage('Server returned an empty response. Please try again.', 'error');
                hideSpinner();
                return;
            }
            
            if (!data.isJson) {
                logDebug('Login failed: Response is not valid JSON', 'error');
                showMessage('Server returned an invalid response. Please try again.', 'error');
                hideSpinner();
                return;
            }
            
            // Handle JSON response
            if (data.json.success) {
                logDebug('Login successful!', 'success');
                
                // Check token and user data
                if (!data.json.data || !data.json.data.token) {
                    logDebug('Login response missing token', 'error');
                    showMessage('Authentication error: Missing token', 'error');
                    hideSpinner();
                    return;
                }
                
                if (!data.json.data.user) {
                    logDebug('Login response missing user data', 'error');
                    showMessage('Authentication error: Missing user data', 'error');
                    hideSpinner();
                    return;
                }
                
                // Store auth data
                try {
                    localStorage.setItem('authToken', data.json.data.token);
                    localStorage.setItem('userData', JSON.stringify(data.json.data.user));
                    logDebug('Authentication data stored successfully', 'success');
                } catch (e) {
                    logDebug(`Failed to store auth data: ${e.message}`, 'error');
                    showMessage('Warning: Could not store login data locally', 'warning');
                }
                
                // Show success message
                showMessage('Login successful! Redirecting...', 'success');
                
                // Prepare for redirect
                logDebug(`Preparing to redirect to: ${config.dashboardRedirect}`, 'info');
                
                // Redirect with delay
                setTimeout(() => {
                    try {
                        window.location.href = config.dashboardRedirect;
                        logDebug('Redirect initiated', 'info');
                    } catch (e) {
                        logDebug(`Redirect failed: ${e.message}`, 'error');
                        showMessage('Error during redirect. Please go to the dashboard manually.', 'error');
                    }
                }, 1500);
            } else {
                // Login failed
                const errorMsg = data.json.message || 'Invalid username or password';
                logDebug(`Login failed: ${errorMsg}`, 'error');
                showMessage(errorMsg, 'error');
                hideSpinner();
            }
        })
        .catch(error => {
            logDebug(`Login request failed: ${error.message}`, 'error');
            showMessage('Unable to connect to server. Please try again later.', 'error');
            hideSpinner();
        });
    }
    
    // Show message
    function showMessage(message, type = 'info') {
        // Remove any existing messages
        const existingMsgs = document.querySelectorAll('.auth-message');
        existingMsgs.forEach(msg => msg.remove());
        
        // Create message element
        const msgDiv = document.createElement('div');
        msgDiv.className = `auth-message alert alert-${type === 'error' ? 'danger' : (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'info'))}`;
        msgDiv.textContent = message;
        
        // Insert before the form
        const form = document.querySelector('#signin-tab-pane .auth-form');
        if (form && form.parentNode) {
            form.parentNode.insertBefore(msgDiv, form);
        }
        
        if (isDebuggerActive) {
            logDebug(`Message displayed: ${message}`, type);
        }
    }
    
    // Show spinner
    function showSpinner() {
        // Remove any existing spinner
        hideSpinner();
        
        // Create spinner
        const spinner = document.createElement('div');
        spinner.className = 'auth-spinner';
        spinner.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); display: flex; justify-content: center; align-items: center; z-index: 9998;';
        spinner.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        document.body.appendChild(spinner);
        
        if (isDebuggerActive) {
            logDebug('Loading spinner displayed');
        }
    }
    
    // Hide spinner
    function hideSpinner() {
        const spinner = document.querySelector('.auth-spinner');
        if (spinner) {
            spinner.remove();
            if (isDebuggerActive) {
                logDebug('Loading spinner hidden');
            }
        }
    }
    
    // Initialize when DOM is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDebugger);
    } else {
        // DOM already loaded
        initDebugger();
    }
})(); 