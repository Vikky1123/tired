// Dashboard Authentication Check with Absolute URLs

document.addEventListener('DOMContentLoaded', function() {
    // TEMPORARILY DISABLE ALL REDIRECT LOGIC 
    console.log('Client-side auth check disabled to prevent redirect loops');
    
    // Just run the UI updates if user data exists
    const token = localStorage.getItem('authToken');
    const userDataStr = localStorage.getItem('userData');
    
    if (token && userDataStr) {
        try {
            // Parse user data
            const userData = JSON.parse(userDataStr);
            
            // Update user info in dashboard if needed
            updateUserInfo(userData);
            
            // Setup logout functionality
            setupLogout();
            
            // Fix logo display issues
            fixLogos();
            
            // Fire a custom event for other scripts to know the user is logged in
            document.dispatchEvent(new CustomEvent('userAuthenticated', {
                detail: userData
            }));
        } catch (error) {
            console.error('Error parsing user data:', error);
        }
    }
});

// Update user information on the dashboard
function updateUserInfo(userData) {
    // Update username in the header
    const usernameElements = document.querySelectorAll('.user-name');
    usernameElements.forEach(element => {
        element.textContent = userData.name || userData.username;
    });
    
    // Update user avatar if available
    const avatarElements = document.querySelectorAll('.user-img');
    avatarElements.forEach(element => {
        if (userData.avatar) {
            element.src = userData.avatar;
            element.alt = userData.name || userData.username;
        }
    });
    
    // Update role or other user specific information if present on dashboard
    const roleElements = document.querySelectorAll('.user-role');
    roleElements.forEach(element => {
        element.textContent = userData.role || 'User';
    });
    
    // Update email if available
    const emailElements = document.querySelectorAll('.user-email');
    emailElements.forEach(element => {
        element.textContent = userData.email || '';
    });
}

// Setup logout functionality
function setupLogout() {
    const logoutButtons = document.querySelectorAll('.logout-btn, a[href*="logout"]');
    
    logoutButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Clear authentication data
            localStorage.removeItem('authToken');
            localStorage.removeItem('userData');
            localStorage.removeItem('authRedirectCount');
            localStorage.removeItem('preventRedirectLoop');
            
            // Redirect to login page
            window.location.href = '../../bitrader.thetork.com/Signup-Signin/index.html';
        });
    });
}

// Function to fix logo display issues
function fixLogos() {
    // Fix COINEX logo spelling in the sidebar
    const logoTitles = document.querySelectorAll('.logo-title');
    if (logoTitles.length) {
        logoTitles.forEach(element => {
            if (element.textContent === 'OINEX') {
                element.textContent = 'COINEX';
            }
        });
    }
    
    // Fix spacing between logo image and text
    const logoImages = document.querySelectorAll('.navbar-brand img');
    if (logoImages.length) {
        logoImages.forEach(img => {
            img.style.marginRight = '8px';
        });
    }
    
    // Ensure logo is visible
    const logoContainers = document.querySelectorAll('.navbar-brand');
    if (logoContainers.length) {
        logoContainers.forEach(container => {
            container.style.display = 'flex';
            container.style.alignItems = 'center';
        });
    }
}

// Helper function to set authentication data when user logs in
// This can be called from the login form
function setAuthData(userData, token) {
    localStorage.setItem('authToken', token);
    localStorage.setItem('userData', JSON.stringify(userData));
    
    // Fire storage event to notify other tabs/windows
    window.dispatchEvent(new StorageEvent('storage', {
        key: 'authToken',
        newValue: token
    }));
} 