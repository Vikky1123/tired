/**
 * BitRader Login and Authentication Script
 * This script handles login, signup, and sets authentication data in localStorage.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if we're already logged in
    const token = localStorage.getItem('authToken');
    const userDataStr = localStorage.getItem('userData');
    
    if (token && userDataStr) {
        try {
            // If valid data exists, redirect to dashboard
            JSON.parse(userDataStr); // Test if valid JSON
            window.location.href = '../../coinex/dashboard/index.php';
        } catch (error) {
            // Invalid data, remove it
            localStorage.removeItem('authToken');
            localStorage.removeItem('userData');
        }
    }
    
    // API URLs
    const API_BASE_URL = 'http://localhost/PROJECT-BITRADER/backend';
    const LOGIN_API = `${API_BASE_URL}/direct-login.php`;
    const REGISTER_API = `${API_BASE_URL}/direct-register.php`;
    
    // Get form elements
    const signinForm = document.querySelector('#signin-tab-pane .auth-form');
    const signupForm = document.querySelector('#signup-tab-pane .auth-form');
    
    // Add event listeners to forms
    if (signinForm) {
        signinForm.addEventListener('submit', function(event) {
            handleSignIn(event);
        });
    }
    
    if (signupForm) {
        signupForm.addEventListener('submit', function(event) {
            handleSignUp(event);
        });
    }
    
    // Sign In Handler
    function handleSignIn(event) {
        event.preventDefault();
        
        // Get form values
        const username = document.getElementById('signin-username').value.trim();
        const password = document.getElementById('signin-password').value;
        const rememberMe = document.getElementById('remember-me').checked;
        
        // Validate inputs
        if (!username || !password) {
            showError('Please enter both username and password');
            return;
        }
        
        // Show loading state
        showSpinner();
        
        // Send login request to API
        fetch(LOGIN_API, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                password: password
            })
        })
        .then(response => {
            // First check if the response is ok
            if (!response.ok) {
                throw new Error(`Server responded with status: ${response.status}`);
            }
            
            // Try to parse as JSON
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error("Server returned invalid JSON");
                }
            });
        })
        .then(data => {
            if (data.success) {
                // Login successful
                showSuccess('Login successful! Redirecting...');
                
                // Check if data has the correct structure
                if (!data.data || !data.data.token || !data.data.user) {
                    throw new Error("Invalid response format");
                }
                
                // Save token and user data to local storage
                localStorage.setItem('authToken', data.data.token);
                localStorage.setItem('userData', JSON.stringify(data.data.user));
                
                // Redirect to dashboard after a short delay
                setTimeout(() => {
                    window.location.href = '../../coinex/dashboard/index.php';
                }, 1500);
            } else {
                // Login failed
                showError(data.message || 'Invalid username or password. Please try again.');
            }
        })
        .catch(error => {
            // Show error message
            showError('Unable to connect to server. Please try again later.');
        })
        .finally(() => {
            hideSpinner();
        });
    }
    
    // Sign Up Handler
    function handleSignUp(event) {
        event.preventDefault();
        
        // Get form values
        const username = document.getElementById('signup-username').value.trim();
        const fullName = document.getElementById('signup-fullname').value.trim();
        const email = document.getElementById('signup-email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const country = document.getElementById('country').value;
        const password = document.getElementById('signup-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        const termsAgreed = document.getElementById('terms-agree').checked;
        
        // Validate form
        if (!username || !fullName || !email || !phone || !country || !password || !confirmPassword) {
            showError('Please fill in all required fields');
            return;
        }
        
        if (password !== confirmPassword) {
            showError('Passwords do not match');
            return;
        }
        
        if (!termsAgreed) {
            showError('You must agree to the Terms and Conditions');
            return;
        }
        
        // Show spinner
        showSpinner();
        
        // Send registration request to API
        fetch(REGISTER_API, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                full_name: fullName,
                email: email,
                phone: phone,
                country: country,
                password: password
            })
        })
        .then(response => {
            // First check if the response is ok
            if (!response.ok) {
                throw new Error(`Server responded with status: ${response.status}`);
            }
            
            // Try to parse as JSON
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error("Server returned invalid JSON");
                }
            });
        })
        .then(data => {
            if (data.success) {
                // Registration successful
                showSuccess('Account created successfully! Redirecting to dashboard...');
                
                // Check if data has the correct structure
                if (!data.data || !data.data.token || !data.data.user) {
                    throw new Error("Invalid response format");
                }
                
                // Save token and user data to local storage
                localStorage.setItem('authToken', data.data.token);
                localStorage.setItem('userData', JSON.stringify(data.data.user));
                
                // Redirect to dashboard after a short delay
                setTimeout(() => {
                    window.location.href = '../../coinex/dashboard/index.php';
                }, 1500);
            } else {
                // Registration failed
                showError(data.message || 'Registration failed. Please try again.');
            }
        })
        .catch(error => {
            showError('Unable to connect to server. Please try again later.');
        })
        .finally(() => {
            hideSpinner();
        });
    }
    
    // Set authentication data in localStorage or sessionStorage
    function setAuthData(userData, token, rememberMe) {
        if (rememberMe) {
            localStorage.setItem('authToken', token);
            localStorage.setItem('userData', JSON.stringify(userData));
        } else {
            sessionStorage.setItem('authToken', token);
            sessionStorage.setItem('userData', JSON.stringify(userData));
        }
    }
    
    // Show loading spinner
    function showSpinner() {
        const spinner = document.createElement('div');
        spinner.className = 'auth-spinner';
        spinner.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        document.body.appendChild(spinner);
    }
    
    // Hide loading spinner
    function hideSpinner() {
        const spinner = document.querySelector('.auth-spinner');
        if (spinner) spinner.remove();
    }
    
    // Show error message
    function showError(message) {
        // Create error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'auth-message alert alert-danger';
        errorDiv.textContent = message;
        
        // Show on active tab
        const activeTab = document.querySelector('.tab-pane.active .auth-form');
        if (activeTab) {
            // Remove any existing messages
            const existingMessages = activeTab.parentNode.querySelectorAll('.auth-message');
            existingMessages.forEach(msg => msg.remove());
            
            // Add new message
            activeTab.parentNode.insertBefore(errorDiv, activeTab);
        }
    }
    
    // Show success message
    function showSuccess(message) {
        // Create success message
        const successDiv = document.createElement('div');
        successDiv.className = 'auth-message alert alert-success';
        successDiv.textContent = message;
        
        // Show on active tab
        const activeTab = document.querySelector('.tab-pane.active .auth-form');
        if (activeTab) {
            // Remove any existing messages
            const existingMessages = activeTab.parentNode.querySelectorAll('.auth-message');
            existingMessages.forEach(msg => msg.remove());
            
            // Add new message
            activeTab.parentNode.insertBefore(successDiv, activeTab);
        }
    }
    
    // Password visibility toggle
    window.togglePasswordVisibility = function(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    };
}); 