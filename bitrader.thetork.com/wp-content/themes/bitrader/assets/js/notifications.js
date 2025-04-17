// Notification System
(function($) {
    'use strict';

    // Notification messages array
    const notifications = [
        "James from New York just invested $500",
        "Sophia just withdrew $1,200",
        "Michael from Argentina deposited $250",
        "Emily just earned 35% profit from her investment",
        "Liam joined the platform 3 minutes ago",
        "Sarah from London invested $2,500",
        "David just earned 42% on his Platinum plan",
        "Emma withdrew $3,800 from her account",
        "Robert from Canada joined 2 minutes ago",
        "Lisa earned 38% profit from Gold plan",
        "John from Australia invested $1,500",
        "Maria just joined from Spain",
        "William earned 40% on Diamond plan",
        "Anna withdrew $5,000 successfully",
        "Thomas from Germany invested $3,200"
    ];

    // Create notification container if it doesn't exist
    if (!$('#notification-container').length) {
        $('body').append('<div id="notification-container"></div>');
    }

    // Add styles
    const styles = `
        #notification-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
        }
        .notification {
            background: rgba(255, 255, 255, 0.95);
            border-left: 4px solid #2b4eff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 4px;
            padding: 12px 20px;
            margin-top: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: #1a1f36;
            transform: translateX(120%);
            transition: transform 0.3s ease-in-out;
            backdrop-filter: blur(5px);
        }
        .notification.show {
            transform: translateX(0);
        }
        .notification i {
            color: #2b4eff;
            margin-right: 8px;
        }
        @media (max-width: 768px) {
            #notification-container {
                bottom: 10px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }
    `;

    // Add styles to head
    const styleSheet = document.createElement("style");
    styleSheet.textContent = styles;
    document.head.appendChild(styleSheet);

    // Function to create and show notification
    function showNotification() {
        const message = notifications[Math.floor(Math.random() * notifications.length)];
        const notification = $(`
            <div class="notification">
                <i class="fa-solid fa-circle-check"></i>
                ${message}
            </div>
        `);

        $('#notification-container').append(notification);

        // Trigger reflow
        notification[0].offsetHeight;

        // Show notification
        setTimeout(() => {
            notification.addClass('show');
        }, 100);

        // Hide and remove notification
        setTimeout(() => {
            notification.removeClass('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000 + Math.random() * 2000);
    }

    // Function to get random interval between 10-30 seconds
    function getRandomInterval() {
        return (10000 + Math.random() * 20000);
    }

    // Start showing notifications
    function startNotifications() {
        showNotification();
        setTimeout(startNotifications, getRandomInterval());
    }

    // Start the notification system when document is ready
    $(document).ready(function() {
        startNotifications();
    });

})(jQuery); 