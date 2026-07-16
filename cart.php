<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$conn = getDatabaseConnection();

$cartIds = $_SESSION['cart'] ?? [];
$cartProducts = [];
$cartSubtotal = 0.0;
$deliveryFee = 2.00;

if (!empty($cartIds)) {
    $productIds = array_keys($cartIds);
    $inQuery = implode(',', array_fill(0, count($productIds), '?'));
    $types = str_repeat('i', count($productIds));
    
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM `product` p LEFT JOIN `category` c ON p.category_id = c.id WHERE p.id IN ($inQuery) AND p.status = 'active'");
    $stmt->bind_param($types, ...$productIds);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $qty = (int)$cartIds[$row['id']];
        $finalPrice = $row['price'] - ($row['price'] * ($row['discount'] / 100));
        $row['quantity'] = $qty;
        $row['final_price'] = $finalPrice;
        $row['subtotal'] = $finalPrice * $qty;
        
        $cartSubtotal += $row['subtotal'];
        $cartProducts[] = $row;
    }
    $stmt->close();
}

$rescuedTotal = $cartSubtotal > 0 ? ($cartSubtotal + $deliveryFee) : 0.0;

$pageTitle = "Your Shopping Cart - Fresh Rescue";
$pathPrefix = '';
$activePage = 'cart';

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="py-5">
  <div class="container py-4">
    <h2 class="fw-bold mb-4 text-primary-custom">Your Shopping Cart</h2>

    <div class="row g-4">
      <?php if (empty($cartProducts)): ?>
        <div class="col-12 text-center py-5">
          <i class="bi bi-cart-x fs-1 text-muted d-block mb-3"></i>
          <h4 class="fw-bold text-dark">Your Shopping Cart is Empty</h4>
          <p class="text-muted">Rescue delicious meals and groceries by adding items to your cart!</p>
          <a href="products.php" class="btn btn-success bg-primary-custom rounded-custom px-4 py-2 mt-2">Browse Products</a>
        </div>
      <?php else: ?>
        <div class="col-lg-8">
          <div class="bg-white border rounded-3 p-4 shadow-sm mb-4">
            <div class="table-responsive">
              <table class="table align-middle cart-table mb-0">
                <thead>
                  <tr>
                    <th scope="col" class="border-top-none">Product</th>
                    <th scope="col" class="border-top-none">Price</th>
                    <th scope="col" class="text-center border-top-none">Quantity</th>
                    <th scope="col" class="text-end border-top-none">Subtotal</th>
                    <th scope="col" class="text-center border-top-none">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($cartProducts as $cp): ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center gap-3">
                          <img src="<?php echo esc($cp['image']); ?>" alt="<?php echo esc($cp['name']); ?>" class="cart-img rounded">
                          <div>
                            <span class="d-block fw-bold text-dark fs-8"><?php echo esc($cp['name']); ?></span>
                            <small class="text-muted"><?php echo esc($cp['category_name']); ?></small>
                          </div>
                        </div>
                      </td>
                      <td class="fw-semibold">$<?php echo number_format($cp['final_price'], 2); ?></td>
                      <td class="text-center">
                        <form action="cart/update.php" method="POST" class="m-0 d-inline-block">
                          <input type="hidden" name="product_id" value="<?php echo $cp['id']; ?>">
                          <div class="input-group input-group-sm details-qty-wrap mx-auto" style="max-width: 110px;">
                            <input type="number" name="quantity" class="form-control text-center bg-light border-0 fw-bold" value="<?php echo $cp['quantity']; ?>" min="1" max="<?php echo $cp['quantity']; ?>" onchange="this.form.submit()">
                          </div>
                        </form>
                      </td>
                      <td class="text-end fw-bold text-success">$<?php echo number_format($cp['subtotal'], 2); ?></td>
                      <td class="text-center">
                        <form action="cart/remove.php" method="POST" class="m-0 d-inline">
                          <input type="hidden" name="product_id" value="<?php echo $cp['id']; ?>">
                          <button type="submit" class="btn btn-link text-danger p-0" title="Remove Item">
                            <i class="bi bi-trash-fill fs-5"></i>
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="cart-summary shadow-sm p-4 bg-white border rounded-3">
            <h5 class="fw-bold mb-4">Summary</h5>
            <div class="d-flex justify-content-between mb-3 fs-8 text-muted">
              <span>Subtotal:</span>
              <span class="fw-bold text-dark">$<?php echo number_format($cartSubtotal, 2); ?></span>
            </div>
            <div class="d-flex justify-content-between mb-3 fs-8 text-muted">
              <span>Estimated Delivery Fee:</span>
              <span class="fw-bold text-dark">$<?php echo number_format($deliveryFee, 2); ?></span>
            </div>
            <hr class="my-3">
            <div class="d-flex justify-content-between mb-4 fs-6 fw-extrabold text-dark fw-bold">
              <span>Rescued Total:</span>
              <span class="text-success">$<?php echo number_format($rescuedTotal, 2); ?></span>
            </div>
            <a href="checkout.php" class="btn btn-success w-100 py-3 fw-bold bg-primary-custom rounded-custom">
              Proceed to Checkout <i class="bi bi-arrow-right ms-2"></i>
            </a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
