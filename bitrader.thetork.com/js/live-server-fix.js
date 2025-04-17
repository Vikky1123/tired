/**
 * Live Server Fix Script
 * This script fixes issues when running the site through Live Server (127.0.0.1:5500)
 */

console.log("Live Server Fix loaded");

document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on Live Server
    if (window.location.href.includes('127.0.0.1:5500') || window.location.href.includes('localhost:5500')) {
        console.log("Live Server detected - applying fixes");
        
        // Create placeholders for missing scripts
        const missingScripts = {
            'bootstrap.bundle.min.js': `
                // Bootstrap Bundle placeholder
                console.log("Bootstrap Bundle placeholder loaded");
                // Just enough to prevent errors
                window.bootstrap = {
                    Tooltip: function() { return { dispose: function() {} }; },
                    Popover: function() { return { dispose: function() {} }; },
                    Modal: function() { return { show: function() {}, hide: function() {} }; },
                    Collapse: function() { return { show: function() {}, hide: function() {} }; }
                };
            `,
            'swiper-bundle.min.js': `
                // Swiper Bundle placeholder
                console.log("Swiper Bundle placeholder loaded");
                // Just enough to prevent errors
                window.Swiper = function() { 
                    return { 
                        on: function() {}, 
                        destroy: function() {},
                        update: function() {}
                    }; 
                };
            `,
            'bootstrap.min.js': `
                // Bootstrap placeholder 
                console.log("Bootstrap placeholder loaded");
                // Functionality already provided by bootstrap.bundle if available
            `,
            'aos.js': `
                // AOS (Animate On Scroll) placeholder
                console.log("AOS placeholder loaded");
                // Just enough to prevent errors
                window.AOS = {
                    init: function() {},
                    refresh: function() {}
                };
            `,
            'purecounter.js': `
                // PureCounter placeholder
                console.log("PureCounter placeholder loaded");
                // Just enough to prevent errors
                window.PureCounter = function() {};
            `,
            'custom.js': `
                // Custom JS placeholder
                console.log("Custom JS placeholder loaded");
                
                // Basic functionality for the theme mode toggle
                document.addEventListener('DOMContentLoaded', function() {
                    const themeToggle = document.getElementById('btnSwitch');
                    if (themeToggle) {
                        themeToggle.addEventListener('click', function() {
                            document.body.classList.toggle('dark-mode');
                            
                            // Store preference
                            const isDarkMode = document.body.classList.contains('dark-mode');
                            localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');
                        });
                        
                        // Check for saved preference
                        if (localStorage.getItem('darkMode') === 'enabled') {
                            document.body.classList.add('dark-mode');
                        }
                    }
                });
            `
        };
        
        // Create and inject scripts into page head
        Object.keys(missingScripts).forEach(function(scriptName) {
            const scriptContent = missingScripts[scriptName];
            
            // Create script element
            const script = document.createElement('script');
            script.type = 'application/javascript';
            script.textContent = scriptContent;
            
            // Add to document head
            document.head.appendChild(script);
            console.log(`Injected placeholder for ${scriptName}`);
        });
        
        // Fix authentication across Live Server 
        console.log("Setting up auth configuration for Live Server");
        
        // Override any absolute URLs in authentication code
        window.PROJECT_API_BASE = 'http://localhost/PROJECT-BITRADER/backend';
        
        // Inject helper function for API calls
        const helperScript = document.createElement('script');
        helperScript.type = 'application/javascript';
        helperScript.textContent = `
            // API Helper for Live Server
            function getLiveServerApiUrl(endpoint) {
                // Remove leading slash if present
                if (endpoint.startsWith('/')) {
                    endpoint = endpoint.substring(1);
                }
                return window.PROJECT_API_BASE + '/' + endpoint;
            }
            
            // Provide a hook for fetch operations
            const originalFetch = window.fetch;
            window.fetch = function(url, options) {
                // If the URL contains a backend API endpoint but doesn't have the full path
                if (typeof url === 'string' && 
                    (url.includes('direct-login.php') || 
                     url.includes('direct-register.php') || 
                     url.includes('api/')) && 
                    !url.includes('PROJECT-BITRADER')) {
                    
                    console.log('Redirecting API call to PROJECT_API_BASE:', url);
                    url = window.PROJECT_API_BASE + '/' + url.split('/').pop();
                    console.log('New URL:', url);
                }
                
                return originalFetch(url, options);
            };
        `;
        document.head.appendChild(helperScript);
        
        // Apply specific fixes for the login form
        if (document.querySelector('#signin-tab-pane .auth-form')) {
            console.log("Found login form - applying live server specific fixes");
            
            // Create a helper that will ensure login works
            const loginHelperScript = document.createElement('script');
            loginHelperScript.type = 'application/javascript';
            loginHelperScript.textContent = `
                // Add Live Server specific login handler
                document.addEventListener('DOMContentLoaded', function() {
                    const signinForm = document.querySelector('#signin-tab-pane .auth-form');
                    const submitButton = signinForm ? signinForm.querySelector('button[type="submit"]') : null;
                    
                    if (signinForm && submitButton) {
                        console.log('Live Server: Applying login handler override');
                        
                        // Display an indicator that we're in Live Server mode
                        const liveServerIndicator = document.createElement('div');
                        liveServerIndicator.style.position = 'fixed';
                        liveServerIndicator.style.top = '10px';
                        liveServerIndicator.style.right = '10px';
                        liveServerIndicator.style.backgroundColor = 'rgba(255, 0, 0, 0.8)';
                        liveServerIndicator.style.color = 'white';
                        liveServerIndicator.style.padding = '5px 10px';
                        liveServerIndicator.style.borderRadius = '5px';
                        liveServerIndicator.style.zIndex = '9999';
                        liveServerIndicator.textContent = 'Live Server Mode';
                        document.body.appendChild(liveServerIndicator);
                        
                        // Clear any previous click listeners and apply our own
                        const newButton = submitButton.cloneNode(true);
                        submitButton.parentNode.replaceChild(newButton, submitButton);
                        
                        newButton.addEventListener('click', function(event) {
                            event.preventDefault();
                            
                            // Get login credentials
                            const username = document.getElementById('signin-username').value.trim();
                            const password = document.getElementById('signin-password').value;
                            
                            // Show indicator that we're handling the login
                            const msgDiv = document.createElement('div');
                            msgDiv.className = 'auth-message alert alert-info';
                            msgDiv.textContent = 'Live Server: Handling login via backend at ' + window.PROJECT_API_BASE;
                            
                            // Insert before the form
                            const form = document.querySelector('#signin-tab-pane .auth-form');
                            if (form && form.parentNode) {
                                // Remove any existing messages
                                const existingMsgs = document.querySelectorAll('.auth-message');
                                existingMsgs.forEach(msg => msg.remove());
                                
                                form.parentNode.insertBefore(msgDiv, form);
                            }
                            
                            // Make the API call to the backend
                            fetch(window.PROJECT_API_BASE + '/direct-login.php', {
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
                            .then(response => response.text())
                            .then(text => {
                                console.log('Live Server: Login response', text);
                                try {
                                    const data = JSON.parse(text);
                                    if (data.success) {
                                        msgDiv.className = 'auth-message alert alert-success';
                                        msgDiv.textContent = 'Login successful! Redirecting...';
                                        
                                        // Store auth data
                                        localStorage.setItem('authToken', data.data.token);
                                        localStorage.setItem('userData', JSON.stringify(data.data.user));
                                        
                                        // Redirect to dashboard
                                        setTimeout(() => {
                                            window.location.href = '../../coinex/dashboard/index.php';
                                        }, 1000);
                                    } else {
                                        msgDiv.className = 'auth-message alert alert-danger';
                                        msgDiv.textContent = data.message || 'Login failed. Please check your credentials.';
                                    }
                                } catch (e) {
                                    msgDiv.className = 'auth-message alert alert-danger';
                                    msgDiv.textContent = 'Error processing response: ' + e.message;
                                    console.error('Live Server: JSON parse error', e);
                                }
                            })
                            .catch(error => {
                                msgDiv.className = 'auth-message alert alert-danger';
                                msgDiv.textContent = 'Network error: ' + error.message;
                                console.error('Live Server: Fetch error', error);
                            });
                        });
                    }
                });
            `;
            document.head.appendChild(loginHelperScript);
        }
    }
}); 