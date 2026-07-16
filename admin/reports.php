<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$conn = getDatabaseConnection();

// 1. Fetch Top Selling Products
$topProducts = [];
$resProducts = $conn->query("SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price_at_purchase) as revenue
                            FROM `order_item` oi
                            JOIN `product` p ON oi.product_id = p.id
                            GROUP BY p.id
                            ORDER BY total_sold DESC
                            LIMIT 5");
while ($row = $resProducts->fetch_assoc()) {
    $topProducts[] = $row;
}

// 2. Fetch Top Categories
$topCategories = [];
$resCategories = $conn->query("SELECT c.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price_at_purchase) as revenue
                              FROM `order_item` oi
                              JOIN `product` p ON oi.product_id = p.id
                              JOIN `category` c ON p.category_id = c.id
                              GROUP BY c.id
                              ORDER BY total_sold DESC
                              LIMIT 5");
while ($row = $resCategories->fetch_assoc()) {
    $topCategories[] = $row;
}

// 3. Overall Totals
$totalSales = $conn->query("SELECT SUM(`total_price`) FROM `order` WHERE `status` != 'Cancelled'")->fetch_row()[0] ?? 0.0;
$totalOrders = $conn->query("SELECT COUNT(*) FROM `order` WHERE `status` != 'Cancelled'")->fetch_row()[0] ?? 0;
$avgOrderValue = $totalOrders > 0 ? ($totalSales / $totalOrders) : 0.0;

$pageTitle = "Business Reports - Fresh Rescue";
$pathPrefix = '../';
$activeSidebar = 'reports';

include __DIR__ . '/../includes/head.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 fw-bold text-dark">Business Analytics & Reports</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <span class="badge bg-success px-3 py-2 fs-7 rounded-pill"><i class="bi bi-graph-up-arrow me-1"></i> Performance Reports</span>
        </div>
      </div>

      <!-- Overview Cards -->
      <div class="row g-3 mb-4 text-center">
        <div class="col-md-4">
          <div class="bg-white border rounded p-4 shadow-sm">
            <span class="d-block text-muted fs-8 fw-bold text-uppercase mb-2">Total Gross Sales</span>
            <span class="fs-2 fw-extrabold text-success fw-bold">$<?php echo number_format($totalSales, 2); ?></span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="bg-white border rounded p-4 shadow-sm">
            <span class="d-block text-muted fs-8 fw-bold text-uppercase mb-2">Total Rescued Orders</span>
            <span class="fs-2 fw-extrabold text-primary fw-bold"><?php echo $totalOrders; ?></span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="bg-white border rounded p-4 shadow-sm">
            <span class="d-block text-muted fs-8 fw-bold text-uppercase mb-2">Average Order Value</span>
            <span class="fs-2 fw-extrabold text-warning fw-bold">$<?php echo number_format($avgOrderValue, 2); ?></span>
          </div>
        </div>
      </div>

      <div class="row g-4 mb-4">
        <!-- Top Selling Products -->
        <div class="col-lg-6">
          <div class="bg-white border rounded-3 p-4 shadow-sm h-100">
            <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-award text-success me-2"></i>Top Selling Products</h5>
            <div class="table-responsive">
              <table class="table align-middle mb-0 fs-8">
                <thead>
                  <tr>
                    <th>Product Name</th>
                    <th class="text-center">Total Quantity Sold</th>
                    <th class="text-end">Revenue Contribution</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($topProducts)): ?>
                    <tr><td colspan="3" class="text-center text-muted">No sales metrics available.</td></tr>
                  <?php else: ?>
                    <?php foreach ($topProducts as $tp): ?>
                      <tr>
                        <td class="fw-bold text-dark"><?php echo esc($tp['name']); ?></td>
                        <td class="text-center fw-semibold"><?php echo $tp['total_sold']; ?> sold</td>
                        <td class="text-end fw-bold text-success">$<?php echo number_format($tp['revenue'], 2); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Top Selling Categories -->
        <div class="col-lg-6">
          <div class="bg-white border rounded-3 p-4 shadow-sm h-100">
            <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-tag text-success me-2"></i>Top Selling Categories</h5>
            <div class="table-responsive">
              <table class="table align-middle mb-0 fs-8">
                <thead>
                  <tr>
                    <th>Category Name</th>
                    <th class="text-center">Total Quantity Sold</th>
                    <th class="text-end">Revenue Contribution</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($topCategories)): ?>
                    <tr><td colspan="3" class="text-center text-muted">No sales metrics available.</td></tr>
                  <?php else: ?>
                    <?php foreach ($topCategories as $tc): ?>
                      <tr>
                        <td class="fw-bold text-dark"><?php echo esc($tc['name']); ?></td>
                        <td class="text-center fw-semibold"><?php echo $tc['total_sold']; ?> sold</td>
                        <td class="text-end fw-bold text-success">$<?php echo number_format($tc['revenue'], 2); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
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
