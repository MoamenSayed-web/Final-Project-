<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$conn = getDatabaseConnection();

$totalProducts = $conn->query("SELECT COUNT(*) FROM `product`")->fetch_row()[0];
$totalCategories = $conn->query("SELECT COUNT(*) FROM `category`")->fetch_row()[0];
$totalUsers = $conn->query("SELECT COUNT(*) FROM `user` WHERE `role` = 'customer'")->fetch_row()[0];

$todayRevenue = $conn->query("SELECT SUM(`total_price`) FROM `order` WHERE DATE(`created_at`) = CURRENT_DATE")->fetch_row()[0] ?? 0.0;
$monthRevenue = $conn->query("SELECT SUM(`total_price`) FROM `order` WHERE MONTH(`created_at`) = MONTH(CURRENT_DATE) AND YEAR(`created_at`) = YEAR(CURRENT_DATE)")->fetch_row()[0] ?? 0.0;

$pendingOrders = $conn->query("SELECT COUNT(*) FROM `order` WHERE `status` = 'Pending'")->fetch_row()[0];
$completedOrders = $conn->query("SELECT COUNT(*) FROM `order` WHERE `status` = 'Completed' OR `status` = 'Delivered'")->fetch_row()[0];
$cancelledOrders = $conn->query("SELECT COUNT(*) FROM `order` WHERE `status` = 'Cancelled'")->fetch_row()[0];

$today = date('Y-m-d');
$nearExpiryDate = date('Y-m-d', strtotime('+3 days'));
$nearExpiryProducts = $conn->query("SELECT COUNT(*) FROM `product` WHERE `expiration_date` >= '$today' AND `expiration_date` <= '$nearExpiryDate' AND `status` = 'active'")->fetch_row()[0];
$expiredProducts = $conn->query("SELECT COUNT(*) FROM `product` WHERE `expiration_date` < '$today'")->fetch_row()[0];
$outOfStockProducts = $conn->query("SELECT COUNT(*) FROM `product` WHERE `quantity` = 0")->fetch_row()[0];

$pageTitle = "Admin Dashboard - Fresh Rescue";
$pathPrefix = '../';
$activeSidebar = 'dashboard';

include __DIR__ . '/../includes/head.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 fw-bold text-dark">Dashboard</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <span class="badge bg-success px-3 py-2 fs-7 rounded-pill"><i class="bi bi-clock me-1"></i> Live Stats</span>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="row g-3 mb-4">
        <!-- Card 1: Today's Revenue -->
        <div class="col-6 col-lg-3">
          <div class="card border-0 shadow-sm rounded-3 bg-success text-white p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <small class="text-white-50 text-uppercase fw-bold fs-9">Today's Revenue</small>
                <h3 class="fw-bold mb-0 mt-1">$<?php echo number_format($todayRevenue, 2); ?></h3>
              </div>
              <i class="bi bi-currency-dollar fs-1 text-white-50"></i>
            </div>
          </div>
        </div>
        <!-- Card 2: Monthly Revenue -->
        <div class="col-6 col-lg-3">
          <div class="card border-0 shadow-sm rounded-3 bg-primary text-white p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <small class="text-white-50 text-uppercase fw-bold fs-9">Monthly Revenue</small>
                <h3 class="fw-bold mb-0 mt-1">$<?php echo number_format($monthRevenue, 2); ?></h3>
              </div>
              <i class="bi bi-wallet2 fs-1 text-white-50"></i>
            </div>
          </div>
        </div>
        <!-- Card 3: Pending Orders -->
        <div class="col-6 col-lg-3">
          <div class="card border-0 shadow-sm rounded-3 bg-warning text-dark p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <small class="text-dark-50 text-uppercase fw-bold fs-9">Pending Orders</small>
                <h3 class="fw-bold mb-0 mt-1"><?php echo $pendingOrders; ?></h3>
              </div>
              <i class="bi bi-hourglass-split fs-1 text-dark-50"></i>
            </div>
          </div>
        </div>
        <!-- Card 4: Near Expiry -->
        <div class="col-6 col-lg-3">
          <div class="card border-0 shadow-sm rounded-3 bg-danger text-white p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <small class="text-white-50 text-uppercase fw-bold fs-9">Near Expiry</small>
                <h3 class="fw-bold mb-0 mt-1"><?php echo $nearExpiryProducts; ?></h3>
              </div>
              <i class="bi bi-exclamation-triangle fs-1 text-white-50"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Summary Cards -->
      <div class="row g-3 mb-4 text-center">
        <div class="col-4 col-md-2">
          <div class="bg-white border rounded p-3 shadow-sm">
            <span class="d-block text-muted fs-9 fw-bold text-uppercase">Total Products</span>
            <span class="fs-4 fw-bold text-dark"><?php echo $totalProducts; ?></span>
          </div>
        </div>
        <div class="col-4 col-md-2">
          <div class="bg-white border rounded p-3 shadow-sm">
            <span class="d-block text-muted fs-9 fw-bold text-uppercase">Categories</span>
            <span class="fs-4 fw-bold text-dark"><?php echo $totalCategories; ?></span>
          </div>
        </div>
        <div class="col-4 col-md-2">
          <div class="bg-white border rounded p-3 shadow-sm">
            <span class="d-block text-muted fs-9 fw-bold text-uppercase">Customers</span>
            <span class="fs-4 fw-bold text-dark"><?php echo $totalUsers; ?></span>
          </div>
        </div>
        <div class="col-4 col-md-2">
          <div class="bg-white border rounded p-3 shadow-sm">
            <span class="d-block text-muted fs-9 fw-bold text-uppercase">Completed</span>
            <span class="fs-4 fw-bold text-success"><?php echo $completedOrders; ?></span>
          </div>
        </div>
        <div class="col-4 col-md-2">
          <div class="bg-white border rounded p-3 shadow-sm">
            <span class="d-block text-muted fs-9 fw-bold text-uppercase">Expired</span>
            <span class="fs-4 fw-bold text-danger"><?php echo $expiredProducts; ?></span>
          </div>
        </div>
        <div class="col-4 col-md-2">
          <div class="bg-white border rounded p-3 shadow-sm">
            <span class="d-block text-muted fs-9 fw-bold text-uppercase">Out of Stock</span>
            <span class="fs-4 fw-bold text-secondary"><?php echo $outOfStockProducts; ?></span>
          </div>
        </div>
      </div>

      <!-- Tables Section -->
      <div class="row g-4 mb-4">
        <!-- Recent Orders -->
        <div class="col-lg-6">
          <div class="bg-white border rounded-3 p-4 shadow-sm h-100">
            <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-cart3 text-success me-2"></i>Recent Orders</h5>
            <div class="table-responsive">
              <table class="table align-middle mb-0 fs-8">
                <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th class="text-end">Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $recResult = $conn->query("SELECT * FROM `order` ORDER BY `id` DESC LIMIT 5");
                  if ($recResult->num_rows === 0): ?>
                    <tr><td colspan="4" class="text-center text-muted">No orders found</td></tr>
                  <?php else: 
                      while ($ord = $recResult->fetch_assoc()):
                          $status = strtolower($ord['status']);
                          $badge = 'bg-warning text-dark';
                          if ($status === 'delivered' || $status === 'completed') $badge = 'bg-success text-white';
                          elseif ($status === 'cancelled') $badge = 'bg-danger text-white';
                  ?>
                    <tr>
                      <td class="fw-bold">#FR-<?php echo $ord['id']; ?></td>
                      <td><?php echo esc($ord['name']); ?></td>
                      <td><span class="badge <?php echo $badge; ?>"><?php echo esc(ucfirst($ord['status'])); ?></span></td>
                      <td class="text-end fw-bold text-success">$<?php echo number_format($ord['total_price'], 2); ?></td>
                    </tr>
                  <?php endwhile; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Low Stock Products -->
        <div class="col-lg-6">
          <div class="bg-white border rounded-3 p-4 shadow-sm h-100">
            <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-box-seam text-danger me-2"></i>Low Stock Products</h5>
            <div class="table-responsive">
              <table class="table align-middle mb-0 fs-8">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th class="text-center">Stock</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $stockResult = $conn->query("SELECT * FROM `product` ORDER BY `quantity` ASC LIMIT 5");
                  if ($stockResult->num_rows === 0): ?>
                    <tr><td colspan="3" class="text-center text-muted">No products found</td></tr>
                  <?php else: 
                      while ($prod = $stockResult->fetch_assoc()):
                          $qty = $prod['quantity'];
                          $stockClass = ($qty == 0) ? 'text-danger fw-bold' : (($qty < 10) ? 'text-warning fw-bold' : 'text-dark');
                  ?>
                    <tr>
                      <td><?php echo esc($prod['name']); ?></td>
                      <td class="fw-semibold">$<?php echo number_format($prod['price'], 2); ?></td>
                      <td class="text-center <?php echo $stockClass; ?>"><?php echo $qty; ?> left</td>
                    </tr>
                  <?php endwhile; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>
</body>
</html>
