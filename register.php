<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$pageTitle = "Register - Fresh Rescue";
$pathPrefix = '';
$activePage = 'register';

$errorMsg = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);

include __DIR__ . '/includes/head.php';
?>

<div class="container d-flex justify-content-center align-items-center min-vh-100 py-5">
  <div class="bg-white border p-4 p-md-5 rounded shadow-sm w-100 max-width-register rounded-custom">

    <div class="text-center mb-4">
      <a href="index.php" class="text-decoration-none fw-bold fs-3 text-success">
        <i class="bi bi-shield-heart-fill me-2"></i>Fresh<span class="text-warning">Rescue</span>
      </a>
      <h4 class="fw-bold mt-3 text-dark">Create Account</h4>
      <p class="text-muted fs-8">Join to claim custom rescue discounts and save eco waste.</p>
    </div>

    <?php if (!empty($errorMsg)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo htmlspecialchars($errorMsg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <form action="auth/register.php" method="POST" class="needs-validation" novalidate id="registerForm">

      <div class="mb-3">
        <label for="fullName" class="form-label fw-semibold fs-8">Full Name</label>
        <input type="text" class="form-control rounded-custom" id="fullName" name="name" placeholder="John Doe" required>
        <div class="invalid-feedback fs-9">Please enter your name.</div>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label fw-semibold fs-8">Email Address</label>
        <input type="email" class="form-control rounded-custom" id="email" name="email" placeholder="john@example.com" required>
        <div class="invalid-feedback fs-9">Please enter a valid email address.</div>
      </div>

      <div class="mb-3">
        <label for="phone" class="form-label fw-semibold fs-8">Phone Number</label>
        <input type="tel" class="form-control rounded-custom" id="phone" name="phone" placeholder="+1 (555) 345-6789" required>
        <div class="invalid-feedback fs-9">Please enter your phone number.</div>
      </div>

      <div class="mb-3">
        <label for="address" class="form-label fw-semibold fs-8">Address</label>
        <input type="text" class="form-control rounded-custom" id="address" name="address" placeholder="123 Eco Green Boulevard" required>
        <div class="invalid-feedback fs-9">Please enter your delivery address.</div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="password" class="form-label fw-semibold fs-8">Password</label>
          <input type="password" class="form-control rounded-custom" id="password" name="password" placeholder="Min 8 chars" minlength="8" required>
          <div class="invalid-feedback fs-9">Must be at least 8 characters.</div>
        </div>
        <div class="col-md-6 mb-4">
          <label for="confirm_password" class="form-label fw-semibold fs-8">Confirm Password</label>
          <input type="password" class="form-control rounded-custom" id="confirm_password" name="confirm_password" placeholder="Retype password" required>
          <div class="invalid-feedback fs-9" id="confirmFeedback">Please confirm password.</div>
        </div>
      </div>

      <button type="submit" class="btn btn-success w-100 py-2.5 fw-bold mb-3 bg-primary-custom rounded-custom">
        Create Account
      </button>

      <p class="text-center text-muted fs-8 mb-0">
        Already have an account? <a href="login.php" class="text-success fw-bold text-decoration-none">Sign In</a>
      </p>

    </form>
  </div>
</div>

<script>
  const form = document.getElementById('registerForm');
  const password = document.getElementById('password');
  const confirm = document.getElementById('confirm_password');
  const feedback = document.getElementById('confirmFeedback');

  form.addEventListener('submit', function (event) {
    if (password.value !== confirm.value) {
      confirm.setCustomValidity("Passwords do not match");
      feedback.textContent = "Passwords do not match.";
    } else {
      confirm.setCustomValidity("");
    }

    if (!form.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
    }
    form.classList.add('was-validated');
  }, false);
</script>
</body>
</html>
