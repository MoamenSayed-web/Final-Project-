<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$conn = getDatabaseConnection();

// Fetch customer directories with aggregates
$query = "SELECT u.*, 
                 (SELECT COUNT(*) FROM `order` o WHERE o.user_id = u.id) as orders_count,
                 (SELECT SUM(total_price) FROM `order` o WHERE o.user_id = u.id) as total_spending,
                 (SELECT phone FROM `user_phones` up WHERE up.user_id = u.id LIMIT 1) as phone,
                 (SELECT address FROM `user_addresses` ua WHERE ua.user_id = u.id LIMIT 1) as address
          FROM `user` u 
          WHERE u.role = 'customer' 
          ORDER BY u.id DESC";

$result = $conn->query($query);
$customers = [];
while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}

$pageTitle = "Customers Directory - Fresh Rescue";
$pathPrefix = '../';
$activeSidebar = 'customers';

include __DIR__ . '/../includes/head.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 fw-bold text-dark">Customers Directory</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <span class="badge bg-success px-3 py-2 fs-7 rounded-pill"><i class="bi bi-people me-1"></i> Customer Accounts List</span>
        </div>
      </div>

      <!-- Customers Database Table -->
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0 bg-white">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-8">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">Profile</th>
                  <th>Customer Email</th>
                  <th>Phone Number</th>
                  <th>Delivery Address</th>
                  <th class="text-center">Orders Count</th>
                  <th class="text-end">Total Spending</th>
                  <th class="ps-4">Joined Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($customers)): ?>
                  <tr><td colspan="7" class="text-center text-muted py-4">No customer accounts registered yet.</td></tr>
                <?php else: ?>
                  <?php foreach ($customers as $c): 
                      $avatarName = urlencode($c['name']);
                      $spending = $c['total_spending'] ?? 0.0;
                      $phone = $c['phone'] ?? 'N/A';
                      $address = $c['address'] ?? 'N/A';
                  ?>
                    <tr>
                      <td class="ps-4">
                        <div class="d-flex align-items-center gap-2">
                          <img src="https://ui-avatars.com/api/?name=<?php echo $avatarName; ?>&background=198754&color=fff&size=32" alt="<?php echo esc($c['name']); ?> avatar" class="rounded-circle border">
                          <span class="fw-bold text-dark"><?php echo esc($c['name']); ?></span>
                        </div>
                      </td>
                      <td><?php echo esc($c['email']); ?></td>
                      <td class="fw-semibold text-secondary"><?php echo esc($phone); ?></td>
                      <td class="text-muted"><?php echo esc($address); ?></td>
                      <td class="text-center fw-bold"><?php echo $c['orders_count']; ?></td>
                      <td class="text-end fw-bold text-success">$<?php echo number_format($spending, 2); ?></td>
                      <td class="ps-4"><?php echo date('Y-m-d', strtotime($c['created_at'])); ?></td>
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
