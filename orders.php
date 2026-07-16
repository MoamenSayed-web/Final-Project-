<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$conn = getDatabaseConnection();
$userId = $_SESSION['user_id'];

$userOrders = [];
$selectedOrder = null;
$selectedOrderItems = [];

// Fetch user orders
$stmt = $conn->prepare("SELECT * FROM `order` WHERE `user_id` = ? ORDER BY `id` DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $userOrders[] = $row;
}
$stmt->close();

// Fetch single order details if requested
if (isset($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    $orderStmt = $conn->prepare("SELECT o.*, p.method as payment_method, p.status as payment_status 
                                 FROM `order` o 
                                 LEFT JOIN `payment` p ON o.payment_id = p.id 
                                 WHERE o.id = ? AND o.user_id = ?");
    $orderStmt->bind_param("ii", $orderId, $userId);
    $orderStmt->execute();
    $selectedOrder = $orderStmt->get_result()->fetch_assoc();
    $orderStmt->close();

    if ($selectedOrder) {
        $itemsStmt = $conn->prepare("SELECT oi.*, p.name as product_name 
                                     FROM `order_item` oi 
                                     LEFT JOIN `product` p ON oi.product_id = p.id 
                                     WHERE oi.order_id = ?");
        $itemsStmt->bind_param("i", $orderId);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        while ($row = $itemsResult->fetch_assoc()) {
            $selectedOrderItems[] = $row;
        }
        $itemsStmt->close();
    }
}

$pageTitle = "My Orders - Fresh Rescue";
$pathPrefix = '';
$activePage = 'orders';

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="py-5">
  <div class="container py-4">
    <h2 class="fw-bold mb-4 text-primary-custom">My Order History</h2>

    <div class="row g-4">
      <div class="<?php echo $selectedOrder ? 'col-lg-7' : 'col-lg-10 mx-auto'; ?>">
        <div class="bg-white border rounded-3 p-4 shadow-sm">
          <div class="table-responsive">
            <table class="table align-middle cart-table mb-0">
              <thead>
                <tr>
                  <th scope="col" class="border-top-none">Order #</th>
                  <th scope="col" class="border-top-none">Rescue Status</th>
                  <th scope="col" class="text-end border-top-none">Rescued Total</th>
                  <th scope="col" class="text-center border-top-none">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($userOrders)): ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                      <i class="bi bi-receipt fs-3 d-block mb-2 text-success"></i>
                      No recent orders found.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($userOrders as $order): 
                      $status = strtolower($order['status']);
                      $badgeClass = 'bg-warning text-dark';
                      if ($status === 'delivered' || $status === 'completed') {
                          $badgeClass = 'bg-success bg-opacity-10 text-success';
                      } elseif ($status === 'pending' || $status === 'processing') {
                          $badgeClass = 'bg-info bg-opacity-10 text-info';
                      } elseif ($status === 'cancelled') {
                          $badgeClass = 'bg-danger bg-opacity-10 text-danger';
                      }
                  ?>
                    <tr>
                      <td class="fw-bold text-dark">#FR-<?php echo htmlspecialchars($order['id']); ?></td>
                      <td>
                        <span class="badge <?php echo $badgeClass; ?> px-2.5 py-1 rounded fs-badge">
                          <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                        </span>
                      </td>
                      <td class="text-end fw-bold text-success">$<?php echo number_format($order['total_price'], 2); ?></td>
                      <td class="text-center">
                        <a href="orders.php?id=<?php echo htmlspecialchars($order['id']); ?>" class="btn btn-light btn-sm border fs-8 fw-semibold rounded-custom">View Details</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <?php if ($selectedOrder): ?>
        <div class="col-lg-5">
          <div class="bg-white border rounded-3 p-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4 class="fw-bold m-0 text-success">Order #FR-<?php echo htmlspecialchars($selectedOrder['id']); ?></h4>
              <a href="orders.php" class="btn-close" aria-label="Close"></a>
            </div>
            
            <div class="mb-3">
              <span class="d-block text-muted mb-1 fs-8">Customer Name</span>
              <span class="fw-semibold text-dark"><?php echo htmlspecialchars($selectedOrder['name']); ?></span>
            </div>

            <div class="mb-3">
              <span class="d-block text-muted mb-1 fs-8">Status</span>
              <span class="badge bg-info bg-opacity-10 text-info px-2.5 py-1 rounded">
                <?php echo htmlspecialchars(ucfirst($selectedOrder['status'])); ?>
              </span>
            </div>

            <div class="mb-4">
              <span class="d-block text-muted mb-1 fs-8">Payment Method</span>
              <span class="fw-semibold text-dark"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $selectedOrder['payment_method']))); ?> (<?php echo htmlspecialchars($selectedOrder['payment_status']); ?>)</span>
            </div>

            <h5 class="fw-bold mb-3 border-bottom pb-2">Items</h5>
            <div class="mb-4">
              <?php foreach ($selectedOrderItems as $item): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div>
                    <span class="fw-semibold text-dark d-block fs-8"><?php echo htmlspecialchars($item['product_name'] ?? 'Unknown Product'); ?></span>
                    <small class="text-muted">Qty: <?php echo htmlspecialchars($item['quantity']); ?> @ $<?php echo number_format($item['price_at_purchase'], 2); ?></small>
                  </div>
                  <span class="fw-bold text-success">$<?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?></span>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="d-flex justify-content-between align-items-center border-top pt-3">
              <span class="fw-bold text-dark fs-5">Rescued Total:</span>
              <span class="fw-extrabold text-success fs-4 fw-bold">$<?php echo number_format($selectedOrder['total_price'], 2); ?></span>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
