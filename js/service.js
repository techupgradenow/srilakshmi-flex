// Service page specific JavaScript
$(document).ready(function() {
    // Service card interactions
    $('.service-card').each(function() {
        $(this).on('mouseenter', function() {
            $(this).find('.service-icon').addClass('animated');
        }).on('mouseleave', function() {
            $(this).find('.service-icon').removeClass('animated');
        });
    });
    
    // Filter services if needed (for future enhancement)
    $('.service-filter').on('click', function() {
        var filter = $(this).data('filter');
        $('.service-card').hide();
        $('.service-card[data-category="' + filter + '"]').fadeIn();
    });
});

