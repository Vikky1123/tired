/* Custom JS - Added by copy_missing_assets.php */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Custom JS loaded successfully');
    
    // Basic functionality for the theme mode toggle
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