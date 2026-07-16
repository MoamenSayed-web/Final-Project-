<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$conn = getDatabaseConnection();
$userId = $_SESSION['user_id'];
$errorMsg = '';
$successMsg = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newName = trim($_POST['name'] ?? '');
    $newPhone = trim($_POST['phone'] ?? '');
    $newAddress = trim($_POST['address'] ?? '');

    if (!empty($newName) && !empty($newPhone) && !empty($newAddress)) {
        $conn->begin_transaction();
        try {
            // 1. Update user name
            $stmt = $conn->prepare("UPDATE `user` SET `name` = ? WHERE `id` = ?");
            $stmt->bind_param("si", $newName, $userId);
            $stmt->execute();
            $stmt->close();

            // 2. Update user phone
            $stmt = $conn->prepare("SELECT COUNT(*) FROM `user_phones` WHERE `user_id` = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->bind_result($phoneCount);
            $stmt->fetch();
            $stmt->close();

            if ($phoneCount > 0) {
                $stmt = $conn->prepare("UPDATE `user_phones` SET `phone` = ? WHERE `user_id` = ?");
                $stmt->bind_param("si", $newPhone, $userId);
            } else {
                $stmt = $conn->prepare("INSERT INTO `user_phones` (`user_id`, `phone`) VALUES (?, ?)");
                $stmt->bind_param("is", $userId, $newPhone);
            }
            $stmt->execute();
            $stmt->close();

            // 3. Update user address
            $stmt = $conn->prepare("SELECT COUNT(*) FROM `user_addresses` WHERE `user_id` = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->bind_result($addressCount);
            $stmt->fetch();
            $stmt->close();

            if ($addressCount > 0) {
                $stmt = $conn->prepare("UPDATE `user_addresses` SET `address` = ? WHERE `user_id` = ?");
                $stmt->bind_param("si", $newAddress, $userId);
            } else {
                $stmt = $conn->prepare("INSERT INTO `user_addresses` (`user_id`, `address`) VALUES (?, ?)");
                $stmt->bind_param("is", $userId, $newAddress);
            }
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $_SESSION['user_name'] = $newName;
            $successMsg = "Profile updated successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $errorMsg = "Failed to update profile: " . $e->getMessage();
        }
    } else {
        $errorMsg = "All fields are required.";
    }
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM `user` WHERE `id` = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

$userName = $userRow['name'];
$userEmail = $userRow['email'];
$userCreatedAt = date('F Y', strtotime($userRow['created_at']));

// Fetch phone
$stmt = $conn->prepare("SELECT `phone` FROM `user_phones` WHERE `user_id` = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($userPhone);
$stmt->fetch();
$stmt->close();
$userPhone = $userPhone ?? '';

// Fetch address
$stmt = $conn->prepare("SELECT `address` FROM `user_addresses` WHERE `user_id` = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($userAddress);
$stmt->fetch();
$stmt->close();
$userAddress = $userAddress ?? '';

// Fetch user orders (limit 5)
$userOrders = [];
$stmt = $conn->prepare("SELECT * FROM `order` WHERE `user_id` = ? ORDER BY `id` DESC LIMIT 5");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $userOrders[] = $row;
}
$stmt->close();

$pageTitle = "My Profile - Fresh Rescue";
$pathPrefix = '';
$activePage = 'profile';

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="py-5">
  <div class="container py-4">
    <h2 class="fw-bold mb-4 text-primary-custom">My Account</h2>

    <div class="row g-4">
      <div class="col-lg-4">
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

        <div class="profile-card shadow-sm border bg-white p-4 rounded-3 text-center">
          <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=198754&color=fff&size=150" alt="<?php echo esc($userName); ?> avatar" class="profile-card-img rounded-circle mb-3">
          
          <?php if (isset($_GET['action']) && $_GET['action'] === 'edit'): ?>
            <form action="profile.php" method="POST" class="text-start w-100 mt-3 needs-validation" novalidate>
              <input type="hidden" name="update_profile" value="1">
              
              <div class="mb-3">
                <label for="editName" class="form-label fw-semibold fs-8 mb-1">Full Name</label>
                <input type="text" class="form-control rounded-custom fs-8" id="editName" name="name" value="<?php echo esc($userName); ?>" required>
                <div class="invalid-feedback fs-9">Please enter your name.</div>
              </div>

              <div class="mb-3">
                <label for="editPhone" class="form-label fw-semibold fs-8 mb-1">Phone Number</label>
                <input type="tel" class="form-control rounded-custom fs-8" id="editPhone" name="phone" value="<?php echo esc($userPhone); ?>" required>
                <div class="invalid-feedback fs-9">Please enter your phone number.</div>
              </div>

              <div class="mb-3">
                <label for="editAddress" class="form-label fw-semibold fs-8 mb-1">Delivery Address</label>
                <input type="text" class="form-control rounded-custom fs-8" id="editAddress" name="address" value="<?php echo esc($userAddress); ?>" required>
                <div class="invalid-feedback fs-9">Please enter your address.</div>
              </div>

              <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-success flex-grow-1 fw-bold py-2 bg-primary-custom rounded-custom fs-8">
                  Save
                </button>
                <a href="profile.php" class="btn btn-light border flex-grow-1 fw-bold py-2 rounded-custom fs-8">
                  Cancel
                </a>
              </div>
            </form>
          <?php else: ?>
            <h4 class="fw-bold mb-1"><?php echo esc($userName); ?></h4>
            <p class="text-muted fs-8 mb-4">Member since <?php echo esc($userCreatedAt); ?></p>

            <div class="text-start mb-4 w-100 border-top pt-3">
              <div class="mb-3">
                <span class="d-block text-muted fs-9 fw-bold text-uppercase">Email Address</span>
                <span class="fw-semibold text-dark"><?php echo esc($userEmail); ?></span>
              </div>
              <div class="mb-3">
                <span class="d-block text-muted fs-9 fw-bold text-uppercase">Phone Number</span>
                <span class="fw-semibold text-dark"><?php echo esc($userPhone ?: 'No phone number added'); ?></span>
              </div>
              <div class="mb-0">
                <span class="d-block text-muted fs-9 fw-bold text-uppercase">Delivery Address</span>
                <span class="fw-semibold text-dark"><?php echo esc($userAddress ?: 'No address added'); ?></span>
              </div>
            </div>

            <a href="profile.php?action=edit" class="btn btn-success w-100 fw-bold py-2 bg-primary-custom rounded-custom">
              <i class="bi bi-pencil-square me-1"></i> Edit Profile
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="bg-white border rounded-3 p-4 shadow-sm">
          <h5 class="fw-bold border-bottom pb-3 mb-4"><i class="bi bi-receipt me-2 text-success"></i>Recent Orders</h5>

          <div class="table-responsive">
            <table class="table align-middle cart-table mb-0">
              <thead>
                <tr>
                  <th scope="col" class="border-top-none">Order #</th>
                  <th scope="col" class="text-center border-top-none">Status</th>
                  <th scope="col" class="text-end border-top-none">Total</th>
                  <th scope="col" class="text-center border-top-none">Details</th>
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
                      <td class="fw-bold">#FR-<?php echo htmlspecialchars($order['id']); ?></td>
                      <td class="text-center">
                        <span class="badge <?php echo $badgeClass; ?> px-2.5 py-1 rounded fs-badge">
                          <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                        </span>
                      </td>
                      <td class="text-end fw-bold text-success">$<?php echo number_format($order['total_price'], 2); ?></td>
                      <td class="text-center">
                        <a href="orders.php?id=<?php echo htmlspecialchars($order['id']); ?>" class="btn btn-light btn-sm border fs-8 fw-semibold rounded-custom">View</a>
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
