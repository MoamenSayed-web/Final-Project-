<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$conn = getDatabaseConnection();
$errorMsg = '';
$successMsg = '';

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['status'] ?? '';
    $paymentStatus = $_POST['payment_status'] ?? '';

    if ($orderId > 0 && !empty($newStatus)) {
        $conn->begin_transaction();
        try {
            // Update order status
            $stmt = $conn->prepare("UPDATE `order` SET `status` = ? WHERE `id` = ?");
            $stmt->bind_param("si", $newStatus, $orderId);
            $stmt->execute();
            $stmt->close();

            // Update payment status if selected
            if (!empty($paymentStatus)) {
                $payStmt = $conn->prepare("UPDATE `payment` p JOIN `order` o ON o.payment_id = p.id SET p.status = ? WHERE o.id = ?");
                $payStmt->bind_param("si", $paymentStatus, $orderId);
                $payStmt->execute();
                $payStmt->close();
            }

            $conn->commit();
            $successMsg = "Order status updated successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $errorMsg = "Failed to update order status: " . $e->getMessage();
        }
    }
}

// Fetch all orders
$result = $conn->query("SELECT o.*, p.method as payment_method, p.status as payment_status FROM `order` o LEFT JOIN `payment` p ON o.payment_id = p.id ORDER BY o.id DESC");
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// Fetch single order details if requested
$selectedOrder = null;
$selectedOrderItems = [];
if (isset($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT o.*, p.method as payment_method, p.status as payment_status FROM `order` o LEFT JOIN `payment` p ON o.payment_id = p.id WHERE o.id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $selectedOrder = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($selectedOrder) {
        $stmt = $conn->prepare("SELECT oi.*, p.name as product_name FROM `order_item` oi LEFT JOIN `product` p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $itemsResult = $stmt->get_result();
        while ($row = $itemsResult->fetch_assoc()) {
            $selectedOrderItems[] = $row;
        }
        $stmt->close();
    }
}

$pageTitle = "Orders Management - Fresh Rescue";
$pathPrefix = '../';
$activeSidebar = 'orders';

include __DIR__ . '/../includes/head.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 fw-bold text-dark">Orders Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <span class="badge bg-success px-3 py-2 fs-7 rounded-pill"><i class="bi bi-receipt me-1"></i> Customer Orders Log</span>
        </div>
      </div>

      <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <?php echo htmlspecialchars($errorMsg); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>
          <?php echo htmlspecialchars($successMsg); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <div class="row g-4">
        <!-- Orders List -->
        <div class="<?php echo $selectedOrder ? 'col-lg-7' : 'col-lg-12'; ?>">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0 bg-white">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-8">
                  <thead class="table-light">
                    <tr>
                      <th class="ps-4">Order ID</th>
                      <th>Customer</th>
                      <th>Status</th>
                      <th>Payment</th>
                      <th class="text-end">Total</th>
                      <th>Created At</th>
                      <th class="text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($orders)): ?>
                      <tr><td colspan="7" class="text-center text-muted py-4">No customer orders recorded yet.</td></tr>
                    <?php else: ?>
                      <?php foreach ($orders as $ord): 
                          $status = strtolower($ord['status']);
                          $badge = 'bg-warning text-dark';
                          if ($status === 'delivered' || $status === 'completed') $badge = 'bg-success text-white';
                          elseif ($status === 'cancelled') $badge = 'bg-danger text-white';
                      ?>
                        <tr>
                          <td class="ps-4 fw-bold text-dark">#FR-<?php echo $ord['id']; ?></td>
                          <td><?php echo esc($ord['name']); ?></td>
                          <td><span class="badge <?php echo $badge; ?>"><?php echo esc(ucfirst($ord['status'])); ?></span></td>
                          <td><?php echo esc(ucfirst($ord['payment_method'])); ?> (<?php echo esc($ord['payment_status']); ?>)</td>
                          <td class="text-end fw-bold text-success">$<?php echo number_format($ord['total_price'], 2); ?></td>
                          <td><?php echo esc($ord['created_at']); ?></td>
                          <td class="text-center">
                            <a href="orders.php?id=<?php echo $ord['id']; ?>" class="btn btn-outline-success btn-sm py-1 px-2 rounded-custom fs-9"><i class="bi bi-eye-fill"></i> View Details</a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Selected Order Details & Status Updater -->
        <?php if ($selectedOrder): ?>
          <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3">
              <div class="card-header bg-success text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text me-2"></i> Order #FR-<?php echo $selectedOrder['id']; ?> Details</span>
                <a href="orders.php" class="btn-close btn-close-white" aria-label="Close"></a>
              </div>
              <div class="card-body p-4 bg-white fs-8">
                <!-- Status Updater Form -->
                <form action="orders.php?id=<?php echo $selectedOrder['id']; ?>" method="POST" class="mb-4 border-bottom pb-3">
                  <input type="hidden" name="update_status" value="1">
                  <input type="hidden" name="order_id" value="<?php echo $selectedOrder['id']; ?>">
                  
                  <div class="row g-2 align-items-end">
                    <div class="col-6">
                      <label class="form-label fw-bold mb-1">Order Status</label>
                      <select class="form-select form-select-sm rounded-custom" name="status">
                        <option value="Pending" <?php echo $selectedOrder['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Processing" <?php echo $selectedOrder['status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="Completed" <?php echo $selectedOrder['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Delivered" <?php echo $selectedOrder['status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="Cancelled" <?php echo $selectedOrder['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                      </select>
                    </div>
                    <div class="col-6">
                      <label class="form-label fw-bold mb-1">Payment Status</label>
                      <select class="form-select form-select-sm rounded-custom" name="payment_status">
                        <option value="Pending" <?php echo $selectedOrder['payment_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Completed" <?php echo $selectedOrder['payment_status'] === 'Completed' ? 'selected' : ''; ?>>Completed (Paid)</option>
                      </select>
                    </div>
                    <div class="col-12 d-grid mt-2">
                      <button type="submit" class="btn btn-success btn-sm rounded-custom fw-bold">Update Statuses</button>
                    </div>
                  </div>
                </form>

                <div class="mb-3">
                  <span class="d-block text-muted mb-1">Recipient Customer</span>
                  <span class="fw-bold text-dark fs-7"><?php echo esc($selectedOrder['name']); ?></span>
                </div>

                <div class="mb-3">
                  <span class="d-block text-muted mb-1">Payment Reference</span>
                  <span class="fw-bold text-dark"><?php echo esc(ucfirst(str_replace('_', ' ', $selectedOrder['payment_method']))); ?> (<?php echo esc($selectedOrder['payment_status']); ?>)</span>
                </div>

                <div class="mb-3">
                  <span class="d-block text-muted mb-1">Placed At</span>
                  <span class="fw-semibold text-dark"><?php echo esc($selectedOrder['created_at']); ?></span>
                </div>

                <h5 class="fw-bold mb-3 border-bottom pb-2 mt-4">Order Items</h5>
                <div class="mb-4">
                  <?php foreach ($selectedOrderItems as $item): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <div>
                        <span class="fw-semibold text-dark d-block"><?php echo esc($item['product_name'] ?? 'Deleted Product'); ?></span>
                        <small class="text-muted">Qty: <?php echo $item['quantity']; ?> @ $<?php echo number_format($item['price_at_purchase'], 2); ?></small>
                      </div>
                      <span class="fw-bold text-success">$<?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                  <span class="fw-bold text-dark fs-6">Rescued Total:</span>
                  <span class="fw-extrabold text-success fs-5 fw-bold">$<?php echo number_format($selectedOrder['total_price'], 2); ?></span>
                </div>

                <div class="d-grid mt-4">
                  <button type="button" class="btn btn-outline-dark btn-sm rounded-custom" onclick="window.print()"><i class="bi bi-printer"></i> Print Invoice / Bill</button>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>

    </main>
  </div>
</div>
</body>
</html>
