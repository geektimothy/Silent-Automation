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
        const custom = silentData.customAutomations || [];
        const currentUrl = silentData.pageUrl;

        // V1 Compatibility
        rules.forEach(rule => {
            if (rule.page_url === currentUrl) {
                showPopup(rule.message);
            }
        });

        // V2 Custom Automations
        custom.forEach(auto => {
            // Logic for triggering custom automations
            // For MVP, we use local storage to track session state
            if (shouldTrigger(auto)) {
                if (auto.action_type === 'popup') {
                    showPopup(auto.message);
                } else if (auto.action_type === 'whatsapp') {
                    showPopup(auto.message, auto.whatsapp_url);
                }
            }
        });
    }

    function shouldTrigger(auto) {
        const key = 'silent_trigger_' + auto.id;
        if (localStorage.getItem(key)) return false;

        if (auto.condition_type === 'high_intent') {
            const visits = parseInt(localStorage.getItem('silent_visits_' + window.location.pathname) || 0);
            if (visits >= 2) {
                localStorage.setItem(key, '1');
                return true;
            }
        }
        
        if (auto.condition_type === 'cart_abandonment') {
            // Simplified: trigger if they are on a product page but haven't checked out
            if (window.location.pathname.includes('/product/')) {
                return true;
            }
        }

        return false;
    }

    // Track visits in local storage for instant triggers
    const pathKey = 'silent_visits_' + window.location.pathname;
    localStorage.setItem(pathKey, (parseInt(localStorage.getItem(pathKey) || 0) + 1).toString());

    function showPopup(message, actionUrl = null) {
        const popup = document.getElementById('silent-automation-popup');
        if (!popup) return;

        popup.querySelector('.silent-message').innerText = message;
        const actionContainer = popup.querySelector('.silent-action-container');
        
        if (actionUrl) {
            actionContainer.innerHTML = `<a href="${actionUrl}" target="_blank" class="silent-whatsapp-btn">Chat on WhatsApp</a>`;
        } else {
            actionContainer.innerHTML = '';
        }

        popup.style.display = 'flex';

        popup.querySelector('.silent-close').onclick = () => {
            popup.style.display = 'none';
        };
    }
})();
