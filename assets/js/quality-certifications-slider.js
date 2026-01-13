// Quality Certifications Logo Slider
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.quality-certifications-slider-track');
    if (!slider) return;

    // Clone items for seamless infinite scroll
    const items = slider.querySelectorAll('.quality-certifications-slider-item');
    if (items.length === 0) return;

    // Duplicate items to create seamless loop
    items.forEach(item => {
        const clone = item.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        slider.appendChild(clone);
    });

    // Pause animation on hover
    const wrapper = document.querySelector('.quality-certifications-slider-wrapper');
    if (wrapper) {
        wrapper.addEventListener('mouseenter', function() {
            slider.style.animationPlayState = 'paused';
        });

        wrapper.addEventListener('mouseleave', function() {
            slider.style.animationPlayState = 'running';
        });
    }
});
