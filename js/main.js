// Main JavaScript - Shared functionality across all pages
$(document).ready(function() {
    // Mobile navigation toggle
    $('#mobileToggle').click(function() {
        $('#navMenu').toggleClass('active');
        $(this).toggleClass('active');
    });
    
    // Close mobile menu when clicking on a link
    $('.nav-link').click(function() {
        $('#navMenu').removeClass('active');
        $('#mobileToggle').removeClass('active');
        
        // Update active nav link
        $('.nav-link').removeClass('active');
        $(this).addClass('active');
    });
    
    // Scroll progress bar
    $(window).scroll(function() {
        var scrollTop = $(this).scrollTop();
        var docHeight = $(document).height();
        var winHeight = $(this).height();
        var scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;
        
        $('#scrollProgress').css('width', scrollPercent + '%');
        
        // Header background on scroll
        if (scrollTop > 100) {
            $('#mainHeader').css({
                'background-color': 'rgba(255, 255, 255, 0.98)',
                'box-shadow': '0 5px 20px rgba(0, 0, 0, 0.1)'
            });
        } else {
            $('#mainHeader').css({
                'background-color': 'rgba(255, 255, 255, 0.95)',
                'box-shadow': '0 5px 20px rgba(0, 0, 0, 0.08)'
            });
        }
        
        // Trigger animations on scroll
        $('.fade-in, .zoom-in').each(function() {
            var elementTop = $(this).offset().top;
            var elementVisible = 150;
            
            if (scrollTop + winHeight > elementTop + elementVisible) {
                $(this).addClass('visible');
            }
        });
    });
    
    // Trigger initial animation check
    $(window).trigger('scroll');
    
    // Counter animation for trust indicators
    $('.counter').each(function() {
        $(this).prop('Counter', 0).animate({
            Counter: $(this).data('count')
        }, {
            duration: 2000,
            easing: 'swing',
            step: function(now) {
                if ($(this).data('count') > 100) {
                    $(this).text(Math.ceil(now));
                } else {
                    $(this).text(Math.ceil(now) + '+');
                }
            }
        });
    });
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        
        let target = this.hash;
        let $target = $(target);
        
        if ($target.length) {
            $('html, body').stop().animate({
                'scrollTop': $target.offset().top - 80
            }, 800, 'swing');
        }
    });
    
    // Add hover effect to service cards
    $('.service-card').hover(
        function() {
            $(this).find('.service-icon').css('transform', 'scale(1.1)');
        },
        function() {
            $(this).find('.service-icon').css('transform', 'scale(1)');
        }
    );
    
    // Set active nav link based on current page
    var currentPage = window.location.pathname.split('/').pop() || 'index.html';
    $('.nav-link').each(function() {
        var linkHref = $(this).attr('href');
        if (linkHref === currentPage || (currentPage === '' && linkHref === 'index.html')) {
            $(this).addClass('active');
        }
    });
});

