/**
 * Bitrader Theme Mode JS
 * Handles persistent light/dark mode across pages
 */

document.addEventListener("DOMContentLoaded", function() {
    const toggleVersionButton = document.getElementById('btnSwitch');
    const htmlElement = document.documentElement;
    const icon = document.querySelector('#btnSwitch img');
    
    // Check if there's a main logo element
    const mainLogo = document.getElementById('main-logo');
    
    // Get default and dark logo URLs (handle relative paths)
    let defaultLogoUrl = '';
    let darkLogoUrl = '';
    
    if (mainLogo) {
        defaultLogoUrl = mainLogo.src;
        console.log('Initial Default Logo URL:', defaultLogoUrl);
        
        // Define the known path segment for logos and the dark logo filename
        const logoPathSegment = '/wp-content/uploads/2024/06/';
        const darkLogoFilename = 'logo-dark-1.png';

        try {
            // Find the base URL part before /wp-content/
            const contentIndex = defaultLogoUrl.indexOf('/wp-content/');
            if (contentIndex > -1) {
                 const baseUrl = defaultLogoUrl.substring(0, contentIndex);
                 console.log('Derived Base URL:', baseUrl);
                 // Construct the absolute URL for the dark logo
                 darkLogoUrl = baseUrl + logoPathSegment + darkLogoFilename;
                 console.log('Constructed Absolute Dark Logo URL:', darkLogoUrl);
            } else {
                 // Fallback if '/wp-content/' isn't found in the default logo URL
                 console.warn('Could not determine absolute path base from default logo URL. Falling back to relative path.');
                 const prefix = (window.location.pathname === '/' || window.location.pathname.endsWith('/index.html') || window.location.pathname.endsWith('/bitrader.thetork.com/')) ? './' : '../';
                 darkLogoUrl = prefix + 'wp-content/uploads/2024/06/' + darkLogoFilename;
                 console.log('Constructed Relative Dark Logo URL (Fallback):', darkLogoUrl);
            }
        } catch (e) {
             console.error('Error constructing absolute dark logo URL:', e);
             // Fallback to simple relative path on error
             const prefix = (window.location.pathname === '/' || window.location.pathname.endsWith('/index.html') || window.location.pathname.endsWith('/bitrader.thetork.com/')) ? './' : '../';
             darkLogoUrl = prefix + 'wp-content/uploads/2024/06/' + darkLogoFilename;
             console.log('Constructed Relative Dark Logo URL (Error Fallback):', darkLogoUrl);
        }
        
        // Final check if darkLogoUrl was set
        if (!darkLogoUrl) {
             console.error('Failed to determine darkLogoUrl. Using default fallback.');
             darkLogoUrl = './wp-content/uploads/2024/06/logo-dark-1.png'; // Default fallback
             console.log('Final Dark Logo URL (Default Fallback):', darkLogoUrl);
        }
    }
    
    // Function to apply theme
    function applyTheme(isDark) {
        console.log('Applying theme. Dark mode:', isDark);
        // Update HTML attribute
        htmlElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
        
        // Update icon
        if (icon) {
            if (isDark) {
                const iconPath = icon.src.split('/').slice(0, -1).join('/') + '/sun.svg';
                icon.src = iconPath;
                toggleVersionButton.style.backgroundColor = 'white';
            } else {
                const iconPath = icon.src.split('/').slice(0, -1).join('/') + '/moon.svg';
                icon.src = iconPath;
                toggleVersionButton.style.backgroundColor = '#00D094';
            }
        }
        
        // Update logo if available
        if (mainLogo && defaultLogoUrl && darkLogoUrl) {
            const newLogoSrc = isDark ? darkLogoUrl : defaultLogoUrl;
            console.log('Setting logo src to:', newLogoSrc);
            mainLogo.src = newLogoSrc;
        }
    }
    
    // Check localStorage for saved preference
    const savedTheme = localStorage.getItem('bitrader-theme-mode');
    const isDarkMode = savedTheme === 'dark';
    
    // Apply saved theme preference
    if (savedTheme) {
        applyTheme(isDarkMode);
    }
    
    // Add click event to toggle button
    if (toggleVersionButton) {
        toggleVersionButton.addEventListener('click', function() {
            // Get current theme
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const isDark = currentTheme !== 'dark';
            
            // Save to localStorage
            localStorage.setItem('bitrader-theme-mode', isDark ? 'dark' : 'light');
            
            // Apply theme
            applyTheme(isDark);
        });
    }
});
