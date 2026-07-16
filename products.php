<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = "Shop Products - Fresh Rescue";
$pathPrefix = '';
$activePage = 'products';

$conn = getDatabaseConnection();

$selectedCategory = $_GET['category'] ?? '';
$searchTerm = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? '';

// Build dynamic MySQLi query
$query = "SELECT p.*, c.name as category_name FROM `product` p LEFT JOIN `category` c ON p.category_id = c.id WHERE p.status = 'active'";
$types = "";
$params = [];

if (!empty($selectedCategory)) {
    $query .= " AND LOWER(c.name) = ?";
    $types .= "s";
    $params[] = strtolower($selectedCategory);
}

if (!empty($searchTerm)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $types .= "ss";
    $searchWild = "%" . $searchTerm . "%";
    $params[] = $searchWild;
    $params[] = $searchWild;
}

if ($filter === 'near-expiry') {
    $maxExpiry = date('Y-m-d', strtotime('+3 days'));
    $query .= " AND p.expiration_date <= ?";
    $types .= "s";
    $params[] = $maxExpiry;
}

$query .= " ORDER BY p.id DESC";

$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$filteredProducts = [];
while ($row = $result->fetch_assoc()) {
    $filteredProducts[] = $row;
}
$stmt->close();

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="py-5">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <h2 class="fw-bold m-0 text-primary-custom">Rescue Groceries</h2>

      <div class="d-flex gap-2 align-items-center">
        <form action="products.php" method="GET" class="m-0 d-flex gap-2">
          <?php if (!empty($searchTerm)): ?>
            <input type="hidden" name="search" value="<?php echo esc($searchTerm); ?>">
          <?php endif; ?>
          <?php if (!empty($filter)): ?>
            <input type="hidden" name="filter" value="<?php echo esc($filter); ?>">
          <?php endif; ?>
          <select class="form-select form-select-sm rounded-custom products-filter-select" name="category" onchange="this.form.submit()" title="Select category">
            <option value="" <?php echo $selectedCategory === '' ? 'selected' : ''; ?>>All Categories</option>
            <option value="vegetables" <?php echo strtolower($selectedCategory) === 'vegetables' ? 'selected' : ''; ?>>Vegetables</option>
            <option value="fruits" <?php echo strtolower($selectedCategory) === 'fruits' ? 'selected' : ''; ?>>Fruits</option>
            <option value="bakery" <?php echo strtolower($selectedCategory) === 'bakery' ? 'selected' : ''; ?>>Bakery</option>
            <option value="dairy" <?php echo strtolower($selectedCategory) === 'dairy' ? 'selected' : ''; ?>>Dairy</option>
          </select>
        </form>
      </div>
    </div>

    <div class="row g-4">
      <?php if (empty($filteredProducts)): ?>
        <div class="col-12 text-center py-5">
          <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
          <h4 class="fw-bold text-dark">No products found</h4>
          <p class="text-muted">We couldn't find any products matching your filters.</p>
          <a href="products.php" class="btn btn-success bg-primary-custom rounded-custom px-4 py-2 mt-2">View All Products</a>
        </div>
      <?php else: ?>
        <?php foreach ($filteredProducts as $p): 
            $hasDiscount = $p['discount'] > 0;
            $finalPrice = $p['price'] - ($p['price'] * ($p['discount'] / 100));
            $isStock = $p['quantity'] > 0;
            $expiryInfo = calculateExpiry($p['expiration_date']);
        ?>
          <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="product-card">
              <div class="product-card-img-wrap">
                <span class="product-card-badge <?php echo $isStock ? '' : 'out-of-stock'; ?>">
                  <?php echo $isStock ? 'In Stock' : 'Out of Stock'; ?>
                </span>
                <img src="<?php echo esc($p['image']); ?>" alt="<?php echo esc($p['name']); ?>">
              </div>
              <div class="product-card-body">
                <h5 class="product-card-title"><?php echo esc($p['name']); ?></h5>

                <div class="product-card-price-wrap">
                  <span class="product-card-price">$<?php echo number_format($finalPrice, 2); ?></span>
                  <?php if ($hasDiscount): ?>
                    <span class="product-card-price-old">$<?php echo number_format($p['price'], 2); ?></span>
                    <span class="badge-discount-custom">-<?php echo $p['discount']; ?>%</span>
                  <?php endif; ?>
                </div>

                <div class="stock-status">
                  <i class="bi <?php echo $expiryInfo['icon']; ?> <?php echo $expiryInfo['class']; ?> me-1"></i>
                  <span class="<?php echo $expiryInfo['class']; ?>"><?php echo esc($expiryInfo['text']); ?></span>
                </div>

                <div class="product-card-actions">
                  <a href="product-details.php?id=<?php echo $p['id']; ?>" class="btn-card-view">Details</a>
                  <?php if ($isStock): ?>
                    <form action="cart/add.php" method="POST" class="m-0 d-inline">
                      <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
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
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
