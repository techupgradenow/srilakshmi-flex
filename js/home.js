// Banner Slider
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.slider-btn.prev');
    const nextBtn = document.querySelector('.slider-btn.next');
    let currentSlide = 0;
    let autoSlideInterval;

    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));

        if (index >= slides.length) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = slides.length - 1;
        } else {
            currentSlide = index;
        }

        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function prevSlide() {
        showSlide(currentSlide - 1);
    }

    function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, 5000);
    }

    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }

    function resetAutoSlide() {
        stopAutoSlide();
        startAutoSlide();
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            prevSlide();
            resetAutoSlide();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            nextSlide();
            resetAutoSlide();
        });
    }

    dots.forEach(function(dot, index) {
        dot.addEventListener('click', function() {
            showSlide(index);
            resetAutoSlide();
        });
    });

    const bannerSlider = document.querySelector('.banner-slider');
    if (bannerSlider) {
        bannerSlider.addEventListener('mouseenter', stopAutoSlide);
        bannerSlider.addEventListener('mouseleave', startAutoSlide);
    }

    startAutoSlide();
});
