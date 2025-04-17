/**
 * Fix MIME Type Issues Script
 * This script resolves issues with strict MIME type checking in the browser
 */
console.log('fix-mime-issues.js loaded');

// Override the browser's MIME type validation for scripts
document.addEventListener('DOMContentLoaded', function() {
    // Find all script tags that might be affected
    const scripts = document.getElementsByTagName('script');
    
    // Log all script sources for debugging
    console.log('Script tags on page:', scripts.length);
    
    for (let i = 0; i < scripts.length; i++) {
        if (scripts[i].src && !scripts[i].hasAttribute('type')) {
            console.log('Adding type to script:', scripts[i].src);
            scripts[i].setAttribute('type', 'application/javascript');
        }
    }
    
    // Force loading of commonly missing scripts
    const commonScripts = [
        '/wp-content/themes/bitrader/assets/js/swiper-bundle.min.js',
        '/wp-content/themes/bitrader/assets/js/bootstrap.min.js',
        '/wp-content/themes/bitrader/assets/js/aos.js',
        '/wp-content/themes/bitrader/assets/js/purecounter.js',
        '/wp-content/themes/bitrader/assets/js/custom.js'
    ];
    
    commonScripts.forEach(function(scriptPath) {
        // Create a new script element
        const script = document.createElement('script');
        script.src = scriptPath;
        script.type = 'application/javascript';
        script.async = true;
        
        // Add error handling
        script.onerror = function() {
            console.warn('Failed to load script:', scriptPath);
        };
        
        script.onload = function() {
            console.log('Successfully loaded script:', scriptPath);
        };
        
        // Append to the document body
        document.body.appendChild(script);
    });
});