<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/auth.php';

$pathPrefix = $pathPrefix ?? '';
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$user = getLoggedInUser();
$activePage = $activePage ?? '';
?>
<nav class="navbar navbar-expand-lg custom-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand" href="<?php echo $pathPrefix; ?>index.php">
      <i class="bi bi-shield-heart-fill me-2"></i>Fresh<span>Rescue</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent"
      aria-controls="navContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navContent">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?php echo $activePage === 'home' ? 'active' : ''; ?>" href="<?php echo $pathPrefix; ?>index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $activePage === 'products' ? 'active' : ''; ?>" href="<?php echo $pathPrefix; ?>products.php">Shop Products</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $pathPrefix; ?>index.php#categories">Categories</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $activePage === 'orders' ? 'active' : ''; ?>" href="<?php echo $pathPrefix; ?>orders.php">My Orders</a>
        </li>
      </ul>

      <div class="d-flex align-items-center gap-3">
        <div class="nav-search-wrap">
          <form action="<?php echo $pathPrefix; ?>products.php" method="GET" class="m-0">
            <input class="form-control nav-search-input" type="search" name="search" placeholder="Search groceries..." required>
          </form>
        </div>

        <a href="<?php echo $pathPrefix; ?>wishlist.php" class="navbar-icon-btn <?php echo $activePage === 'wishlist' ? 'active' : ''; ?>" title="Wishlist">
          <i class="bi bi-heart"></i>
        </a>

        <a href="<?php echo $pathPrefix; ?>cart.php" class="navbar-icon-btn <?php echo $activePage === 'cart' ? 'active' : ''; ?>" title="Cart">
          <i class="bi bi-cart3"></i>
          <span class="navbar-badge"><?php echo $cartCount; ?></span>
        </a>

        <?php if ($user): ?>
          <div class="dropdown">
            <a class="btn btn-login dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-fill me-1"></i><?php echo htmlspecialchars($user['name']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?php echo $pathPrefix; ?>profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
              <li><a class="dropdown-item" href="<?php echo $pathPrefix; ?>orders.php"><i class="bi bi-receipt me-2"></i>My Orders</a></li>
              <?php if ($user['role'] === 'admin'): ?>
                <li><a class="dropdown-item fw-bold text-success" href="<?php echo $pathPrefix; ?>admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?php echo $pathPrefix; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?php echo $pathPrefix; ?>login.php" class="btn btn-login">Login</a>
        <?php endif; ?>

      </div>
    </div>
  </div>
</nav>
