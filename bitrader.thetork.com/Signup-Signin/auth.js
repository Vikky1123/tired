// BiTrader Authentication Module

document.addEventListener('DOMContentLoaded', function() {
    // Get references to form elements
    const loginForm = document.querySelector('#signin-tab-pane .auth-form');
    const usernameInput = document.getElementById('signin-username');
    const passwordInput = document.getElementById('signin-password');
    const loginButton = loginForm.querySelector('.trk-btn');
    
    // API endpoint for authentication
    const API_BASE_URL = 'http://localhost/PROJECT-BITRADER/backend';
    const LOGIN_API = `${API_BASE_URL}/direct-login.php`;
    
    // Add event listener for form submission
    loginForm.addEventListener('submit', function(event) {
        // Prevent the default form submission
        event.preventDefault();
        
        // Show loading state on button
        loginButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Signing In...';
        loginButton.disabled = true;
        
        // Get values from form
        const username = usernameInput.value.trim();
        const password = passwordInput.value;
        
        // Validate inputs
        if (!username || !password) {
            showMessage('Please enter both username and password', 'error');
            resetButton();
            return;
        }
        
        // Send login request to the API
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
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Authentication successful
                showMessage('Login successful! Redirecting...', 'success');
                
                // Save token and user data to local storage
                localStorage.setItem('authToken', data.data.token);
                localStorage.setItem('userData', JSON.stringify(data.data.user));
                
                // Redirect to dashboard after a short delay
                setTimeout(() => {
                    window.location.href = '../../coinex/dashboard/index.php';
                }, 1000);
            } else {
                // Login failed
                showMessage(data.message || 'Invalid username or password. Please try again.', 'error');
                resetButton();
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            showMessage('Unable to connect to server. Please try again later.', 'error');
            resetButton();
        });
    });
    
    // Helper function to display messages
    function showMessage(message, type) {
        // Check if message container exists, if not create it
        let messageContainer = document.querySelector('.auth-message');
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.className = 'auth-message mt-3';
            loginForm.appendChild(messageContainer);
        }
        
        // Set message style based on type
        const className = type === 'error' ? 'alert-danger' : 'alert-success';
        messageContainer.className = `auth-message alert ${className} mt-3`;
        messageContainer.textContent = message;
        
        // Auto-hide message after 5 seconds if it's an error
        if (type === 'error') {
            setTimeout(() => {
                messageContainer.style.display = 'none';
            }, 5000);
        }
    }
    
    // Reset button state
    function resetButton() {
        loginButton.innerHTML = 'Sign In';
        loginButton.disabled = false;
    }
});

// Add authentication check to the dashboard page
function checkDashboardAuth() {
    // This function will be called in the dashboard to check for valid authentication
    const token = localStorage.getItem('authToken');
    const userData = localStorage.getItem('userData');
    
    if (!token || !userData) {
        // No valid authentication found, redirect to login
        window.location.href = '../../bitrader.thetork.com/Signup-Signin/index.html';
        return false;
    }
    
    return true;
} 