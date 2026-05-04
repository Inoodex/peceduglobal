export default {
    mounted(el, binding) {
        // Ensure element has relative positioning to contain the ripple
        const style = window.getComputedStyle(el);
        if (style.position === 'static') {
            el.style.position = 'relative';
        }
        el.style.overflow = 'hidden';

        el.addEventListener('mousedown', function (e) {
            // Remove existing ripple
            const existingRipple = el.querySelector('.custom-ripple');
            if (existingRipple) {
                existingRipple.remove();
            }

            const ripple = document.createElement('span');
            ripple.classList.add('custom-ripple');
            
            const rect = el.getBoundingClientRect();
            // Calculate the radius to reach the furthest corner
            const dx = Math.max(e.clientX - rect.left, rect.right - e.clientX);
            const dy = Math.max(e.clientY - rect.top, rect.bottom - e.clientY);
            const radius = Math.sqrt(dx * dx + dy * dy);
            const diameter = radius * 2;
            
            ripple.style.width = ripple.style.height = `${diameter}px`;
            ripple.style.left = `${e.clientX - rect.left - radius}px`;
            ripple.style.top = `${e.clientY - rect.top - radius}px`;
            
            // Allow passing a custom color
            if (binding.value) {
                ripple.style.backgroundColor = binding.value;
            }

            el.appendChild(ripple);

            // Clean up the DOM after animation completes
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    }
}
