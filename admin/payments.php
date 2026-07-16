<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$conn = getDatabaseConnection();

// Fetch payment transactions logs
$query = "SELECT p.*, o.id as order_id, o.name as customer_name, o.total_price as amount
          FROM `payment` p
          LEFT JOIN `order` o ON o.payment_id = p.id
          ORDER BY p.id DESC";

$result = $conn->query($query);
$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

$pageTitle = "Payment Transactions Log - Fresh Rescue";
$pathPrefix = '../';
$activeSidebar = 'payments';

include __DIR__ . '/../includes/head.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 fw-bold text-dark">Payment Transactions Log</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <span class="badge bg-success px-3 py-2 fs-7 rounded-pill"><i class="bi bi-shield-check me-1"></i> Transaction Security Logs</span>
        </div>
      </div>

      <!-- Payment Log Table -->
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0 bg-white">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-8">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">Transaction ID</th>
                  <th>Order Reference</th>
                  <th>Customer</th>
                  <th>Payment Method</th>
                  <th>Status</th>
                  <th class="text-end">Amount</th>
                  <th>Paid Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($payments)): ?>
                  <tr><td colspan="7" class="text-center text-muted py-4">No payment transactions recorded yet.</td></tr>
                <?php else: ?>
                  <?php foreach ($payments as $p): 
                      $status = strtolower($p['status']);
                      $badge = 'bg-warning text-dark';
                      if ($status === 'completed' || $status === 'paid' || $status === 'success') {
                          $badge = 'bg-success bg-opacity-10 text-success';
                      } elseif ($status === 'pending') {
                          $badge = 'bg-info bg-opacity-10 text-info';
                      } elseif ($status === 'failed') {
                          $badge = 'bg-danger bg-opacity-10 text-danger';
                      }
                      $paidDate = $p['paid_at'] ?? 'N/A (Pending)';
                  ?>
                    <tr>
                      <td class="ps-4 fw-bold text-dark">#TXN-<?php echo $p['id']; ?></td>
                      <td>
                        <a href="orders.php?id=<?php echo $p['order_id']; ?>" class="fw-bold text-success text-decoration-none">
                          #FR-<?php echo $p['order_id']; ?>
                        </a>
                      </td>
                      <td><?php echo esc($p['customer_name'] ?? 'N/A'); ?></td>
                      <td><?php echo esc(ucfirst(str_replace('_', ' ', $p['method']))); ?></td>
                      <td>
                        <span class="badge <?php echo $badge; ?> px-2.5 py-1 rounded">
                          <?php echo esc(ucfirst($p['status'])); ?>
                        </span>
                      </td>
                      <td class="text-end fw-bold text-success">$<?php echo number_format($p['amount'] ?? 0.0, 2); ?></td>
                      <td><?php echo esc($paidDate); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>
</body>
</html>
