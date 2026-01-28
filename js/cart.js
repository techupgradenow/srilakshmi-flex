/**
 * ProPrint Solutions - Cart Functionality
 * Handles add to cart, cart management, and localStorage persistence
 */

(function() {
    'use strict';

    // Cart Module
    const Cart = {
        storageKey: 'proprintCart',

        // Get cart from localStorage
        getCart: function() {
            const cart = localStorage.getItem(this.storageKey);
            return cart ? JSON.parse(cart) : [];
        },

        // Save cart to localStorage
        saveCart: function(cart) {
            localStorage.setItem(this.storageKey, JSON.stringify(cart));
            this.updateCartCount();
        },

        // Add item to cart
        addItem: function(product) {
            const cart = this.getCart();
            const existingIndex = cart.findIndex(item => item.id === product.id);

            if (existingIndex > -1) {
                // Item exists, increase quantity
                cart[existingIndex].quantity += 1;
            } else {
                // New item, add to cart
                cart.push({
                    id: product.id,
                    name: product.name,
                    image: product.image,
                    category: product.category,
                    quantity: 1,
                    addedAt: new Date().toISOString()
                });
            }

            this.saveCart(cart);
            return cart;
        },

        // Remove item from cart
        removeItem: function(productId) {
            let cart = this.getCart();
            cart = cart.filter(item => item.id !== productId);
            this.saveCart(cart);
            return cart;
        },

        // Update item quantity
        updateQuantity: function(productId, quantity) {
            console.log('updateQuantity called:', productId, quantity);
            const cart = this.getCart();
            const itemIndex = cart.findIndex(item => item.id === productId);
            console.log('Item found at index:', itemIndex);

            if (itemIndex > -1) {
                if (quantity <= 0) {
                    cart.splice(itemIndex, 1);
                } else {
                    cart[itemIndex].quantity = quantity;
                    console.log('Updated quantity to:', quantity);
                }
                this.saveCart(cart);
                console.log('Cart saved');
            }
            return cart;
        },

        // Get total items count
        getTotalItems: function() {
            const cart = this.getCart();
            return cart.reduce((total, item) => total + item.quantity, 0);
        },

        // Clear entire cart
        clearCart: function() {
            localStorage.removeItem(this.storageKey);
            this.updateCartCount();
        },

        // Update cart count in header
        updateCartCount: function() {
            const count = this.getTotalItems();
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(el => {
                el.textContent = count;
                // Add animation class
                el.classList.add('cart-count-updated');
                setTimeout(() => {
                    el.classList.remove('cart-count-updated');
                }, 300);
            });
        }
    };

    // Initialize cart count on page load
    document.addEventListener('DOMContentLoaded', function() {
        Cart.updateCartCount();
        initAddToCartButtons();
        initCartPage();
    });

    // Initialize Add to Cart buttons with event delegation
    function initAddToCartButtons() {
        // Use event delegation to catch all add-to-cart button clicks
        // This works for buttons that exist now AND buttons added later (like modal buttons)
        document.body.addEventListener('click', function(e) {
            // Check if clicked element or its parent is an add-to-cart button
            const button = e.target.closest('.add-to-cart-btn');

            if (button) {
                e.preventDefault();
                e.stopPropagation();

                console.log('Add to Cart clicked!', button.dataset);

                // Validate we have the required data
                if (!button.dataset.id || !button.dataset.name) {
                    console.error('Missing product data on button:', button);
                    return;
                }

                const product = {
                    id: button.dataset.id,
                    name: button.dataset.name,
                    image: button.dataset.image || '',
                    category: button.dataset.category || 'General'
                };

                console.log('Adding product to cart:', product);

                // Add to cart
                Cart.addItem(product);

                // Show feedback
                showAddedFeedback(button);

                // Show toast notification
                showToastNotification(product.name);

                console.log('Cart updated! Total items:', Cart.getTotalItems());
            }
        });

        console.log('Cart event listener initialized');
    }

    // Show visual feedback when item is added
    function showAddedFeedback(button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Added!';
        button.classList.add('added-to-cart');
        button.disabled = true;

        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('added-to-cart');
            button.disabled = false;
        }, 1500);
    }

    // Show toast notification
    function showToastNotification(productName) {
        // Remove existing toast if any
        const existingToast = document.querySelector('.cart-toast');
        if (existingToast) {
            existingToast.remove();
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'cart-toast';
        toast.innerHTML = `
            <div class="cart-toast-content">
                <i class="fas fa-check-circle"></i>
                <div class="cart-toast-text">
                    <strong>Added to Cart!</strong>
                    <p>${productName}</p>
                </div>
            </div>
            <a href="cart.html" class="cart-toast-link">View Cart</a>
        `;

        document.body.appendChild(toast);

        // Show toast with animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        // Auto hide after 4 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 4000);
    }

    // Initialize Cart Page functionality
    function initCartPage() {
        const cartContainer = document.getElementById('cartItems');
        if (!cartContainer) return; // Not on cart page

        renderCartItems();
        initCartActions();
    }

    // Render cart items on cart page
    function renderCartItems() {
        console.log('renderCartItems called');
        const cartContainer = document.getElementById('cartItems');
        const cartSummary = document.getElementById('cartSummary');
        const emptyCart = document.getElementById('emptyCart');
        const cartContent = document.querySelector('.cart-content');
        const cartItemsWrapper = document.querySelector('.cart-items-wrapper');

        if (!cartContainer) return;

        const cart = Cart.getCart();
        console.log('Current cart:', cart);

        if (cart.length === 0) {
            // Hide entire cart content section using class + inline style for maximum reliability
            if (cartContent) {
                cartContent.style.display = 'none';
                cartContent.classList.add('cart-hidden');
            }
            if (emptyCart) {
                emptyCart.style.display = 'block';
                emptyCart.classList.add('cart-visible');
            }
            console.log('Cart is empty - hiding cart content');
            return;
        }

        // Show cart content, hide empty message
        if (cartContent) {
            cartContent.style.display = '';
            cartContent.classList.remove('cart-hidden');
        }
        if (cartItemsWrapper) cartItemsWrapper.style.display = '';
        if (cartContainer) cartContainer.style.display = '';
        if (cartSummary) cartSummary.style.display = '';
        if (emptyCart) {
            emptyCart.style.display = 'none';
            emptyCart.classList.remove('cart-visible');
        }
        console.log('Cart has items - showing cart content');

        let html = '';
        cart.forEach(item => {
            html += `
                <div class="cart-item" data-id="${item.id}">
                    <div class="cart-item-image">
                        <img src="${item.image}" alt="${item.name}" onerror="this.src='images/placeholder.png'">
                    </div>
                    <div class="cart-item-details">
                        <h3 class="cart-item-name">${item.name}</h3>
                        <p class="cart-item-category">${item.category}</p>
                    </div>
                    <div class="cart-item-quantity">
                        <button type="button" class="qty-btn qty-decrease" data-id="${item.id}">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="qty-value">${item.quantity}</span>
                        <button type="button" class="qty-btn qty-increase" data-id="${item.id}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button type="button" class="cart-item-remove" data-id="${item.id}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            `;
        });

        cartContainer.innerHTML = html;

        // Attach direct click handlers to buttons for better reliability
        attachCartButtonHandlers();

        // Update summary
        updateCartSummary();
    }

    // Attach direct event handlers to cart buttons
    function attachCartButtonHandlers() {
        // Increase buttons
        document.querySelectorAll('.qty-increase').forEach(btn => {
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                const productId = this.dataset.id;
                console.log('Direct handler: Increasing quantity for:', productId);
                const cart = Cart.getCart();
                const item = cart.find(i => i.id === productId);
                if (item) {
                    Cart.updateQuantity(productId, item.quantity + 1);
                    renderCartItems();
                }
            };
        });

        // Decrease buttons
        document.querySelectorAll('.qty-decrease').forEach(btn => {
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                const productId = this.dataset.id;
                console.log('Direct handler: Decreasing quantity for:', productId);
                const cart = Cart.getCart();
                const item = cart.find(i => i.id === productId);
                if (item && item.quantity > 1) {
                    Cart.updateQuantity(productId, item.quantity - 1);
                    renderCartItems();
                } else if (item && item.quantity === 1) {
                    if (confirm('Remove this item from cart?')) {
                        Cart.removeItem(productId);
                        renderCartItems();
                    }
                }
            };
        });

        // Remove buttons
        document.querySelectorAll('.cart-item-remove').forEach(btn => {
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                const productId = this.dataset.id;
                console.log('Direct handler: Removing item:', productId);
                if (confirm('Remove this item from cart?')) {
                    Cart.removeItem(productId);
                    renderCartItems();
                }
            };
        });

        // Clear cart button
        const clearBtn = document.getElementById('clearCart');
        if (clearBtn) {
            clearBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Direct handler: Clear cart clicked');
                if (confirm('Are you sure you want to clear your cart?')) {
                    Cart.clearCart();
                    renderCartItems();
                }
            };
        }

        // Request quote button
        const quoteBtn = document.getElementById('proceedToQuote');
        if (quoteBtn) {
            quoteBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                const cart = Cart.getCart();
                if (cart.length === 0) {
                    alert('Your cart is empty!');
                    return;
                }
                let message = 'I would like to request a quote for the following items:\n\n';
                cart.forEach((item, index) => {
                    message += `${index + 1}. ${item.name} (Qty: ${item.quantity})\n`;
                });
                message += '\nPlease provide pricing and availability.';
                localStorage.setItem('quoteMessage', message);
                window.location.href = 'contact.html';
            };
        }

        console.log('Direct button handlers attached');
    }

    // Update cart summary
    function updateCartSummary() {
        const totalItemsEl = document.getElementById('totalItems');
        const cart = Cart.getCart();
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0);

        if (totalItemsEl) {
            totalItemsEl.textContent = totalItems + (totalItems === 1 ? ' item' : ' items');
        }
    }

    // Initialize cart page actions with document-level event delegation
    // This ensures buttons work on both mobile and desktop consistently
    function initCartActions() {
        const cartSection = document.querySelector('.cart-section');
        if (!cartSection) {
            console.log('Cart section not found');
            return;
        }

        console.log('Cart actions initialized with document-level delegation');

        // Use document-level event delegation for all cart buttons
        // This works reliably on both mobile and desktop
        document.addEventListener('click', function(e) {
            // Handle quantity increase
            const increaseBtn = e.target.closest('.qty-increase');
            if (increaseBtn) {
                e.preventDefault();
                e.stopPropagation();
                const productId = increaseBtn.dataset.id;
                console.log('Increasing quantity for:', productId);

                const cart = Cart.getCart();
                const item = cart.find(i => i.id === productId);
                if (item) {
                    Cart.updateQuantity(productId, item.quantity + 1);
                    renderCartItems();
                }
                return;
            }

            // Handle quantity decrease
            const decreaseBtn = e.target.closest('.qty-decrease');
            if (decreaseBtn) {
                e.preventDefault();
                e.stopPropagation();
                const productId = decreaseBtn.dataset.id;
                console.log('Decreasing quantity for:', productId);

                const cart = Cart.getCart();
                const item = cart.find(i => i.id === productId);
                if (item && item.quantity > 1) {
                    Cart.updateQuantity(productId, item.quantity - 1);
                    renderCartItems();
                } else if (item && item.quantity === 1) {
                    if (confirm('Remove this item from cart?')) {
                        Cart.removeItem(productId);
                        renderCartItems();
                    }
                }
                return;
            }

            // Handle remove item
            const removeBtn = e.target.closest('.cart-item-remove');
            if (removeBtn) {
                e.preventDefault();
                e.stopPropagation();
                const productId = removeBtn.dataset.id;
                console.log('Removing item:', productId);

                if (confirm('Remove this item from cart?')) {
                    Cart.removeItem(productId);
                    renderCartItems();
                }
                return;
            }

            // Handle clear cart
            const clearBtn = e.target.closest('#clearCart');
            if (clearBtn) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Clear cart clicked');

                if (confirm('Are you sure you want to clear your cart?')) {
                    Cart.clearCart();
                    renderCartItems();
                }
                return;
            }

            // Handle proceed to quote
            const quoteBtn = e.target.closest('#proceedToQuote');
            if (quoteBtn) {
                e.preventDefault();
                e.stopPropagation();

                const cart = Cart.getCart();
                if (cart.length === 0) {
                    alert('Your cart is empty!');
                    return;
                }

                // Build message for contact form
                let message = 'I would like to request a quote for the following items:\n\n';
                cart.forEach((item, index) => {
                    message += `${index + 1}. ${item.name} (Qty: ${item.quantity})\n`;
                });
                message += '\nPlease provide pricing and availability.';

                // Store message for contact page
                localStorage.setItem('quoteMessage', message);

                // Redirect to contact page
                window.location.href = 'contact.html';
                return;
            }
        });
    }

    // Expose Cart to global scope for external use
    window.ProPrintCart = Cart;

})();
