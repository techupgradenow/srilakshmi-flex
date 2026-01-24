<?php
/**
 * Products Page
 * Fetches products from database and displays them by category
 */

require_once 'config/database.php';

$conn = getConnection();

// Fetch all active products with category info
// Using LEFT JOIN to include products without categories
$products = $conn->query("
    SELECT p.*, COALESCE(c.name, 'Other Products') as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active'
    ORDER BY
        CASE WHEN c.name IS NULL THEN 1 ELSE 0 END,
        c.name ASC,
        p.created_at DESC
")->fetchAll();

// Group products by category
$productsByCategory = [];
foreach ($products as $product) {
    $catName = $product['category_name'];
    if (!isset($productsByCategory[$catName])) {
        $productsByCategory[$catName] = [];
    }
    $productsByCategory[$catName][] = $product;
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
    <section class="hero" style="min-height: 60vh; padding: 10rem 0 4rem;">
        <div class="container">
            <div class="hero-content">
                <h1>Our Products</h1>
                <p>Explore our premium collection of thamboolam bags, wedding cards, and custom designs</p>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="section-padding" style="background: linear-gradient(to bottom, var(--white), var(--light-gray));">
        <div class="container">

            <?php if (!empty($productsByCategory)): ?>
                <?php foreach ($productsByCategory as $categoryName => $categoryProducts): ?>
                    <div class="category-section fade-in" style="margin-bottom: 4rem;">
                        <div class="text-center" style="margin-bottom: 2rem;">
                            <h2 class="text-center"><?php echo htmlspecialchars($categoryName); ?></h2>
                            <p><?php echo count($categoryProducts); ?> products available</p>
                        </div>

                        <div class="medals-gallery">
                            <?php foreach ($categoryProducts as $product): ?>
                                <div class="medal-design-card fade-in">
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
                                        <button class="btn btn-primary add-to-cart-btn"
                                                data-id="product-<?php echo $product['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                data-image="<?php echo htmlspecialchars($imagePath); ?>"
                                                data-category="<?php echo htmlspecialchars($categoryName); ?>">
                                            <i class="fas fa-shopping-cart"></i> Order Now
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
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
    .category-section {
        scroll-margin-top: 100px;
    }
    </style>
</body>
</html>
