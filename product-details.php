<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$conn = getDatabaseConnection();

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM `product` p LEFT JOIN `category` c ON p.category_id = c.id WHERE p.id = ? AND p.status = 'active'");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    // Redirect or display first product if not found
    $firstStmt = $conn->query("SELECT p.*, c.name as category_name FROM `product` p LEFT JOIN `category` c ON p.category_id = c.id WHERE p.status = 'active' LIMIT 1");
    $product = $firstStmt->fetch_assoc();
}

// Generate related products
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM `product` p LEFT JOIN `category` c ON p.category_id = c.id WHERE p.id != ? AND p.status = 'active' LIMIT 4");
$stmt->bind_param("i", $product['id']);
$stmt->execute();
$relatedResult = $stmt->get_result();
$relatedProducts = [];
while ($row = $relatedResult->fetch_assoc()) {
    $relatedProducts[] = $row;
}
$stmt->close();

$hasDiscount = $product['discount'] > 0;
$finalPrice = $product['price'] - ($product['price'] * ($product['discount'] / 100));
$isStock = $product['quantity'] > 0;
$expiryInfo = calculateExpiry($product['expiration_date']);

$pageTitle = $product['name'] . " - Fresh Rescue";
$pathPrefix = '';
$activePage = 'products';

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="py-5">
  <div class="container py-4">
    <div class="row g-5">

      <div class="col-md-6">
        <div class="details-img-wrap shadow-sm border rounded bg-white overflow-hidden p-0 d-flex align-items-center justify-content-center" style="height: 400px;">
          <img src="<?php echo esc($product['image']); ?>" alt="<?php echo esc($product['name']); ?>" class="w-100 h-100" id="productMainImg" style="object-fit: cover;">
        </div>
      </div>

      <div class="col-md-6">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
          <span class="badge bg-success-subtle text-success border border-success-subtle py-1.5 px-3 rounded-pill fw-semibold">
            <i class="bi bi-tag-fill me-1"></i><?php echo esc($product['category_name']); ?>
          </span>
          <span class="badge <?php echo $expiryInfo['badge_class']; ?> py-1.5 px-3 rounded-pill fw-semibold">
            <i class="bi <?php echo $expiryInfo['icon']; ?> me-1"></i><?php echo esc($expiryInfo['text']); ?>
          </span>
        </div>

        <h1 class="details-title fw-bold mb-3"><?php echo esc($product['name']); ?></h1>

        <div class="d-flex align-items-baseline gap-3 mb-4">
          <span class="fs-2 fw-extrabold text-success" style="color: var(--primary-color);">$<?php echo number_format($finalPrice, 2); ?></span>
          <?php if ($hasDiscount): ?>
            <span class="fs-5 text-muted text-decoration-line-through">$<?php echo number_format($product['price'], 2); ?></span>
            <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fs-8 fw-bold">-<?php echo $product['discount']; ?>% OFF</span>
          <?php endif; ?>
        </div>

        <div class="stock-status mb-4 p-3 bg-light rounded" style="font-size: 0.9rem;">
          <span class="text-dark fw-bold">Availability:</span>
          <span class="ms-1 <?php echo $isStock ? 'text-success' : 'text-danger'; ?> fw-bold"><?php echo $isStock ? 'In Stock' : 'Out of Stock'; ?></span>
          <span class="text-muted mx-2">|</span>
          <span class="text-dark fw-bold">Rescue Status:</span>
          <span class="ms-1 <?php echo $expiryInfo['class']; ?> fw-bold"><?php echo esc($expiryInfo['text']); ?></span>
        </div>

        <p class="details-desc text-muted mb-4" style="line-height: 1.6;"><?php echo esc($product['description']); ?></p>

        <hr class="my-4">

        <?php if ($isStock): ?>
          <form action="cart/add.php" method="POST">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <input type="hidden" name="action" value="add">

            <div class="row g-3 mb-4">
              <div class="col-4 col-sm-3">
                <label for="qty" class="form-label fs-8 text-muted mb-1 fw-bold">Quantity</label>
                <input type="number" class="form-control form-control-lg details-qty-input border-2 text-center" id="qty" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" required>
              </div>
              <div class="col-8 col-sm-9 d-flex align-items-end">
                <button type="submit" class="btn btn-success btn-lg w-100 py-3 fw-bold bg-primary-custom rounded-custom d-flex align-items-center justify-content-center gap-2">
                  <i class="bi bi-cart3"></i> Add to Cart
                </button>
              </div>
            </div>
          </form>
        <?php else: ?>
          <button type="button" class="btn btn-secondary btn-lg w-100 py-3 fw-bold rounded-custom mb-4" disabled>Out of Stock</button>
        <?php endif; ?>

        <form action="wishlist/process.php" method="POST" class="m-0">
          <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
          <button type="submit" class="btn btn-light border w-100 py-2.5 fw-semibold rounded-custom text-dark d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-heart"></i> Add to Wishlist
          </button>
        </form>

      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-light border-top">
  <div class="container">
    <h3 class="fw-bold mb-4 text-dark">Related <span class="text-primary-custom">Deals</span></h3>
    <div class="row g-4">
      <?php foreach ($relatedProducts as $rp): 
          $rpHasDiscount = $rp['discount'] > 0;
          $rpFinalPrice = $rp['price'] - ($rp['price'] * ($rp['discount'] / 100));
          $rpIsStock = $rp['quantity'] > 0;
          $rpExpiryInfo = calculateExpiry($rp['expiration_date']);
      ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <div class="product-card">
            <div class="product-card-img-wrap">
              <span class="product-card-badge <?php echo $rpIsStock ? '' : 'out-of-stock'; ?>">
                <?php echo $rpIsStock ? 'In Stock' : 'Out of Stock'; ?>
              </span>
              <img src="<?php echo esc($rp['image']); ?>" alt="<?php echo esc($rp['name']); ?>">
            </div>
            <div class="product-card-body">
              <h5 class="product-card-title"><?php echo esc($rp['name']); ?></h5>
              <div class="product-card-price-wrap">
                <span class="product-card-price">$<?php echo number_format($rpFinalPrice, 2); ?></span>
                <?php if ($rpHasDiscount): ?>
                  <span class="product-card-price-old">$<?php echo number_format($rp['price'], 2); ?></span>
                  <span class="badge-discount-custom">-<?php echo $rp['discount']; ?>%</span>
                <?php endif; ?>
              </div>
              <div class="stock-status">
                <i class="bi <?php echo $rpExpiryInfo['icon']; ?> <?php echo $rpExpiryInfo['class']; ?> me-1"></i>
                <span class="<?php echo $rpExpiryInfo['class']; ?>"><?php echo esc($rpExpiryInfo['text']); ?></span>
              </div>
              <div class="product-card-actions">
                <a href="product-details.php?id=<?php echo $rp['id']; ?>" class="btn-card-view">Details</a>
                <?php if ($rpIsStock): ?>
                  <form action="cart/add.php" method="POST" class="m-0 d-inline">
                    <input type="hidden" name="product_id" value="<?php echo $rp['id']; ?>">
                    <button type="submit" class="btn-card-add w-100">Add to Cart</button>
                  </form>
                <?php else: ?>
                  <button type="button" class="btn-card-add w-100" disabled>Out of Stock</button>
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
