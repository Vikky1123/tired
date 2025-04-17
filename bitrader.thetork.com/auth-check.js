/**
 * BitRader Authentication Check Script
 * This script checks if a user is authenticated by verifying tokens in localStorage
 * and updates UI elements accordingly.
 */

// Auth Check Script for Navigation Bar
document.addEventListener('DOMContentLoaded', function() {
    // Function to determine base path
    function getBasePath() {
        // This is a simple way to get the base path that should work for our project structure
        return '../../';
    }
    
    // Function to update navigation buttons based on auth status
    function updateNavigation() {
        const token = localStorage.getItem('authToken');
        const userDataStr = localStorage.getItem('userData');
        const isLoggedIn = token && userDataStr;
        const basePath = getBasePath();
        
        try {
            // Update all Join Now buttons in header navigation
            const headerBtns = document.querySelectorAll('.header-btn');
            
            headerBtns.forEach(btnContainer => {
                const link = btnContainer.querySelector('a');
                if (!link) return;
                
                if (isLoggedIn) {
                    // Update to Dashboard button for logged-in users
                    link.innerHTML = `
                        <span style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 4H5C4.44772 4 4 4.44772 4 5V19C4 19.5523 4.44772 20 5 20H19C19.5523 20 20 19.5523 20 19V5C20 4.44772 19.5523 4 19 4H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M12 12V3M12 3L9 6M12 3L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Dashboard
                        </span>`;
                    link.href = basePath + 'coinex/dashboard/index.php';
                    link.style.display = 'flex';
                    link.style.alignItems = 'center';
                    link.style.justifyContent = 'center';
                } else {
                    // Set to Join Now for non-logged-in users
                    link.innerHTML = '<span>Join Now</span>';
                    link.href = basePath + 'bitrader.thetork.com/Signup-Signin/index.html';
                }
            });
            
            // Additional selector for different types of auth buttons across the site
            const otherAuthButtons = document.querySelectorAll('.menu-btn, .join-now, .signin-btn, .auth-btn, .login-button');
            
            otherAuthButtons.forEach(btn => {
                if (!btn) return;
                
                // Skip if this is inside a header-btn (already handled above)
                if (btn.closest('.header-btn')) return;
                
                const link = btn.tagName.toLowerCase() === 'a' ? btn : btn.querySelector('a');
                if (!link) return;
                
                if (isLoggedIn) {
                    if (link.textContent.trim().toLowerCase().includes('join now') || 
                        link.textContent.trim().toLowerCase().includes('sign in')) {
                        if (link.classList.contains('signin-link')) {
                            link.innerHTML = '<span>Dashboard</span>';
                        } else {
                            link.innerHTML = '<span>Dashboard</span>';
                        }
                        link.href = basePath + 'coinex/dashboard/index.php';
                    }
                }
            });
            
            // Also update mobile navigation menu if it exists
            const mobileNavButtons = document.querySelectorAll('.mobile-nav .join-btn, .mobile-menu .auth-btn');
            mobileNavButtons.forEach(btn => {
                if (!btn) return;
                
                if (isLoggedIn) {
                    btn.innerHTML = 'Dashboard';
                    btn.href = basePath + 'coinex/dashboard/index.php';
                } else {
                    btn.innerHTML = 'Join Now';
                    btn.href = basePath + 'bitrader.thetork.com/Signup-Signin/index.html';
                }
            });
            
            // Fire a custom event for other scripts to know the user's auth status
            document.dispatchEvent(new CustomEvent('authStatusUpdated', {
                detail: { isLoggedIn, userData: isLoggedIn ? JSON.parse(userDataStr) : null }
            }));
            
        } catch (error) {
            console.error('Error updating navigation:', error);
        }
    }

    // Initial update when page loads
    updateNavigation();

    // Listen for auth status changes (in case user logs in/out in another tab)
    window.addEventListener('storage', function(e) {
        if (e.key === 'authToken' || e.key === 'userData') {
            updateNavigation();
        }
    });

    // Periodically check auth status
    setInterval(updateNavigation, 3000);
}); 