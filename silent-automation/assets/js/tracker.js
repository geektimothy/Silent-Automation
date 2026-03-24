(function() {
    const sessionId = getSessionId();
    const startTime = Date.now();
    let timeSpentSent = false;

    // Track Visit
    trackEvent('visit', 1);

    // Track Time Spent on page exit or visibility change
    window.addEventListener('beforeunload', () => {
        sendTimeSpent();
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            sendTimeSpent();
        }
    });

    function sendTimeSpent() {
        if (timeSpentSent) return;
        const seconds = Math.floor((Date.now() - startTime) / 1000);
        if (seconds > 5) { // Only track if more than 5 seconds
            trackEvent('time_spent', seconds);
            timeSpentSent = true;
        }
    }

    function trackEvent(type, value) {
        fetch(silentData.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': silentData.nonce
            },
            body: JSON.stringify({
                session_id: sessionId,
                page_url: silentData.pageUrl,
                event_type: type,
                value: value
            })
        }).catch(err => console.error('Tracking failed', err));
    }

    function getSessionId() {
        let id = getCookie('silent_session_id');
        if (!id) {
            id = 'sess_' + Math.random().toString(36).substr(2, 9);
            setCookie('silent_session_id', id, 1);
        }
        return id;
    }

    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + "=" + value + ";expires=" + d.toUTCString() + ";path=/";
    }

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    // Handle Popup Logic
    checkAutomations();

    function checkAutomations() {
        const rules = silentData.activeRules || [];
        const currentUrl = silentData.pageUrl;

        rules.forEach(rule => {
            if (rule.page_url === currentUrl) {
                // In a real scenario, we might check local storage for visit count
                // For MVP, we show it if the rule is active for this page
                showPopup(rule.message);
            }
        });
    }

    function showPopup(message) {
        const popup = document.getElementById('silent-automation-popup');
        if (!popup) return;

        popup.querySelector('.silent-message').innerText = message;
        popup.style.display = 'flex';

        popup.querySelector('.silent-close').onclick = () => {
            popup.style.display = 'none';
        };
    }
})();
