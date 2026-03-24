// Banner Slider
function initSlider() {
    var slides = document.querySelectorAll('.slide');
    var dots = document.querySelectorAll('.dot');
    var prevBtn = document.querySelector('.slider-btn.prev');
    var nextBtn = document.querySelector('.slider-btn.next');
    var bannerSlider = document.querySelector('.banner-slider');
    var currentSlide = 0;
    var autoSlideInterval;

    if (!slides.length) return;

    function showSlide(index) {
        slides.forEach(function(slide) { slide.classList.remove('active'); });
        dots.forEach(function(dot) { dot.classList.remove('active'); });

        if (index >= slides.length) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = slides.length - 1;
        } else {
            currentSlide = index;
        }

        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    }

    function nextSlide() { showSlide(currentSlide + 1); }
    function prevSlide() { showSlide(currentSlide - 1); }

    function startAutoSlide() {
        clearInterval(autoSlideInterval);
        autoSlideInterval = setInterval(nextSlide, 5000);
    }

    function stopAutoSlide() { clearInterval(autoSlideInterval); }
    function resetAutoSlide() { stopAutoSlide(); startAutoSlide(); }

    if (prevBtn) {
        prevBtn.addEventListener('click', function() { prevSlide(); resetAutoSlide(); });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function() { nextSlide(); resetAutoSlide(); });
    }

    dots.forEach(function(dot, index) {
        dot.addEventListener('click', function() { showSlide(index); resetAutoSlide(); });
    });

    if (bannerSlider) {
        bannerSlider.addEventListener('mouseenter', stopAutoSlide);
        bannerSlider.addEventListener('mouseleave', startAutoSlide);
    }

    startAutoSlide();
}

document.addEventListener('DOMContentLoaded', initSlider);
