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
            const cart = this.getCart();
            const itemIndex = cart.findIndex(item => item.id === productId);

            if (itemIndex > -1) {
                if (quantity <= 0) {
                    cart.splice(itemIndex, 1);
                } else {
                    cart[itemIndex].quantity = quantity;
                }
                this.saveCart(cart);
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

    // Initialize Add to Cart buttons
    function initAddToCartButtons() {
        const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

        addToCartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const product = {
                    id: this.dataset.id,
                    name: this.dataset.name,
                    image: this.dataset.image,
                    category: this.dataset.category || 'General'
                };

                // Add to cart
                Cart.addItem(product);

                // Show feedback
                showAddedFeedback(this);

                // Redirect to cart page after brief delay
                setTimeout(() => {
                    window.location.href = 'cart.html';
                }, 500);
            });
        });
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

    // Initialize Cart Page functionality
    function initCartPage() {
        const cartContainer = document.getElementById('cartItems');
        if (!cartContainer) return; // Not on cart page

        renderCartItems();
        initCartActions();
    }

    // Render cart items on cart page
    function renderCartItems() {
        const cartContainer = document.getElementById('cartItems');
        const cartSummary = document.getElementById('cartSummary');
        const emptyCart = document.getElementById('emptyCart');

        if (!cartContainer) return;

        const cart = Cart.getCart();

        if (cart.length === 0) {
            if (cartContainer) cartContainer.style.display = 'none';
            if (cartSummary) cartSummary.style.display = 'none';
            if (emptyCart) emptyCart.style.display = 'block';
            return;
        }

        if (cartContainer) cartContainer.style.display = 'block';
        if (cartSummary) cartSummary.style.display = 'block';
        if (emptyCart) emptyCart.style.display = 'none';

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
                        <button class="qty-btn qty-decrease" data-id="${item.id}">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="qty-value">${item.quantity}</span>
                        <button class="qty-btn qty-increase" data-id="${item.id}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button class="cart-item-remove" data-id="${item.id}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            `;
        });

        cartContainer.innerHTML = html;

        // Update summary
        updateCartSummary();
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

    // Initialize cart page actions
    function initCartActions() {
        const cartContainer = document.getElementById('cartItems');
        if (!cartContainer) return;

        // Event delegation for cart actions
        cartContainer.addEventListener('click', function(e) {
            const target = e.target.closest('button');
            if (!target) return;

            const productId = target.dataset.id;

            if (target.classList.contains('qty-increase')) {
                const cart = Cart.getCart();
                const item = cart.find(i => i.id === productId);
                if (item) {
                    Cart.updateQuantity(productId, item.quantity + 1);
                    renderCartItems();
                }
            } else if (target.classList.contains('qty-decrease')) {
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
            } else if (target.classList.contains('cart-item-remove')) {
                if (confirm('Remove this item from cart?')) {
                    Cart.removeItem(productId);
                    renderCartItems();
                }
            }
        });

        // Clear cart button
        const clearCartBtn = document.getElementById('clearCart');
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to clear your cart?')) {
                    Cart.clearCart();
                    renderCartItems();
                }
            });
        }

        // Proceed to quote button
        const proceedBtn = document.getElementById('proceedToQuote');
        if (proceedBtn) {
            proceedBtn.addEventListener('click', function() {
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
            });
        }
    }

    // Expose Cart to global scope for external use
    window.ProPrintCart = Cart;

})();
