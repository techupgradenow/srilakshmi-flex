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
                <div class="all-products-wrapper" style="text-align: center; margin-bottom: 2rem;">
                    <button class="category-filter-btn all-products-btn" data-category="all" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; padding: 0.75rem 2rem; border-radius: 50px; font-size: 1rem; font-weight: 600; cursor: pointer; box-shadow: 0 4px 15px rgba(10, 44, 90, 0.3); transition: all 0.3s ease;">
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
            <!-- Back to Categories Button (Mobile Only) -->
            <div class="back-to-categories-wrapper" style="display: none; margin-bottom: 1.5rem;">
                <button class="back-to-categories-btn" onclick="backToCategories()">
                    <i class="fas fa-arrow-left"></i> Back to Categories
                </button>
            </div>
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
                            <button class="btn btn-primary view-product-btn"
                                    data-id="<?php echo $product['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-price="<?php echo $product['price'] ?? 0; ?>"
                                    data-size="<?php echo htmlspecialchars($product['size'] ?? ''); ?>"
                                    data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                    data-image="<?php echo htmlspecialchars($imagePath); ?>"
                                    data-category="<?php echo htmlspecialchars($product['category_display']); ?>">
                                <i class="fas fa-eye"></i> View Details
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

    <!-- Product Details Modal -->
    <div id="productModal" class="product-modal">
        <div class="modal-overlay" onclick="closeProductModal()"></div>
        <div class="modal-content">
            <button class="modal-close" onclick="closeProductModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-body">
                <div class="modal-image">
                    <img id="modalProductImage" src="" alt="">
                </div>
                <div class="modal-info">
                    <h2 id="modalProductName"></h2>
                    <p class="modal-category">
                        <i class="fas fa-tag"></i> <span id="modalProductCategory"></span>
                    </p>
                    <p class="modal-description" id="modalProductDescription"></p>
                    <p class="modal-size" id="modalProductSize"></p>
                    <p class="modal-price" id="modalProductPrice"></p>
                    <div class="modal-actions">
                        <button class="btn btn-primary" id="modalAddToCart">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="btn btn-secondary" onclick="closeProductModal()">
                            <i class="fas fa-times"></i> Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        align-items: stretch;
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
        min-height: 120px;
        height: 100%;
        box-sizing: border-box;
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
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 70px;
    }

    .category-info h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-color);
        margin: 0 0 0.5rem 0;
        transition: color 0.4s ease;
        line-height: 1.3;
        word-wrap: break-word;
        overflow-wrap: break-word;
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

    /* Responsive - Mobile First */
    @media (max-width: 768px) {
        /* Enable vertical scroll, prevent horizontal overflow */
        body {
            overflow-x: hidden !important;
            overflow-y: auto !important;
            position: static !important;
            height: auto !important;
            min-height: 100vh !important;
        }

        .container {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
        }

        /* Hero section must not restrict height */
        .hero {
            min-height: auto !important;
            height: auto !important;
            padding: 6rem 0 2rem !important;
        }

        .categories-showcase-inline {
            width: 100% !important;
            overflow: visible !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            height: auto !important;
            max-height: none !important;
            min-height: auto !important;
            padding-bottom: 4rem !important;
            position: static !important;
        }

        /* VERTICAL LIST VIEW - Replace grid with flex column */
        .categories-grid {
            display: flex !important;
            flex-direction: column !important;
            gap: 0.75rem !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            box-sizing: border-box !important;
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }

        /* Category cards as horizontal list items */
        .category-card {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
            text-align: left !important;
            padding: 1rem !important;
            min-height: 60px !important;
            height: auto !important;
            max-height: none !important;
            gap: 1rem !important;
            width: 100% !important;
            box-sizing: border-box !important;
            border-radius: 12px !important;
            overflow: visible !important;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
            transition: all 0.2s ease !important;
        }

        .category-card:active {
            transform: scale(0.98) !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12) !important;
        }

        .category-card::before {
            display: none !important;
        }

        /* Icon on the left */
        .category-icon {
            width: 50px !important;
            height: 50px !important;
            min-width: 50px !important;
            min-height: 50px !important;
            margin: 0 !important;
            flex-shrink: 0 !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: linear-gradient(135deg, var(--secondary-color) 0%, #ffda44 100%) !important;
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3) !important;
        }

        .category-icon i {
            font-size: 1.4rem !important;
            color: var(--primary-color) !important;
        }

        .category-icon img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 50% !important;
        }

        /* Category info in the middle - takes available space */
        .category-info {
            margin: 0 !important;
            padding: 0 !important;
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            justify-content: center !important;
            width: auto !important;
            max-width: none !important;
            overflow: visible !important;
        }

        .category-info h3 {
            font-size: 0.95rem !important;
            line-height: 1.3 !important;
            margin: 0 0 0.2rem 0 !important;
            padding: 0 !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            text-align: left !important;
            width: 100% !important;
            color: var(--primary-color) !important;
            font-weight: 600 !important;
        }

        .category-count {
            font-size: 0.75rem !important;
            margin: 0 !important;
            padding: 0 !important;
            color: #666 !important;
            font-weight: 500 !important;
        }

        /* Arrow on the right */
        .category-arrow {
            margin: 0 !important;
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
            min-height: 32px !important;
            flex-shrink: 0 !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: rgba(10, 40, 84, 0.08) !important;
        }

        .category-arrow i {
            font-size: 0.9rem !important;
            color: var(--primary-color) !important;
        }

        /* Disable hover effects on mobile */
        .category-card:hover {
            transform: none !important;
        }

        .category-card:hover .category-icon {
            transform: none !important;
        }

        /* Mobile All Products Button - Hidden on mobile */
        .all-products-wrapper {
            display: none !important;
        }

        .all-products-btn {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0.85rem 1.5rem !important;
            font-size: 0.9rem !important;
            border-radius: 12px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.5rem !important;
        }

        .all-products-btn.active {
            background: linear-gradient(135deg, var(--secondary-color), #ffda44) !important;
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4) !important;
        }

        /* Back to Categories Button */
        .back-to-categories-wrapper {
            display: none !important;
        }

        .back-to-categories-wrapper.show {
            display: block !important;
        }

        .back-to-categories-btn {
            width: 100% !important;
            padding: 0.85rem 1.5rem !important;
            background: linear-gradient(135deg, var(--primary-color), #1a3a6e) !important;
            color: white !important;
            border: none !important;
            border-radius: 12px !important;
            font-size: 0.9rem !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.5rem !important;
            box-shadow: 0 4px 15px rgba(10, 44, 90, 0.3) !important;
            transition: all 0.3s ease !important;
        }

        .back-to-categories-btn:active {
            transform: scale(0.98) !important;
        }

        /* Hide categories when products are shown */
        .categories-showcase-inline.hide-on-mobile {
            display: none !important;
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

    /* Product Modal Styles */
    .product-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        animation: fadeIn 0.3s ease;
    }

    .product-modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
    }

    .modal-content {
        position: relative;
        background: white;
        border-radius: 20px;
        max-width: 900px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
        z-index: 10000;
    }

    .modal-close {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 40px;
        height: 40px;
        border: none;
        background: rgba(0, 0, 0, 0.1);
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        z-index: 10001;
    }

    .modal-close:hover {
        background: var(--primary-color);
        color: white;
        transform: rotate(90deg);
    }

    .modal-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        padding: 2rem;
    }

    .modal-image {
        border-radius: 15px;
        overflow: hidden;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-image img {
        width: 100%;
        height: auto;
        object-fit: contain;
    }

    .modal-info h2 {
        font-size: 1.8rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    .modal-category {
        display: inline-block;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: #ffffff;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }

    .modal-description {
        color: #666;
        line-height: 1.6;
        margin: 1rem 0;
    }

    .modal-size {
        color: var(--primary-color);
        font-weight: 600;
        margin: 0.5rem 0;
    }

    .modal-price {
        font-size: 2rem;
        color: var(--secondary-color);
        font-weight: 800;
        margin: 1rem 0;
    }

    .modal-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .modal-actions .btn {
        flex: 1;
        padding: 1rem;
        font-size: 1rem;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Mobile Modal Styles */
    @media (max-width: 768px) {
        .modal-body {
            grid-template-columns: 1fr;
            padding: 1.5rem;
        }

        .modal-info h2 {
            font-size: 1.4rem;
        }

        .modal-price {
            font-size: 1.5rem;
        }

        .modal-actions {
            flex-direction: column;
        }

        /* Products shown on desktop, hidden on mobile until category selected */
        .medals-gallery {
            display: none;
        }

        .medals-gallery.show-products {
            display: grid;
        }
    }

    /* Desktop: Always show products */
    @media (min-width: 769px) {
        .medals-gallery {
            display: grid !important;
        }
    }

    /* Cart Toast Notification */
    .cart-toast {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        padding: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        min-width: 320px;
        max-width: 420px;
        z-index: 10000;
        transform: translateY(150%);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 4px solid #28a745;
    }

    .cart-toast.show {
        transform: translateY(0);
        opacity: 1;
    }

    .cart-toast-content {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }

    .cart-toast-content i {
        font-size: 1.5rem;
        color: #28a745;
        flex-shrink: 0;
    }

    .cart-toast-text {
        flex: 1;
    }

    .cart-toast-text strong {
        display: block;
        color: var(--primary-color);
        font-size: 0.95rem;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }

    .cart-toast-text p {
        margin: 0;
        color: #666;
        font-size: 0.85rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cart-toast-link {
        padding: 0.6rem 1.2rem;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
        transition: all 0.3s ease;
    }

    .cart-toast-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(10, 44, 90, 0.3);
    }

    /* Cart count animation */
    .cart-count-updated {
        animation: cartBounce 0.5s ease;
    }

    @keyframes cartBounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.3); }
    }

    /* Mobile responsive toast */
    @media (max-width: 768px) {
        .cart-toast {
            bottom: 1rem;
            right: 1rem;
            left: 1rem;
            min-width: auto;
            max-width: none;
        }

        .cart-toast-text p {
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
        }
    }

    /* Add to cart button success state */
    .added-to-cart {
        background: #28a745 !important;
        border-color: #28a745 !important;
    }
    </style>

    <script>
    // Product Modal Functions
    function openProductModal(productData) {
        const modal = document.getElementById('productModal');
        document.getElementById('modalProductImage').src = productData.image;
        document.getElementById('modalProductName').textContent = productData.name;
        document.getElementById('modalProductCategory').textContent = productData.category;
        document.getElementById('modalProductDescription').textContent = productData.description;

        const sizeEl = document.getElementById('modalProductSize');
        if (productData.size) {
            sizeEl.innerHTML = '<i class="fas fa-ruler"></i> Size: ' + productData.size;
            sizeEl.style.display = 'block';
        } else {
            sizeEl.style.display = 'none';
        }

        const priceEl = document.getElementById('modalProductPrice');
        if (productData.price > 0) {
            priceEl.innerHTML = '<i class="fas fa-tag"></i> ₹' + parseFloat(productData.price).toFixed(2);
            priceEl.style.display = 'block';
        } else {
            priceEl.style.display = 'none';
        }

        // Setup add to cart button
        const addToCartBtn = document.getElementById('modalAddToCart');
        addToCartBtn.setAttribute('data-id', 'product-' + productData.id);
        addToCartBtn.setAttribute('data-name', productData.name);
        addToCartBtn.setAttribute('data-image', productData.image);
        addToCartBtn.setAttribute('data-category', productData.category);
        addToCartBtn.className = 'btn btn-primary add-to-cart-btn';

        // Remove any existing click handlers to prevent duplicates
        addToCartBtn.replaceWith(addToCartBtn.cloneNode(true));
        const freshBtn = document.getElementById('modalAddToCart');
        freshBtn.className = 'btn btn-primary add-to-cart-btn';
        freshBtn.setAttribute('data-id', 'product-' + productData.id);
        freshBtn.setAttribute('data-name', productData.name);
        freshBtn.setAttribute('data-image', productData.image);
        freshBtn.setAttribute('data-category', productData.category);

        // Add direct click handler for modal button (in addition to event delegation)
        freshBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Modal Add to Cart clicked!', {
                id: freshBtn.dataset.id,
                name: freshBtn.dataset.name,
                image: freshBtn.dataset.image,
                category: freshBtn.dataset.category
            });

            // Trigger cart addition using the global Cart object
            if (window.ProPrintCart) {
                const product = {
                    id: freshBtn.dataset.id,
                    name: freshBtn.dataset.name,
                    image: freshBtn.dataset.image,
                    category: freshBtn.dataset.category
                };

                window.ProPrintCart.addItem(product);
                console.log('Product added to cart via direct handler');
                console.log('Cart now has', window.ProPrintCart.getTotalItems(), 'items');

                // Show button feedback
                const originalText = freshBtn.innerHTML;
                freshBtn.innerHTML = '<i class="fas fa-check"></i> Added!';
                freshBtn.classList.add('added-to-cart');
                freshBtn.disabled = true;

                setTimeout(() => {
                    freshBtn.innerHTML = originalText;
                    freshBtn.classList.remove('added-to-cart');
                    freshBtn.disabled = false;
                }, 1500);

                // Show toast notification
                const existingToast = document.querySelector('.cart-toast');
                if (existingToast) {
                    existingToast.remove();
                }

                const toast = document.createElement('div');
                toast.className = 'cart-toast';
                toast.innerHTML = `
                    <div class="cart-toast-content">
                        <i class="fas fa-check-circle"></i>
                        <div class="cart-toast-text">
                            <strong>Added to Cart!</strong>
                            <p>${product.name}</p>
                        </div>
                    </div>
                    <a href="cart.html" class="cart-toast-link">View Cart</a>
                `;

                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.classList.add('show');
                }, 10);

                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }, 4000);
            } else {
                console.error('ProPrintCart not found! Cart.js may not be loaded.');
            }
        });

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeProductModal() {
        const modal = document.getElementById('productModal');
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Back to Categories Function
    function backToCategories() {
        const isMobile = window.innerWidth <= 768;
        if (!isMobile) return;

        const categoriesSection = document.querySelector('.categories-showcase-inline');
        const productsGallery = document.querySelector('.medals-gallery');
        const backButton = document.querySelector('.back-to-categories-wrapper');
        const categoriesGrid = document.querySelector('.categories-grid');

        // CRITICAL: Reset body scroll first
        document.body.style.overflow = 'auto';
        document.body.style.position = 'static';
        document.body.style.height = 'auto';

        // Show categories and reset all layout styles
        if (categoriesSection) {
            categoriesSection.classList.remove('hide-on-mobile');
            // Force reset inline styles
            categoriesSection.style.height = 'auto';
            categoriesSection.style.maxHeight = 'none';
            categoriesSection.style.overflow = 'visible';
            categoriesSection.style.transform = 'none';
            categoriesSection.style.position = 'static';
        }

        // Reset category grid styles
        if (categoriesGrid) {
            categoriesGrid.style.display = 'flex';
            categoriesGrid.style.flexDirection = 'column';
            categoriesGrid.style.height = 'auto';
            categoriesGrid.style.maxHeight = 'none';
            categoriesGrid.style.overflow = 'visible';
        }

        // Hide products
        if (productsGallery) {
            productsGallery.classList.remove('show-products');
        }

        // Hide back button
        if (backButton) {
            backButton.classList.remove('show');
        }

        // Remove all active states
        document.querySelectorAll('.category-filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Clear session storage
        sessionStorage.removeItem('selectedCategory');

        // Force reflow to ensure DOM updates
        if (categoriesSection) {
            void categoriesSection.offsetHeight;
        }

        // Scroll to top smoothly
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Category Filtering JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const categoryButtons = document.querySelectorAll('.category-filter-btn');
        const productItems = document.querySelectorAll('.product-item');
        const allProductsBtn = document.querySelector('[data-category="all"]');
        const productsGallery = document.querySelector('.medals-gallery');
        const isMobile = () => window.innerWidth <= 768;

        // Function to filter products
        function filterProducts(categoryId, saveToSession = true) {
            const categoriesSection = document.querySelector('.categories-showcase-inline');
            const backButton = document.querySelector('.back-to-categories-wrapper');

            // Save selected category to sessionStorage
            if (saveToSession) {
                sessionStorage.setItem('selectedCategory', categoryId);
            }

            // On mobile, show products and hide categories
            if (isMobile()) {
                if (productsGallery) {
                    productsGallery.classList.add('show-products');
                }

                // Hide categories section
                if (categoriesSection) {
                    categoriesSection.classList.add('hide-on-mobile');
                }

                // Show back button
                if (backButton) {
                    backButton.classList.add('show');
                }
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

            // Scroll to products section smoothly on mobile
            if (productsGallery && isMobile()) {
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

        // Initial state setup
        if (isMobile()) {
            // Mobile: Always show categories first, hide products
            const categoriesSection = document.querySelector('.categories-showcase-inline');
            const backButton = document.querySelector('.back-to-categories-wrapper');

            // Ensure categories are visible
            if (categoriesSection) {
                categoriesSection.classList.remove('hide-on-mobile');
            }

            // Hide products gallery
            if (productsGallery) {
                productsGallery.classList.remove('show-products');
            }

            // Hide back button
            if (backButton) {
                backButton.classList.remove('show');
            }

            // Clear saved category on mobile
            sessionStorage.removeItem('selectedCategory');
        } else {
            // Desktop: Restore previous selection or show all
            const savedCategory = sessionStorage.getItem('selectedCategory');

            if (savedCategory && savedCategory !== 'all') {
                const savedButton = document.querySelector('[data-category="' + savedCategory + '"]');
                if (savedButton) {
                    categoryButtons.forEach(btn => btn.classList.remove('active'));
                    savedButton.classList.add('active');
                    filterProducts(savedCategory, false);
                }
            } else {
                if (allProductsBtn) {
                    allProductsBtn.classList.add('active');
                }
                if (productsGallery) {
                    productsGallery.classList.add('show-products');
                }
            }
        }

        // View Product Button Handlers
        document.querySelectorAll('.view-product-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productData = {
                    id: this.getAttribute('data-id'),
                    name: this.getAttribute('data-name'),
                    price: this.getAttribute('data-price'),
                    size: this.getAttribute('data-size'),
                    description: this.getAttribute('data-description'),
                    image: this.getAttribute('data-image'),
                    category: this.getAttribute('data-category')
                };
                openProductModal(productData);
            });
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeProductModal();
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const categoriesSection = document.querySelector('.categories-showcase-inline');
            const backButton = document.querySelector('.back-to-categories-wrapper');

            if (!isMobile()) {
                // Desktop: Show all content
                if (productsGallery) {
                    productsGallery.classList.add('show-products');
                }
                if (categoriesSection) {
                    categoriesSection.classList.remove('hide-on-mobile');
                }
                if (backButton) {
                    backButton.classList.remove('show');
                }
                if (allProductsBtn && !document.querySelector('.category-filter-btn.active')) {
                    allProductsBtn.classList.add('active');
                }
            }
        });
    });
    </script>
</body>
</html>
