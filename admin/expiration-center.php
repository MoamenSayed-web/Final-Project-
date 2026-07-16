<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$conn = getDatabaseConnection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

if ($productId > 0 && !empty($action)) {
    if ($action === 'increase_discount') {
        $conn->query("UPDATE `product` SET `discount` = LEAST(`discount` + 10, 90) WHERE `id` = $productId");
        $_SESSION['expiry_msg'] = "Discount increased successfully!";
    } elseif ($action === 'hide') {
        $conn->query("UPDATE `product` SET `status` = 'hidden' WHERE `id` = $productId");
        $_SESSION['expiry_msg'] = "Product is now hidden from storefront!";
    } elseif ($action === 'delete') {
        $conn->query("DELETE FROM `product` WHERE `id` = $productId");
        $_SESSION['expiry_msg'] = "Product deleted successfully!";
    } elseif ($action === 'mark_expired') {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $conn->query("UPDATE `product` SET `expiration_date` = '$yesterday' WHERE `id` = $productId");
        $_SESSION['expiry_msg'] = "Product marked as expired!";
    } elseif ($action === 'edit_date') {
        $newDate = $_POST['expiration_date'] ?? '';
        if (!empty($newDate)) {
            $stmt = $conn->prepare("UPDATE `product` SET `expiration_date` = ? WHERE `id` = ?");
            $stmt->bind_param("si", $newDate, $productId);
            $stmt->execute();
            $stmt->close();
            $_SESSION['expiry_msg'] = "Expiration date updated successfully!";
        }
    }
    header('Location: expiration-center.php');
    exit();
}

$msg = $_SESSION['expiry_msg'] ?? '';
unset($_SESSION['expiry_msg']);

// Retrieve all products
$result = $conn->query("SELECT p.*, c.name as category_name FROM `product` p LEFT JOIN `category` c ON p.category_id = c.id ORDER BY p.expiration_date ASC");
$products = [];
while ($row = $result->fetch_assoc()) {
    $row['expiry_info'] = calculateExpiry($row['expiration_date']);
    $products[] = $row;
}

$pageTitle = "Expiration Center - Fresh Rescue";
$pathPrefix = '../';
$activeSidebar = 'expiration';

include __DIR__ . '/../includes/head.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 fw-bold text-dark">Expiration Center</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <span class="badge bg-danger px-3 py-2 fs-7 rounded-pill"><i class="bi bi-shield-fill-exclamation me-1"></i> Inventory Expiry Control</span>
        </div>
      </div>

      <?php if (!empty($msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>
          <?php echo htmlspecialchars($msg); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <!-- Dynamic Expiry Groupings -->
      <div class="row g-4 mb-4">
        <!-- Expired Section -->
        <div class="col-12">
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-danger text-white fw-bold py-3">
              <i class="bi bi-x-circle-fill me-2"></i> Expired Products (Action Required)
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-8">
                  <thead class="table-light">
                    <tr>
                      <th class="ps-4">Product Name</th>
                      <th>Category</th>
                      <th>Expiration Date</th>
                      <th>Status Info</th>
                      <th>Current Discount</th>
                      <th class="text-center">Quick Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    $hasExpired = false;
                    foreach ($products as $p):
                        if ($p['expiry_info']['status'] === 'expired'):
                            $hasExpired = true;
                    ?>
                      <tr>
                        <td class="ps-4 fw-bold text-dark"><?php echo esc($p['name']); ?></td>
                        <td><?php echo esc($p['category_name']); ?></td>
                        <td class="fw-semibold text-danger"><?php echo esc($p['expiration_date']); ?></td>
                        <td>
                          <span class="badge bg-danger bg-opacity-10 text-danger px-2.5 py-1 rounded">
                            <i class="bi <?php echo $p['expiry_info']['icon']; ?> me-1"></i><?php echo esc($p['expiry_info']['text']); ?>
                          </span>
                        </td>
                        <td class="fw-bold"><?php echo $p['discount']; ?>% OFF</td>
                        <td class="text-center">
                          <div class="d-flex justify-content-center gap-2">
                            <form action="expiration-center.php" method="POST" class="d-inline">
                              <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                              <input type="hidden" name="action" value="increase_discount">
                              <button type="submit" class="btn btn-outline-warning btn-sm fs-9 rounded-custom" title="Increase Discount">+10% Discount</button>
                            </form>
                            <form action="expiration-center.php" method="POST" class="d-inline">
                              <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                              <input type="hidden" name="action" value="hide">
                              <button type="submit" class="btn btn-outline-secondary btn-sm fs-9 rounded-custom" title="Hide Product"><i class="bi bi-eye-slash-fill"></i> Hide</button>
                            </form>
                            <form action="expiration-center.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                              <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                              <input type="hidden" name="action" value="delete">
                              <button type="submit" class="btn btn-outline-danger btn-sm fs-9 rounded-custom" title="Delete Product"><i class="bi bi-trash-fill"></i> Delete</button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    <?php endif; endforeach; 
                    if (!$hasExpired): ?>
                      <tr><td colspan="6" class="text-center text-muted py-4">No expired products in database!</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Near Expiry Section (7 Days or Less) -->
        <div class="col-12">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-warning text-dark fw-bold py-3">
              <i class="bi bi-hourglass-split me-2"></i> Near Expiration Products (7 Days or Less)
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-8">
                  <thead class="table-light">
                    <tr>
                      <th class="ps-4">Product Name</th>
                      <th>Category</th>
                      <th>Expiration Date</th>
                      <th>Status Info</th>
                      <th>Current Discount</th>
                      <th class="text-center">Quick Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    $hasNear = false;
                    foreach ($products as $p):
                        if (in_array($p['expiry_info']['status'], ['today', 'tomorrow', 'urgent', 'near'])):
                            $hasNear = true;
                            $badge = 'bg-warning text-dark';
                            if ($p['expiry_info']['status'] === 'today' || $p['expiry_info']['status'] === 'tomorrow') {
                                $badge = 'bg-danger text-white';
                            }
                    ?>
                      <tr>
                        <td class="ps-4 fw-bold text-dark"><?php echo esc($p['name']); ?></td>
                        <td><?php echo esc($p['category_name']); ?></td>
                        <td class="fw-semibold"><?php echo esc($p['expiration_date']); ?></td>
                        <td>
                          <span class="badge <?php echo $p['expiry_info']['badge_class']; ?> px-2.5 py-1 rounded">
                            <i class="bi <?php echo $p['expiry_info']['icon']; ?> me-1"></i><?php echo esc($p['expiry_info']['text']); ?>
                          </span>
                        </td>
                        <td class="fw-bold"><?php echo $p['discount']; ?>% OFF</td>
                        <td class="text-center">
                          <div class="d-flex justify-content-center gap-2 align-items-center">
                            <form action="expiration-center.php" method="POST" class="d-inline m-0">
                              <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                              <input type="hidden" name="action" value="increase_discount">
                              <button type="submit" class="btn btn-outline-success btn-sm fs-9 rounded-custom" title="Increase Discount">+10% Discount</button>
                            </form>
                            <form action="expiration-center.php" method="POST" class="d-inline m-0">
                              <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                              <input type="hidden" name="action" value="mark_expired">
                              <button type="submit" class="btn btn-outline-danger btn-sm fs-9 rounded-custom" title="Mark Expired"><i class="bi bi-clock-history"></i> Mark Expired</button>
                            </form>
                            
                            <!-- Edit Date Inline Form -->
                            <form action="expiration-center.php" method="POST" class="d-inline-flex m-0 align-items-center gap-1">
                              <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                              <input type="hidden" name="action" value="edit_date">
                              <input type="date" class="form-control form-control-sm border py-0.5 px-2" name="expiration_date" value="<?php echo esc($p['expiration_date']); ?>" required style="max-width: 130px; font-size: 0.8rem;">
                              <button type="submit" class="btn btn-success btn-sm py-1 px-2 rounded-custom fs-9" title="Save Expiration Date">Save</button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    <?php endif; endforeach; 
                    if (!$hasNear): ?>
                      <tr><td colspan="6" class="text-center text-muted py-4">No near expiration products!</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>
</body>
</html>
