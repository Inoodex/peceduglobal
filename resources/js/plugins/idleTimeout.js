import Cookies from 'js-cookie';

export const setupIdleTimeout = (timeoutMinutes = 30) => {
    let idleTimer;
    const timeoutMs = timeoutMinutes * 60 * 1000;

    const logout = () => {
        const token = Cookies.get('auth_token') || Cookies.get('token') || localStorage.getItem('token');
        if (token) {
            localStorage.removeItem('token');
            Cookies.remove('auth_token');
            Cookies.remove('token');
            window.location.href = '/login';
        }
    };

    const resetTimer = () => {
        clearTimeout(idleTimer);
        const token = Cookies.get('auth_token') || Cookies.get('token') || localStorage.getItem('token');
        if (token) {
            idleTimer = setTimeout(logout, timeoutMs);
        }
    };

    // Events that signify user activity
    const events = [
        'mousemove', 'keydown', 'wheel', 'DOMMouseScroll', 
        'mouseWheel', 'mousedown', 'touchstart', 'touchmove', 
        'MSPointerDown', 'MSPointerMove'
    ];
    
    // Use a throttled version of resetTimer to improve performance
    let isThrottled = false;
    const throttledResetTimer = () => {
        if (!isThrottled) {
            resetTimer();
            isThrottled = true;
            setTimeout(() => { isThrottled = false; }, 1000); // Only reset timer max once per second
        }
    };

    events.forEach(event => {
        document.addEventListener(event, throttledResetTimer, { passive: true });
    });

    // Initialize the first timer
    resetTimer();
};
