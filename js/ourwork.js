// Our Work page specific JavaScript
$(document).ready(function() {
    // Project item interactions
    $('.project-item').each(function() {
        $(this).on('mouseenter', function() {
            $(this).find('.project-overlay').addClass('active');
        }).on('mouseleave', function() {
            $(this).find('.project-overlay').removeClass('active');
        });
    });
    
    // Lightbox functionality for project images (if needed)
    $('.project-item img').on('click', function() {
        var imgSrc = $(this).attr('src');
        // You can implement a lightbox here
        // For now, just log the image source
        console.log('Clicked image:', imgSrc);
    });
    
    // Filter projects if needed (for future enhancement)
    $('.project-filter').on('click', function() {
        var filter = $(this).data('filter');
        $('.project-item').hide();
        $('.project-item[data-category="' + filter + '"]').fadeIn();
    });
});

