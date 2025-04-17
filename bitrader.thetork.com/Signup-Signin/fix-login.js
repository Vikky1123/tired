/**
 * BiTrader Login Fix Script
 * This script handles login process and authentication.
 */

// Self-executing function to avoid global scope pollution
(function() {
    // Function to run when DOM is ready
    function initLoginFix() {
        // Get the login form and button
        const signinForm = document.querySelector('#signin-tab-pane .auth-form');
        const submitButton = signinForm ? signinForm.querySelector('button[type="submit"]') : null;
        
        if (signinForm && submitButton) {
            // Clear any previous click listeners
            const newButton = submitButton.cloneNode(true);
            submitButton.parentNode.replaceChild(newButton, submitButton);
            
            // Add our fixed login handler
            newButton.addEventListener('click', function(event) {
                event.preventDefault();
                handleFixedLogin();
            });
            
            // Also handle form submit
            signinForm.addEventListener('submit', function(event) {
                event.preventDefault();
                handleFixedLogin();
            });
        }
        
        // Initialize dark mode and fix styling issues
        fixThemeAndStyleIssues();
    }
    
    // Enhanced login handler with better error handling
    function handleFixedLogin() {
        try {
            // Clear any previous error messages
            clearMessages();
            
            // Get login credentials
            const username = document.getElementById('signin-username').value.trim();
            const password = document.getElementById('signin-password').value;
            
            // Validate
            if (!username || !password) {
                showMessage('Please enter both username and password', 'error');
                return;
            }
            
            // Show spinner
            showSpinner();
            
            // Make login request with explicit content type
            const apiUrl = 'http://localhost/PROJECT-BITRADER/backend/direct-login.php';
            
            fetch(apiUrl, {
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
                // Always get text first, don't assume it's JSON
                return response.text().then(text => {
                    // Check if empty response
                    if (!text || text.trim() === '') {
                        return { 
                            status: response.status,
                            text: 'Empty response received',
                            isJson: false,
                            parseError: 'Empty response'
                        };
                    }
                    
                    // Try to parse as JSON
                    let jsonData;
                    try {
                        jsonData = JSON.parse(text);
                        return { 
                            status: response.status,
                            json: jsonData,
                            isJson: true
                        };
                    } catch (e) {
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
                    if (data.json.success) {
                        showMessage('Login successful! Redirecting...', 'success');
                        
                        // Store auth data
                        try {
                            localStorage.setItem('authToken', data.json.data.token);
                            localStorage.setItem('userData', JSON.stringify(data.json.data.user));
                        } catch (e) {
                            showMessage('Warning: Could not store login data locally. You may need to login again later.', 'warning');
                        }
                        
                        // Redirect to dashboard after a short delay
                        setTimeout(() => {
                            try {
                                // Try to use relative path first
                                const dashboardPath = '../../coinex/dashboard/index.php';
                                window.location.href = dashboardPath;
                            } catch (e) {
                                showMessage('Error during redirect. Please go to the dashboard manually.', 'error');
                            }
                        }, 1000);
                    } else {
                        showMessage(data.json.message || 'Login failed. Please check your credentials.', 'error');
                        hideSpinner();
                    }
                } else {
                    // If not JSON or couldn't parse
                    showMessage('Received invalid response from server. Please try again.', 'error');
                    hideSpinner();
                }
            })
            .catch(error => {
                showMessage('Error connecting to server: ' + error.message, 'error');
                hideSpinner();
            });
        } catch (error) {
            showMessage('An unexpected error occurred. Please try again later.', 'error');
            hideSpinner();
        }
    }
    
    // Fix theme and style issues that could be causing problems
    function fixThemeAndStyleIssues() {
        // Set consistent theme to prevent theme-related errors
        const htmlElement = document.documentElement;
        if (htmlElement) {
            // Set data attribute for Bootstrap 5 theme
            htmlElement.setAttribute('data-bs-theme', 'light');
        }
        
        // Fix missing images or broken references
        document.querySelectorAll('img').forEach(img => {
            img.onerror = function() {
                // Replace broken image with a placeholder or hide it
                this.style.display = 'none';
            };
        });
        
        // Ensure body has proper theme class
        document.body.classList.add('light-mode');
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
        
        // Safety timeout to hide spinner after 30 seconds
        setTimeout(hideSpinner, 30000);
    }
    
    // Hide spinner
    function hideSpinner() {
        const spinner = document.querySelector('.auth-spinner');
        if (spinner) {
            spinner.remove();
        }
    }
    
    // Initialize when DOM is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initLoginFix();
            // Do NOT auto-redirect on page load to prevent redirect loops
        });
    } else {
        // DOM already loaded
        initLoginFix();
        // Do NOT auto-redirect on page load to prevent redirect loops
    }
})(); 