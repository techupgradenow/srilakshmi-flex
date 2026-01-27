<?php
/**
 * Products Page
 * Fetches products from database and displays them by category
 */

require_once 'config/database.php';

$conn = getConnection();

// Fetch all active categories
$categories = $conn->query("
    SELECT id, name, image
    FROM categories
    WHERE status = 'active'
    ORDER BY display_order ASC
")->fetchAll();

// Fetch all active products with category info
$products = $conn->query("
    SELECT p.*, c.name as category_name, c.id as category_id
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active'
    ORDER BY c.display_order ASC, p.created_at DESC
")->fetchAll();

// Group products by category
$productsByCategory = [];

// First, initialize all categories (even empty ones)
foreach ($categories as $category) {
    $productsByCategory[$category['name']] = [
        'id' => $category['id'],
        'name' => $category['name'],
        'image' => $category['image'] ?? '',
        'products' => []
    ];
}

// Then, add products to their respective categories
foreach ($products as $product) {
    $catName = $product['category_name'];
    if ($catName && isset($productsByCategory[$catName])) {
        $productsByCategory[$catName]['products'][] = $product;
    } else {
        // Handle uncategorized products
        if (!isset($productsByCategory['Other Products'])) {
            $productsByCategory['Other Products'] = [
                'id' => null,
                'name' => 'Other Products',
                'products' => []
            ];
        }
        $productsByCategory['Other Products']['products'][] = $product;
    }
}

// Remove "Other Products" if it's empty
if (isset($productsByCategory['Other Products']) && empty($productsByCategory['Other Products']['products'])) {
    unset($productsByCategory['Other Products']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | Sri Lakshmi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>
    <!-- Scroll Progress Bar -->
    <div class="scroll-progress" id="scrollProgress"></div>

    <!-- Header & Navigation -->
    <header id="mainHeader">
        <div class="container header-container">
            <a href="index.html" class="logo">
                <i class="fas fa-shopping-bag"></i> Sri<span>Lakshmi</span>
            </a>

            <div class="mobile-toggle" id="mobileToggle">
                <i class="fas fa-bars"></i>
            </div>

            <ul class="nav-menu" id="navMenu">
                <li><a href="index.html" class="nav-link">Home</a></li>
                <li><a href="index.html#why-us" class="nav-link">Why Choose Us</a></li>
                <li><a href="service.html" class="nav-link">Services</a></li>
                <li><a href="ourwork.html" class="nav-link">Our Work</a></li>
                <li><a href="products.php" class="nav-link active">Products</a></li>
                <li><a href="contact.html" class="nav-link">Contact</a></li>
            </ul>

            <a href="cart.html" class="cart-icon" id="cartIcon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count">0</span>
            </a>
        </div>
    </header>

    <!-- Products Hero Section -->
    <section class="hero" style="min-height: auto; padding: 10rem 0 3rem;">
        <div class="container">
            <div class="hero-content">
                <h1>Our Products</h1>
                <p>Explore our premium collection of thamboolam bags, wedding cards, and custom designs</p>
            </div>

            <!-- Categories Browse -->
            <div class="categories-showcase-inline" style="margin-top: 3rem;">
                <!-- All Products Button -->
                <div style="text-align: center; margin-bottom: 2rem;">
                    <button class="category-filter-btn active" data-category="all" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; padding: 0.75rem 2rem; border-radius: 50px; font-size: 1rem; font-weight: 600; cursor: pointer; box-shadow: 0 4px 15px rgba(10, 44, 90, 0.3); transition: all 0.3s ease;">
                        <i class="fas fa-th"></i> All Products
                    </button>
                </div>

                <div class="categories-grid">
                <?php if (!empty($productsByCategory)): ?>
                    <?php foreach ($productsByCategory as $categoryName => $categoryData): ?>
                        <div class="category-card category-filter-btn" data-category="<?php echo $categoryData['id']; ?>">
                            <div class="category-icon">
                                <?php
                                $imagePath = '';
                                if (!empty($categoryData['image'])) {
                                    if (file_exists(__DIR__ . '/uploads/categories/' . $categoryData['image'])) {
                                        $imagePath = 'uploads/categories/' . $categoryData['image'];
                                    } elseif (file_exists(__DIR__ . '/' . $categoryData['image'])) {
                                        $imagePath = $categoryData['image'];
                                    }
                                }

                                if ($imagePath):
                                ?>
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($categoryData['name']); ?>" class="category-image">
                                <?php else: ?>
                                    <i class="fas <?php
                                        // Dynamic icons based on category name
                                        $icon = 'fa-box';
                                        if (stripos($categoryData['name'], 'bag') !== false) $icon = 'fa-shopping-bag';
                                        elseif (stripos($categoryData['name'], 'card') !== false || stripos($categoryData['name'], 'invitation') !== false) $icon = 'fa-envelope';
                                        elseif (stripos($categoryData['name'], 'banner') !== false || stripos($categoryData['name'], 'flex') !== false) $icon = 'fa-flag';
                                        elseif (stripos($categoryData['name'], 'poster') !== false) $icon = 'fa-image';
                                        elseif (stripos($categoryData['name'], 'medal') !== false || stripos($categoryData['name'], 'trophy') !== false) $icon = 'fa-trophy';
                                        elseif (stripos($categoryData['name'], 'uv') !== false || stripos($categoryData['name'], 'print') !== false) $icon = 'fa-print';
                                        elseif (stripos($categoryData['name'], 'design') !== false || stripos($categoryData['name'], 'custom') !== false) $icon = 'fa-palette';
                                        echo $icon;
                                    ?>"></i>
                                <?php endif; ?>
                            </div>
                            <div class="category-info">
                                <h3><?php echo htmlspecialchars($categoryData['name']); ?></h3>
                                <p class="category-count"><?php echo count($categoryData['products']); ?> Product<?php echo count($categoryData['products']) != 1 ? 's' : ''; ?></p>
                            </div>
                            <div class="category-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center" style="grid-column: 1/-1; padding: 3rem;">
                        <i class="fas fa-inbox" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem; display: block;"></i>
                        <p style="color: #999; font-size: 1.1rem;">No categories available yet.</p>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section style="padding: 3rem 0 4rem; background: linear-gradient(to bottom, var(--white), var(--light-gray));">
        <div class="container">
            <?php
            // Flatten all products from all categories into one array
            $allProducts = [];
            foreach ($productsByCategory as $categoryData) {
                foreach ($categoryData['products'] as $product) {
                    $product['category_display'] = $categoryData['name'];
                    $allProducts[] = $product;
                }
            }
            ?>

            <?php if (!empty($allProducts)): ?>
                <div class="medals-gallery">
                    <?php foreach ($allProducts as $product): ?>
                    <div class="medal-design-card fade-in product-item" data-category="<?php echo $product['category_id']; ?>">
                        <div class="medal-preview">
                            <?php
                            // Determine image path
                            $imagePath = '';
                            if ($product['image']) {
                                if (file_exists(__DIR__ . '/uploads/products/' . $product['image'])) {
                                    $imagePath = 'uploads/products/' . $product['image'];
                                } elseif (file_exists(__DIR__ . '/' . $product['image'])) {
                                    $imagePath = $product['image'];
                                }
                            }
                            ?>
                            <?php if ($imagePath): ?>
                                <img src="<?php echo htmlspecialchars($imagePath); ?>"
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f0f0f0;">
                                    <i class="fas fa-image" style="font-size:3rem;color:#ccc;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="medal-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <?php if (!empty($product['description'])): ?>
                                <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?><?php echo strlen($product['description']) > 80 ? '...' : ''; ?></p>
                            <?php endif; ?>
                            <?php if (!empty($product['size'])): ?>
                                <p class="product-size"><i class="fas fa-ruler"></i> <?php echo htmlspecialchars($product['size']); ?></p>
                            <?php endif; ?>
                            <?php if (isset($product['price']) && $product['price'] > 0): ?>
                                <p class="product-price"><i class="fas fa-tag"></i> ₹<?php echo number_format($product['price'], 2); ?></p>
                            <?php endif; ?>
                            <button class="btn btn-primary add-to-cart-btn"
                                    data-id="product-<?php echo $product['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-image="<?php echo htmlspecialchars($imagePath); ?>"
                                    data-category="<?php echo htmlspecialchars($product['category_display']); ?>">
                                <i class="fas fa-shopping-cart"></i> Order Now
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center" style="padding: 4rem 2rem;">
                    <i class="fas fa-box-open" style="font-size: 4rem; color: var(--gray); margin-bottom: 1.5rem; display: block;"></i>
                    <h3 style="color: var(--gray); margin-bottom: 1rem;">No Products Available</h3>
                    <p style="color: var(--gray);">Products will be displayed here once added from the admin panel.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Quick Contact Strip -->
    <section class="quick-contact">
        <div class="container">
            <h3>Need a custom solution? Let's discuss your project!</h3>
            <div class="quick-contact-btns">
                <a href="contact.html" class="btn btn-primary">
                    <i class="fas fa-envelope"></i> Get Free Quote
                </a>
                <a href="https://wa.me/1234567890" target="_blank" class="btn btn-secondary">
                    <i class="fab fa-whatsapp"></i> WhatsApp Us
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>Sri Lakshmi</h3>
                    <p>Your trusted partner for high-quality thamboolam bags, wedding cards, and printing solutions. We deliver professional results with fast turnaround times.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <a href="index.html">Home</a>
                    <a href="index.html#why-us">Why Choose Us</a>
                    <a href="service.html">Services</a>
                    <a href="ourwork.html">Our Work</a>
                    <a href="products.php">Products</a>
                    <a href="contact.html">Contact</a>
                </div>

                <div class="footer-col">
                    <h3>Our Products</h3>
                    <a href="products.php">Thamboolam Bags</a>
                    <a href="products.php">Wedding Invitation Cards</a>
                    <a href="products.php">Flex Banners</a>
                    <a href="products.php">Custom Designs</a>
                </div>

                <div class="footer-col">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Printing Street, Business City</p>
                    <p><i class="fas fa-phone"></i> +1 (234) 567-8900</p>
                    <p><i class="fas fa-envelope"></i> info@srilakshmi.com</p>
                    <p><i class="fas fa-clock"></i> Mon-Sat: 9:00 AM - 7:00 PM</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2024 Sri Lakshmi. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/1234567890" target="_blank" class="whatsapp-float">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
    <script src="js/cart.js"></script>

    <style>
    /* Categories Showcase */
    .categories-showcase {
        position: relative;
        overflow: hidden;
    }

    .categories-showcase::before {
        content: '';
        position: absolute;
        top: 0;
        left: -50%;
        width: 200%;
        height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255, 193, 7, 0.03), transparent);
        animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        position: relative;
        z-index: 1;
    }

    .category-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 16px;
        padding: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        text-decoration: none;
        color: inherit;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: hidden;
    }

    .category-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--primary-color) 0%, #1a3a6e 100%);
        opacity: 0;
        transition: opacity 0.4s ease;
        z-index: 0;
    }

    .category-card:hover::before {
        opacity: 1;
    }

    .category-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 35px rgba(10, 40, 84, 0.2);
        border-color: var(--secondary-color);
    }

    .category-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, var(--secondary-color) 0%, #ffda44 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.4s ease;
        position: relative;
        z-index: 1;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
    }

    .category-card:hover .category-icon {
        transform: rotate(360deg) scale(1.1);
        box-shadow: 0 6px 25px rgba(255, 193, 7, 0.5);
    }

    .category-icon i {
        font-size: 2rem;
        color: var(--primary-color);
        transition: all 0.4s ease;
    }

    .category-card:hover .category-icon i {
        color: #fff;
    }

    .category-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .category-info {
        flex: 1;
        position: relative;
        z-index: 1;
    }

    .category-info h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-color);
        margin: 0 0 0.5rem 0;
        transition: color 0.4s ease;
        line-height: 1.3;
    }

    .category-card:hover .category-info h3 {
        color: #fff;
    }

    .category-count {
        font-size: 0.9rem;
        color: #666;
        margin: 0;
        transition: color 0.4s ease;
        font-weight: 500;
    }

    .category-card:hover .category-count {
        color: var(--secondary-color);
    }

    .category-arrow {
        width: 40px;
        height: 40px;
        background: rgba(10, 40, 84, 0.05);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.4s ease;
        position: relative;
        z-index: 1;
    }

    .category-card:hover .category-arrow {
        background: var(--secondary-color);
        transform: translateX(5px);
    }

    .category-arrow i {
        font-size: 1rem;
        color: var(--primary-color);
        transition: color 0.4s ease;
    }

    .category-card:hover .category-arrow i {
        color: var(--primary-color);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .categories-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .category-card {
            display: flex !important;
            padding: 1.25rem 0.75rem !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: space-between !important;
            text-align: center !important;
            min-height: 200px !important;
            gap: 0.75rem !important;
        }

        .category-icon {
            width: 50px !important;
            height: 50px !important;
            margin: 0 !important;
            flex-shrink: 0 !important;
        }

        .category-icon i {
            font-size: 1.4rem !important;
        }

        .category-info {
            margin: 0 !important;
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            width: 100% !important;
        }

        .category-info h3 {
            font-size: 0.85rem !important;
            line-height: 1.2 !important;
            margin: 0 0 0.35rem 0 !important;
        }

        .category-count {
            font-size: 0.7rem !important;
            margin: 0 !important;
        }

        .category-arrow {
            margin: 0 !important;
            width: 32px !important;
            height: 32px !important;
            flex-shrink: 0 !important;
        }

        .category-arrow i {
            font-size: 0.85rem !important;
        }
    }

    @media (min-width: 769px) and (max-width: 1024px) {
        .categories-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Existing styles */
    .product-description {
        font-size: 0.85rem;
        color: var(--gray);
        margin: 0.5rem 0;
        line-height: 1.4;
    }
    .product-size {
        font-size: 0.8rem;
        color: var(--primary-color);
        margin: 0.5rem 0;
    }
    .product-size i {
        margin-right: 0.25rem;
    }
    .product-price {
        font-size: 1.1rem;
        color: var(--secondary-color);
        margin: 0.75rem 0;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .product-price i {
        font-size: 0.9rem;
    }
    .category-section {
        scroll-margin-top: 100px;
    }

    /* Category Filter Styles */
    .category-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .category-card.active {
        border: 3px solid var(--secondary-color);
        box-shadow: 0 10px 30px rgba(255, 193, 7, 0.4);
        transform: translateY(-5px) scale(1.05);
    }

    .category-filter-btn.active {
        background: linear-gradient(135deg, var(--secondary-color), var(--accent-color)) !important;
        transform: scale(1.1);
    }

    .product-item {
        transition: all 0.4s ease;
    }

    .product-item.hidden {
        display: none !important;
    }

    .category-card:hover {
        transform: translateY(-5px);
    }

    /* Mobile: Hide products initially until category is selected */
    @media (max-width: 768px) {
        .medals-gallery {
            display: none;
        }

        .medals-gallery.show-products {
            display: grid;
        }

        /* Hide "All Products" button on mobile */
        .category-filter-btn[data-category="all"] {
            display: none;
        }
    }
    </style>

    <script>
    // Category Filtering JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const categoryButtons = document.querySelectorAll('.category-filter-btn');
        const productItems = document.querySelectorAll('.product-item');
        const allProductsBtn = document.querySelector('[data-category="all"]');
        const productsGallery = document.querySelector('.medals-gallery');
        const isMobile = () => window.innerWidth <= 768;

        // Function to filter products
        function filterProducts(categoryId) {
            // On mobile, show the products section when a category is clicked
            if (isMobile() && productsGallery) {
                productsGallery.classList.add('show-products');
            }

            productItems.forEach(product => {
                const productCategory = product.getAttribute('data-category');

                if (categoryId === 'all' || productCategory === categoryId.toString()) {
                    product.classList.remove('hidden');
                    // Re-trigger fade-in animation
                    product.style.opacity = '0';
                    setTimeout(() => {
                        product.style.opacity = '1';
                    }, 10);
                } else {
                    product.classList.add('hidden');
                }
            });

            // Scroll to products section smoothly
            if (productsGallery) {
                setTimeout(() => {
                    productsGallery.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            }
        }

        // Add click handlers to all category buttons
        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-category');

                // Remove active class from all buttons
                categoryButtons.forEach(btn => btn.classList.remove('active'));

                // Add active class to clicked button
                this.classList.add('active');

                // Filter products
                filterProducts(categoryId);
            });
        });

        // Set initial state
        // Desktop: show all products
        // Mobile: hide products until category is selected
        if (!isMobile()) {
            if (allProductsBtn) {
                allProductsBtn.classList.add('active');
            }
            if (productsGallery) {
                productsGallery.classList.add('show-products');
            }
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (!isMobile() && productsGallery) {
                productsGallery.classList.add('show-products');
                if (allProductsBtn && !document.querySelector('.category-filter-btn.active')) {
                    allProductsBtn.classList.add('active');
                }
            }
        });
    });
    </script>
</body>
</html>
