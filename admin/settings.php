<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$settingsFile = __DIR__ . '/../config/settings.json';

// Default settings
$settings = [
    'store_name' => 'Fresh Rescue',
    'currency' => '$',
    'delivery_fee' => 2.00,
    'email' => 'support@freshrescue.com',
    'phone' => '+20 123 456 7890',
    'timezone' => 'Africa/Cairo'
];

if (file_exists($settingsFile)) {
    $settings = array_merge($settings, json_decode(file_get_contents($settingsFile), true));
}

$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['store_name'] = trim($_POST['store_name'] ?? 'Fresh Rescue');
    $settings['currency'] = trim($_POST['currency'] ?? '$');
    $settings['delivery_fee'] = (float)($_POST['delivery_fee'] ?? 2.00);
    $settings['email'] = trim($_POST['email'] ?? 'support@freshrescue.com');
    $settings['phone'] = trim($_POST['phone'] ?? '+20 123 456 7890');
    $settings['timezone'] = trim($_POST['timezone'] ?? 'Africa/Cairo');

    file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
    $successMsg = "Store configurations updated successfully!";
}

$pageTitle = "Store Settings - Fresh Rescue";
$pathPrefix = '../';
$activeSidebar = 'settings';

include __DIR__ . '/../includes/head.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 fw-bold text-dark">Store Settings</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <span class="badge bg-success px-3 py-2 fs-7 rounded-pill"><i class="bi bi-gear me-1"></i> Meta Configurations</span>
        </div>
      </div>

      <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>
          <?php echo htmlspecialchars($successMsg); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4 bg-white">
          <form action="settings.php" method="POST" class="needs-validation" novalidate>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold fs-8">Store Name</label>
                <input type="text" class="form-control rounded-custom" name="store_name" value="<?php echo esc($settings['store_name']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold fs-8">Currency Symbol</label>
                <input type="text" class="form-control rounded-custom" name="currency" value="<?php echo esc($settings['currency']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold fs-8">Delivery Fees ($)</label>
                <input type="number" step="0.01" class="form-control rounded-custom" name="delivery_fee" value="<?php echo esc($settings['delivery_fee']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold fs-8">Store Email</label>
                <input type="email" class="form-control rounded-custom" name="email" value="<?php echo esc($settings['email']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold fs-8">Store Phone</label>
                <input type="text" class="form-control rounded-custom" name="phone" value="<?php echo esc($settings['phone']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold fs-8">System Timezone</label>
                <input type="text" class="form-control rounded-custom" name="timezone" value="<?php echo esc($settings['timezone']); ?>" required>
              </div>
              <div class="col-md-12 d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-success bg-primary-custom rounded-pill px-4 py-2 fw-bold">Save Settings</button>
              </div>
            </div>
          </form>
        </div>
      </div>

    </main>
  </div>
</div>

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
</body>
</html>
