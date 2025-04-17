// Auth Status Handler for Navigation Buttons
document.addEventListener('DOMContentLoaded', function() {
    // Function to update button based on auth status
    function updateJoinButton() {
        // Find all Join Now buttons in header
        const headerBtns = document.querySelectorAll('.header-btn');
        const isLoggedIn = localStorage.getItem('authToken') && localStorage.getItem('userData');
        
        headerBtns.forEach(btnContainer => {
            const link = btnContainer.querySelector('a');
            if (!link) return;
            
            if (isLoggedIn) {
                // Update button for logged-in users
                link.innerHTML = `
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 4H5C4.44772 4 4 4.44772 4 5V19C4 19.5523 4.44772 20 5 20H19C19.5523 20 20 19.5523 20 19V5C20 4.44772 19.5523 4 19 4H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M12 12V3M12 3L9 6M12 3L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Dashboard
                    </span>`;
                link.href = '/coinex/dashboard/index.php';
                link.style.display = 'flex';
                link.style.alignItems = 'center';
                link.style.justifyContent = 'center';
            } else {
                // Keep default state for non-logged-in users
                link.innerHTML = '<span>Join Now</span>';
                link.href = '/bitrader.thetork.com/Signup-Signin/index.html';
            }
        });
    }

    // Initial update
    updateJoinButton();

    // Listen for auth status changes
    window.addEventListener('storage', function(e) {
        if (e.key === 'authToken' || e.key === 'userData') {
            updateJoinButton();
        }
    });

    // Optional: Check auth status periodically
    setInterval(updateJoinButton, 5000);
});

// Authentication Status Check
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('authToken');
    const userDataStr = localStorage.getItem('userData');
    const BASE_URL = window.location.origin;

    // Check if user is on a protected page
    const protectedPages = [
        '/dashboard/',
        '/profile/',
        '/settings/',
        '/transactions/'
    ];

    const currentPath = window.location.pathname;
    const isProtectedPage = protectedPages.some(page => currentPath.includes(page));

    if (isProtectedPage) {
        if (!token || !userDataStr) {
            window.location.href = BASE_URL + '/bitrader.thetork.com/Signup-Signin/index.html';
            return;
        }

        try {
            const userData = JSON.parse(userDataStr);
            if (!userData || !userData.id) {
                throw new Error('Invalid user data');
            }
        } catch (error) {
            console.error('Error validating authentication:', error);
            localStorage.removeItem('authToken');
            localStorage.removeItem('userData');
            window.location.href = BASE_URL + '/bitrader.thetork.com/Signup-Signin/index.html';
        }
    } else {
        // For non-protected pages, update UI based on auth status
        const loginButtons = document.querySelectorAll('.login-button, .signin-button');
        const profileButtons = document.querySelectorAll('.profile-button, .dashboard-button');
        
        if (token && userDataStr) {
            loginButtons.forEach(btn => btn.style.display = 'none');
            profileButtons.forEach(btn => btn.style.display = 'inline-block');
        } else {
            loginButtons.forEach(btn => btn.style.display = 'inline-block');
            profileButtons.forEach(btn => btn.style.display = 'none');
        }
    }
}); 