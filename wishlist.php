<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$conn = getDatabaseConnection();

$wishlistIds = $_SESSION['wishlist'] ?? [];
$wishlistProducts = [];

if (!empty($wishlistIds)) {
    $inQuery = implode(',', array_fill(0, count($wishlistIds), '?'));
    $types = str_repeat('i', count($wishlistIds));
    
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM `product` p LEFT JOIN `category` c ON p.category_id = c.id WHERE p.id IN ($inQuery) AND p.status = 'active'");
    $stmt->bind_param($types, ...$wishlistIds);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $hasDiscount = $row['discount'] > 0;
        $finalPrice = $row['price'] - ($row['price'] * ($row['discount'] / 100));
        $row['final_price'] = $finalPrice;
        $row['has_discount'] = $hasDiscount;
        $row['is_stock'] = $row['quantity'] > 0;
        $row['expiry_info'] = calculateExpiry($row['expiration_date']);
        
        $wishlistProducts[] = $row;
    }
    $stmt->close();
}

$pageTitle = "My Wishlist - Fresh Rescue";
$pathPrefix = '';
$activePage = 'wishlist';

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="py-5">
  <div class="container py-4">
    <h2 class="fw-bold mb-4 text-primary-custom">My Wishlist</h2>

    <div class="row g-4">
      <?php if (empty($wishlistProducts)): ?>
        <div class="col-12 text-center py-5">
          <i class="bi bi-heart fs-1 text-muted d-block mb-3"></i>
          <h4 class="fw-bold text-dark">Your Wishlist is Empty</h4>
          <p class="text-muted">Save your favorite groceries to rescue later!</p>
          <a href="products.php" class="btn btn-success bg-primary-custom rounded-custom px-4 py-2 mt-2">Browse Products</a>
        </div>
      <?php else: ?>
        <div class="col-12">
          <div class="bg-white border rounded-3 p-4 shadow-sm">
            <div class="table-responsive">
              <table class="table align-middle cart-table mb-0">
                <thead>
                  <tr>
                    <th scope="col" class="border-top-none">Product</th>
                    <th scope="col" class="border-top-none">Price</th>
                    <th scope="col" class="border-top-none">Rescue Status</th>
                    <th scope="col" class="text-center border-top-none">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($wishlistProducts as $wp): ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center gap-3">
                          <img src="<?php echo esc($wp['image']); ?>" alt="<?php echo esc($wp['name']); ?>" class="cart-img rounded">
                          <div>
                            <span class="d-block fw-bold text-dark fs-8"><?php echo esc($wp['name']); ?></span>
                            <small class="text-muted"><?php echo esc($wp['category_name']); ?></small>
                          </div>
                        </div>
                      </td>
                      <td class="fw-semibold">
                        <span class="text-success">$<?php echo number_format($wp['final_price'], 2); ?></span>
                        <?php if ($wp['has_discount']): ?>
                          <small class="text-muted text-decoration-line-through ms-2">$<?php echo number_format($wp['price'], 2); ?></small>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="<?php echo $wp['expiry_info']['class']; ?> fw-bold fs-8">
                          <i class="bi <?php echo $wp['expiry_info']['icon']; ?> me-1"></i><?php echo esc($wp['expiry_info']['text']); ?>
                        </span>
                      </td>
                      <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center gap-2">
                          <?php if ($wp['is_stock']): ?>
                            <form action="cart/add.php" method="POST" class="m-0">
                              <input type="hidden" name="product_id" value="<?php echo $wp['id']; ?>">
                              <button type="submit" class="btn btn-success btn-sm bg-primary-custom rounded-custom px-3">Add to Cart</button>
                            </form>
                          <?php else: ?>
                            <button type="button" class="btn btn-secondary btn-sm rounded-custom px-3" disabled>Out of Stock</button>
                          <?php endif; ?>
                          <form action="wishlist/process.php" method="POST" class="m-0">
                            <input type="hidden" name="product_id" value="<?php echo $wp['id']; ?>">
                            <input type="hidden" name="action" value="remove">
                            <button type="submit" class="btn btn-light btn-sm border text-danger rounded-custom" title="Remove">
                              <i class="bi bi-trash-fill"></i>
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
