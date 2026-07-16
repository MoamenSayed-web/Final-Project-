<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    $user = getLoggedInUser();
    if ($user['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$pageTitle = "Login - Fresh Rescue";
$pathPrefix = '';
$activePage = 'login';

$errorMsg = $_SESSION['login_error'] ?? '';
$successMsg = $_SESSION['register_success'] ?? '';
unset($_SESSION['login_error'], $_SESSION['register_success']);

include __DIR__ . '/includes/head.php';
?>

<div class="container d-flex justify-content-center align-items-center min-vh-100 py-5">
  <div class="bg-white border p-4 p-md-5 rounded shadow-sm w-100 max-width-auth rounded-custom">

    <div class="text-center mb-4">
      <a href="index.php" class="text-decoration-none fw-bold fs-3 text-success">
        <i class="bi bi-shield-heart-fill me-2"></i>Fresh<span class="text-warning">Rescue</span>
      </a>
      <h4 class="fw-bold mt-3 text-dark">Sign In</h4>
      <p class="text-muted fs-8">Save food, save money. Access your rescue items.</p>
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

    <form action="auth/login.php" method="POST" class="needs-validation" novalidate>

      <div class="mb-3">
        <label for="loginEmail" class="form-label fw-semibold fs-8">Email Address</label>
        <input type="email" class="form-control rounded-custom" id="loginEmail" name="email"
          placeholder="name@example.com" required>
        <div class="invalid-feedback fs-9">Please enter a valid email address.</div>
      </div>

      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label for="loginPassword" class="form-label fw-semibold fs-8 m-0">Password</label>
          <a href="#" class="text-success fs-9 text-decoration-none fw-bold">Forgot password?</a>
        </div>
        <input type="password" class="form-control rounded-custom" id="loginPassword" name="password"
          placeholder="••••••••" required>
        <div class="invalid-feedback fs-9">Please enter your password.</div>
      </div>

      <button type="submit" class="btn btn-success w-100 py-2.5 fw-bold mb-3 bg-primary-custom rounded-custom">
        Sign In
      </button>

      <p class="text-center text-muted fs-8 mb-0">
        New here? <a href="register.php" class="text-success fw-bold text-decoration-none">Create Account</a>
      </p>

    </form>
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
