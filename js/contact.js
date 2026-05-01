// Contact page specific JavaScript
$(document).ready(function() {
    // Auto-fill subject from URL ?service= parameter
    var urlParams = new URLSearchParams(window.location.search);
    var serviceParam = urlParams.get('service');
    if (serviceParam) {
        $('#subject').val(serviceParam + ' Quote');
        $('#service-hidden').val(serviceParam);
        $('#subject').parent().addClass('focused');
    }

    // Form submission - works with both old and new form classes
    $('.contact-form, .modern-contact-form').submit(function(e) {
        e.preventDefault();

        var $form = $(this);

        // Get form data
        var formData = {
            name: $('#name').val(),
            email: $('#email').val(),
            phone: $('#phone').val(),
            subject: $('#subject').val(),
            message: $('#message').val(),
            service: $('#service-hidden').val() || ''
        };

        // Basic validation
        if (!formData.name || !formData.email || !formData.message) {
            showNotification('Please fill in all required fields.', 'error');
            return;
        }

        // Email validation
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(formData.email)) {
            showNotification('Please enter a valid email address.', 'error');
            return;
        }

        // Disable submit button
        var $submitBtn = $form.find('button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

        $.ajax({
            url: 'api/enquiry.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('Thank you for your enquiry! We will get back to you within 24 hours.', 'success');
                    $form[0].reset();
                    $form.find('.modern-form-group').removeClass('focused');
                } else {
                    showNotification(response.error || 'Something went wrong. Please try again.', 'error');
                }
                $submitBtn.prop('disabled', false).html(originalText);
            },
            error: function() {
                showNotification('Network error. Please try again later.', 'error');
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Modern form field animations
    $('.modern-form-group input, .modern-form-group textarea').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        if ($(this).val() === '') {
            $(this).parent().removeClass('focused');
        }
    });
    
    // Legacy form field animations (for compatibility)
    $('.form-group input, .form-group textarea').not('.modern-form-group input, .modern-form-group textarea').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        if ($(this).val() === '') {
            $(this).parent().removeClass('focused');
        }
    });
    
    // Notification system
    function showNotification(message, type) {
        // Remove existing notifications
        $('.form-notification').remove();
        
        var bgColor = type === 'success' ? '#4CAF50' : '#f44336';
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        var notification = $('<div class="form-notification" style="position: fixed; top: 100px; right: 20px; background: ' + bgColor + '; color: white; padding: 1rem 1.5rem; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); z-index: 10000; display: flex; align-items: center; gap: 1rem; max-width: 400px; animation: slideInRight 0.3s ease;"><i class="fas ' + icon + '"></i><span>' + message + '</span></div>');
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Add animation keyframes if not exists
    if ($('#notification-styles').length === 0) {
        $('<style id="notification-styles">@keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }</style>').appendTo('head');
    }
    
    // Animate contact cards on scroll
    $('.modern-card').each(function() {
        var $card = $(this);
        $(window).on('scroll', function() {
            var cardTop = $card.offset().top;
            var windowBottom = $(window).scrollTop() + $(window).height();
            
            if (cardTop < windowBottom - 100) {
                $card.addClass('animate-in');
            }
        });
    });
});

