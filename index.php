<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = "Fresh Rescue - Save Food. Save Money.";
$pathPrefix = '';
$activePage = 'home';

$conn = getDatabaseConnection();

// Fetch categories
$categoriesResult = $conn->query("SELECT * FROM `category` ORDER BY `id` ASC");
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch featured products (limit 8)
$featuredResult = $conn->query("SELECT p.*, c.name as category_name FROM `product` p LEFT JOIN `category` c ON p.category_id = c.id WHERE p.status = 'active' ORDER BY p.id DESC LIMIT 8");
$featuredProducts = [];
while ($row = $featuredResult->fetch_assoc()) {
    $featuredProducts[] = $row;
}

// Fetch rescue zone products (expiring in 3 days or less, limit 4)
$today = date('Y-m-d');
$maxExpiry = date('Y-m-d', strtotime('+3 days'));
$rescueResult = $conn->query("SELECT p.*, c.name as category_name FROM `product` p LEFT JOIN `category` c ON p.category_id = c.id WHERE p.status = 'active' AND p.expiration_date <= '$maxExpiry' ORDER BY p.expiration_date ASC LIMIT 4");
$rescueProducts = [];
while ($row = $rescueResult->fetch_assoc()) {
    $rescueProducts[] = $row;
}

include __DIR__ . '/includes/head.php';
?>

<header class="homepage-header">
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <section class="hero-sec">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-10 col-lg-7">
          <h1 class="hero-title">Save Food.<br><span>Save Money.</span></h1>
          <p class="hero-desc">FreshRescue connects you with local groceries to buy daily essentials close to expiry
            at a fraction of the cost. Fresh, safe, and sustainable.</p>
          <a href="products.php" class="btn btn-primary-custom">Shop Now</a>
        </div>
      </div>
    </div>
  </section>
</header>

<section class="py-5" id="categories">
  <div class="container py-4">
    <div class="text-center mb-5">
      <h2 class="fw-bold mb-2 text-dark">Shop by <span class="text-primary-custom">Category</span></h2>
      <p class="text-muted">Browse our top categories and find fresh products at great prices.</p>
    </div>

    <div class="row g-4 mb-5">
      <?php 
      $catIcons = [
          1 => 'bi-egg-fried',       // Fruits
          2 => 'bi-cup-straw',       // Dairy
          3 => 'bi-basket',          // Bakery
          4 => 'bi-tree',            // Vegetables
      ];
      $catImages = [
          1 => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&q=80&w=400',
          2 => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&q=80&w=400',
          3 => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&q=80&w=400',
          4 => 'https://images.unsplash.com/photo-1574316071802-0d684efa7bf5?auto=format&fit=crop&q=80&w=400',
      ];
      foreach ($categories as $cat): 
          $icon = $catIcons[$cat['id']] ?? 'bi-tag';
          $image = $catImages[$cat['id']] ?? 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=400';
      ?>
      <div class="col-sm-6 col-md-3">
        <div class="category-shape-card">
          <div class="csc-img-wrap">
            <img src="<?php echo $image; ?>" alt="<?php echo esc($cat['name']); ?>">
          </div>
          <div class="csc-badge">
            <i class="bi <?php echo $icon; ?>"></i>
          </div>
          <div class="csc-body">
            <h4><?php echo esc($cat['name']); ?></h4>
            <p><?php echo esc($cat['description']); ?></p>
            <a href="products.php?category=<?php echo urlencode(strtolower($cat['name'])); ?>" class="btn btn-csc-shop">
              Shop Now <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="row justify-content-center pt-5 border-top g-4">
      <div class="col-md-10 col-lg-9">
        <div class="d-flex flex-column flex-md-row justify-content-around align-items-center gap-3 text-start">
          <div class="d-flex align-items-center gap-3">
            <i class="bi bi-leaf text-success fs-3"></i>
            <div>
              <h6 class="fw-bold mb-0 text-dark">Fresh Quality</h6>
              <small class="text-muted">100% fresh products</small>
            </div>
          </div>
          <div class="d-none d-md-block trust-divider"></div>
          <div class="d-flex align-items-center gap-3">
            <i class="bi bi-tag text-success fs-3"></i>
            <div>
              <h6 class="fw-bold mb-0 text-dark">Best Prices</h6>
              <small class="text-muted">Save more every day</small>
            </div>
          </div>
          <div class="d-none d-md-block trust-divider"></div>
          <div class="d-flex align-items-center gap-3">
            <i class="bi bi-truck text-success fs-3"></i>
            <div>
              <h6 class="fw-bold mb-0 text-dark">Fast Delivery</h6>
              <small class="text-muted">Quick delivery to your door</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-light">
  <div class="container py-4">
    <div class="featured-title-wrap">
      <h2 class="fw-bold mb-2 text-dark">Featured <span class="text-primary-custom">Products</span></h2>
      <p class="text-muted">Check out our most popular groceries, handpicked for freshness and quality.</p>
      <div class="featured-line-deco"></div>
    </div>

    <div class="row g-4">
      <?php foreach ($featuredProducts as $product): 
          $hasDiscount = $product['discount'] > 0;
          $finalPrice = $product['price'] - ($product['price'] * ($product['discount'] / 100));
          $isStock = $product['quantity'] > 0;
      ?>
      <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="product-card">
          <div class="product-card-img-wrap">
            <span class="product-card-badge <?php echo $isStock ? '' : 'out-of-stock'; ?>">
              <?php echo $isStock ? 'In Stock' : 'Out of Stock'; ?>
            </span>
            <img src="<?php echo esc($product['image']); ?>" alt="<?php echo esc($product['name']); ?>">
          </div>
          <div class="product-card-body">
            <h5 class="product-card-title"><?php echo esc($product['name']); ?></h5>
            <div class="product-card-price-wrap">
              <span class="product-card-price">$<?php echo number_format($finalPrice, 2); ?></span>
              <?php if ($hasDiscount): ?>
                <span class="product-card-price-old">$<?php echo number_format($product['price'], 2); ?></span>
                <span class="badge-discount-custom">-<?php echo $product['discount']; ?>%</span>
              <?php endif; ?>
            </div>
            <div class="product-card-actions">
              <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn-card-view"><i class="bi bi-eye"></i> Details</a>
              <?php if ($isStock): ?>
                <form action="cart/add.php" method="POST" class="m-0 d-inline">
                  <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                  <button type="submit" class="btn-card-add w-100"><i class="bi bi-cart"></i> Add to Cart</button>
                </form>
              <?php else: ?>
                <button class="btn-card-add w-100" disabled>Out of Stock</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center">
      <a href="products.php" class="btn btn-view-all-custom">View All Products <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
</section>

<section class="py-5" id="expiry-rescue">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-baseline mb-4">
      <h2 class="fw-bold m-0 text-primary-custom"><i class="bi bi-hourglass-split me-2"></i>Rescue Zone (Near Expiration)</h2>
      <a href="products.php?filter=near-expiry" class="text-success fw-bold text-decoration-none">View All Deals <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="row g-4">
      <?php foreach ($rescueProducts as $product): 
          $hasDiscount = $product['discount'] > 0;
          $finalPrice = $product['price'] - ($product['price'] * ($product['discount'] / 100));
          $isStock = $product['quantity'] > 0;
          $expiryInfo = calculateExpiry($product['expiration_date']);
      ?>
      <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="product-card">
          <div class="product-card-img-wrap">
            <img src="<?php echo esc($product['image']); ?>" alt="<?php echo esc($product['name']); ?>">
          </div>
          <div class="product-card-body">
            <h5 class="product-card-title"><?php echo esc($product['name']); ?></h5>
            <div class="product-card-price-wrap">
              <span class="product-card-price">$<?php echo number_format($finalPrice, 2); ?></span>
              <?php if ($hasDiscount): ?>
                <span class="product-card-price-old">$<?php echo number_format($product['price'], 2); ?></span>
                <span class="badge-discount-custom">-<?php echo $product['discount']; ?>%</span>
              <?php endif; ?>
            </div>
            <div class="stock-status">
              <i class="bi <?php echo $expiryInfo['icon']; ?> <?php echo $expiryInfo['class']; ?> me-1"></i>
              <span class="<?php echo $expiryInfo['class']; ?>"><?php echo esc($expiryInfo['text']); ?></span>
            </div>
            <div class="product-card-actions">
              <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn-card-view">Details</a>
              <?php if ($isStock): ?>
                <form action="cart/add.php" method="POST" class="m-0 d-inline">
                  <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                  <button type="submit" class="btn-card-add w-100">Add to Cart</button>
                </form>
              <?php else: ?>
                <button class="btn-card-add w-100" disabled>Out of Stock</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>