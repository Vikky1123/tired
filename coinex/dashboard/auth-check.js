// Dashboard Authentication Check

document.addEventListener('DOMContentLoaded', function() {
    // Check if user is authenticated
    const token = localStorage.getItem('authToken');
    const userDataStr = localStorage.getItem('userData');
    
    if (!token || !userDataStr) {
        // No valid authentication found, redirect to login
        window.location.href = '../../Signup-Signin/index.html';
        return;
    }
    
    try {
        // Parse user data
        const userData = JSON.parse(userDataStr);
        
        // Update user info in dashboard if needed
        updateUserInfo(userData);
        
        // Setup logout functionality
        setupLogout();
    } catch (error) {
        console.error('Error parsing user data:', error);
        // Invalid user data, redirect to login
        localStorage.removeItem('authToken');
        localStorage.removeItem('userData');
        window.location.href = '../../Signup-Signin/index.html';
    }
});

// Function to update user information in the dashboard
function updateUserInfo(userData) {
    // Update username
    const usernameElements = document.querySelectorAll('.user-name');
    if (usernameElements.length) {
        usernameElements.forEach(element => {
            element.textContent = userData.username;
        });
    }
    
    // Update user role
    const roleElements = document.querySelectorAll('.user-role');
    if (roleElements.length) {
        roleElements.forEach(element => {
            element.textContent = userData.role;
        });
    }
    
    // Update user email
    const emailElements = document.querySelectorAll('.user-email');
    if (emailElements.length) {
        emailElements.forEach(element => {
            element.textContent = userData.email;
        });
    }
}

// Setup logout functionality
function setupLogout() {
    const logoutButtons = document.querySelectorAll('.logout-btn');
    
    if (logoutButtons.length) {
        logoutButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                
                // Clear authentication data
                localStorage.removeItem('authToken');
                localStorage.removeItem('userData');
                
                // Redirect to login page
                window.location.href = '../../Signup-Signin/index.html';
            });
        });
    }
} 