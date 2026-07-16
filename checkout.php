<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$conn = getDatabaseConnection();

$cartIds = $_SESSION['cart'] ?? [];
if (empty($cartIds)) {
    header('Location: cart.php');
    exit();
}

$cartProducts = [];
$cartSubtotal = 0.0;
$deliveryFee = 2.00;

// Fetch cart items for rendering
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

$rescuedTotal = $cartSubtotal + $deliveryFee;

// Process Order Placement on POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? 'cod';
    
    if (!empty($name) && !empty($phone) && !empty($address) && !empty($cartProducts)) {
        // Begin Transaction
        $conn->begin_transaction();
        try {
            // Insert Payment
            $payStatus = ($paymentMethod === 'cod') ? 'Pending' : 'Completed';
            $paidAt = ($paymentMethod === 'cod') ? null : date('Y-m-d H:i:s');
            
            $payStmt = $conn->prepare("INSERT INTO `payment` (`method`, `status`, `paid_at`) VALUES (?, ?, ?)");
            $payStmt->bind_param("sss", $paymentMethod, $payStatus, $paidAt);
            $payStmt->execute();
            $paymentId = $conn->insert_id;
            $payStmt->close();
            
            // Insert Order
            $userId = $_SESSION['user_id'];
            $orderStatus = 'Pending';
            
            $orderStmt = $conn->prepare("INSERT INTO `order` (`name`, `status`, `total_price`, `user_id`, `payment_id`) VALUES (?, ?, ?, ?, ?)");
            $orderStmt->bind_param("ssdii", $name, $orderStatus, $rescuedTotal, $userId, $paymentId);
            $orderStmt->execute();
            $orderId = $conn->insert_id;
            $orderStmt->close();
            
            // Insert Order Items
            $itemStmt = $conn->prepare("INSERT INTO `order_item` (`id`, `order_id`, `product_id`, `quantity`, `price_at_purchase`) VALUES (?, ?, ?, ?, ?)");
            $itemId = 1;
            foreach ($cartProducts as $cp) {
                $itemStmt->bind_param("iiiid", $itemId, $orderId, $cp['id'], $cp['quantity'], $cp['final_price']);
                $itemStmt->execute();
                $itemId++;
            }
            $itemStmt->close();
            
            $conn->commit();
            
            // Clear cart
            unset($_SESSION['cart']);
            
            header('Location: orders.php');
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $errorMsg = "Order placement failed. Please try again: " . $e->getMessage();
        }
    } else {
        $errorMsg = "All checkout fields are required.";
    }
}

$pageTitle = "Secure Checkout - Fresh Rescue";
$pathPrefix = '';
$activePage = 'cart';

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="py-5">
  <div class="container py-4">
    <h2 class="fw-bold mb-4 text-primary-custom">Secure Checkout</h2>

    <?php if (isset($errorMsg)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo htmlspecialchars($errorMsg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <form action="checkout.php" method="POST" class="needs-validation" novalidate>
      <div class="row g-4">

        <div class="col-lg-8">
          <div class="checkout-panel shadow-sm mb-4 bg-white border p-4 rounded-3">
            <h5 class="fw-bold mb-4"><i class="bi bi-geo-alt me-2 text-success"></i>Shipping Information</h5>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="shippingName" class="form-label fw-semibold">Full Name</label>
                <input type="text" class="form-control rounded-custom" id="shippingName" name="name" placeholder="Johnathan Doe" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" required>
                <div class="invalid-feedback">Full name is required.</div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="shippingPhone" class="form-label fw-semibold">Phone Number</label>
                <input type="tel" class="form-control rounded-custom" id="shippingPhone" name="phone" placeholder="+1 (555) 345-6789" required>
                <div class="invalid-feedback">Phone number is required.</div>
              </div>
            </div>

            <div class="mb-3">
              <label for="shippingAddress" class="form-label fw-semibold">Delivery Address</label>
              <input type="text" class="form-control rounded-custom" id="shippingAddress" name="address" placeholder="123 Eco Green Boulevard, Sustainable City" required>
              <div class="invalid-feedback">Delivery address is required.</div>
            </div>
          </div>

          <div class="checkout-panel shadow-sm bg-white border p-4 rounded-3">
            <h5 class="fw-bold mb-4"><i class="bi bi-credit-card me-2 text-success"></i>Payment Method</h5>

            <div class="form-check mb-3">
              <input class="form-check-input" type="radio" name="payment_method" id="payCredit" value="credit_card" checked>
              <label class="form-check-label fw-semibold text-dark" for="payCredit">
                Credit / Debit Card
              </label>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="radio" name="payment_method" id="payWallet" value="wallet">
              <label class="form-check-label fw-semibold text-dark" for="payWallet">
                Eco Wallet Balance
              </label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method" id="payCod" value="cod">
              <label class="form-check-label fw-semibold text-dark" for="payCod">
                Cash on Delivery
              </label>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="cart-summary shadow-sm p-4 bg-white border rounded-3">
            <h5 class="fw-bold mb-3">Order Summary</h5>

            <?php foreach ($cartProducts as $cp): ?>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <span class="d-block fw-bold text-dark fs-8"><?php echo esc($cp['name']); ?></span>
                  <small class="text-muted">Quantity: <?php echo $cp['quantity']; ?></small>
                </div>
                <span class="fw-bold text-success fs-8">$<?php echo number_format($cp['subtotal'], 2); ?></span>
              </div>
            <?php endforeach; ?>

            <div class="d-flex justify-content-between fs-8 text-muted mb-2">
              <span>Subtotal:</span>
              <span class="fw-bold text-dark">$<?php echo number_format($cartSubtotal, 2); ?></span>
            </div>
            <div class="d-flex justify-content-between fs-8 text-muted mb-3">
              <span>Delivery Fee:</span>
              <span class="fw-bold text-dark">$<?php echo number_format($deliveryFee, 2); ?></span>
            </div>
            <hr class="my-3">

            <div class="d-flex justify-content-between align-items-baseline mb-4">
              <span class="fw-bold text-dark">Rescued Total:</span>
              <span class="fs-4 fw-extrabold text-success fw-bold">$<?php echo number_format($rescuedTotal, 2); ?></span>
            </div>

            <button type="submit" class="btn btn-success w-100 py-3 fw-bold bg-primary-custom rounded-custom">
              Place Order
            </button>
          </div>
        </div>

      </div>
    </form>
  </div>
</section>

<script>
  (function () {
    'use strict'
    const forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
      .forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
  })()
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
