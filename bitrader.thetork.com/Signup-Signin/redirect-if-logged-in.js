// Redirect to dashboard if already logged in

document.addEventListener('DOMContentLoaded', function() {
    // Check if user is already authenticated
    const token = localStorage.getItem('authToken');
    const userData = localStorage.getItem('userData');
    
    if (token && userData) {
        // User is already logged in, redirect to dashboard
        window.location.href = '../../coinex/dashboard/index.php';
    }
}); 