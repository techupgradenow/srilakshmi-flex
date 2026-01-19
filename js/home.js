// Home page specific JavaScript
$(document).ready(function() {
    // Initialize Particles.js
    if (typeof particlesJS !== 'undefined' && $('#particles-js').length) {
        particlesJS('particles-js', {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: "#ffc107" },
                shape: { type: "circle" },
                opacity: { value: 0.5, random: true },
                size: { value: 3, random: true },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: "#ffffff",
                    opacity: 0.2,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 2,
                    direction: "none",
                    random: true,
                    straight: false,
                    out_mode: "out",
                    bounce: false
                }
            },
            interactivity: {
                detect_on: "canvas",
                events: {
                    onhover: { enable: true, mode: "repulse" },
                    onclick: { enable: true, mode: "push" }
                }
            },
            retina_detect: true
        });
    }
    
    // Typing effect simulation
    let typingText = $('.typing-text');
    if (typingText.length) {
        let texts = [
            "Flex Banner & Printing Solutions",
            "UV Printing Services",
            "Custom Branding Solutions",
            "Poster & Banner Printing"
        ];
        let textIndex = 0;
        let charIndex = 0;
        let isDeleting = false;
        let typingSpeed = 100;
        
        function typeEffect() {
            let currentText = texts[textIndex];
            
            if (isDeleting) {
                typingText.text(currentText.substring(0, charIndex - 1));
                charIndex--;
                typingSpeed = 50;
            } else {
                typingText.text(currentText.substring(0, charIndex + 1));
                charIndex++;
                typingSpeed = 100;
            }
            
            if (!isDeleting && charIndex === currentText.length) {
                isDeleting = true;
                typingSpeed = 1000; // Pause at end
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                textIndex = (textIndex + 1) % texts.length;
                typingSpeed = 500; // Pause before next word
            }
            
            setTimeout(typeEffect, typingSpeed);
        }
        
        // Start typing effect after page load
        setTimeout(typeEffect, 1000);
    }
    
    // Toggle particles
    $('#toggleParticles').click(function() {
        let particles = $('#particles-js');
        if (particles.css('display') === 'none') {
            particles.show();
            if (typeof particlesJS !== 'undefined') {
                particlesJS.resume();
            }
        } else {
            particles.hide();
            if (typeof particlesJS !== 'undefined') {
                particlesJS.pause();
            }
        }
    });
    
    // Change particles style
    $('#changeParticles').click(function() {
        if (typeof particlesJS !== 'undefined') {
            particlesJS('particles-js', {
                particles: {
                    number: { value: 60, density: { enable: true, value_area: 800 } },
                    color: { value: ["#ffc107", "#ff6b35", "#0a2c5a"] },
                    shape: { type: ["circle", "triangle", "edge"] },
                    opacity: { value: 0.6, random: true },
                    size: { value: 4, random: true },
                    line_linked: {
                        enable: true,
                        distance: 120,
                        color: "#ffc107",
                        opacity: 0.4,
                        width: 1
                    },
                    move: {
                        enable: true,
                        speed: 3,
                        direction: "none",
                        random: true,
                        straight: false,
                        out_mode: "out",
                        bounce: false
                    }
                },
                interactivity: {
                    detect_on: "canvas",
                    events: {
                        onhover: { enable: true, mode: "bubble" },
                        onclick: { enable: true, mode: "push" }
                    }
                },
                retina_detect: true
            });
        }
    });
});

