<?php
$activeSidebar = $activeSidebar ?? '';
$pathPrefix = $pathPrefix ?? '../';
?>
<div class="col-md-3 col-lg-2 bg-dark sidebar p-0 min-vh-100 shadow" id="sidebarMenu">
  <div class="position-sticky pt-3">
    <div class="px-4 py-2 border-bottom border-secondary mb-3">
      <a class="navbar-brand text-light fs-4 fw-bold d-flex align-items-center" href="<?php echo $pathPrefix; ?>index.php">
        <i class="bi bi-shield-heart-fill text-success me-2"></i>Fresh<span class="text-success">Rescue</span>
      </a>
      <small class="text-muted d-block mt-1">Administrator Portal</small>
    </div>
    
    <ul class="nav flex-column px-3 gap-1 admin-sidebar-nav">
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 <?php echo $activeSidebar === 'dashboard' ? 'bg-success text-white fw-bold active' : 'text-white-75'; ?>" href="<?php echo $pathPrefix; ?>admin/dashboard.php">
          <i class="bi bi-speedometer2 text-success"></i>
          Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 <?php echo $activeSidebar === 'expiration' ? 'bg-success text-white fw-bold active' : 'text-white-75'; ?>" href="<?php echo $pathPrefix; ?>admin/expiration-center.php">
          <i class="bi bi-calendar-event text-warning"></i>
          Expiration Center
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 <?php echo $activeSidebar === 'products' ? 'bg-success text-white fw-bold active' : 'text-white-75'; ?>" href="<?php echo $pathPrefix; ?>admin/products.php">
          <i class="bi bi-box-seam text-info"></i>
          Products
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 <?php echo $activeSidebar === 'orders' ? 'bg-success text-white fw-bold active' : 'text-white-75'; ?>" href="<?php echo $pathPrefix; ?>admin/orders.php">
          <i class="bi bi-cart text-primary"></i>
          Orders
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 <?php echo $activeSidebar === 'customers' ? 'bg-success text-white fw-bold active' : 'text-white-75'; ?>" href="<?php echo $pathPrefix; ?>admin/customers.php">
          <i class="bi bi-people text-light"></i>
          Customers
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 <?php echo $activeSidebar === 'payments' ? 'bg-success text-white fw-bold active' : 'text-white-75'; ?>" href="<?php echo $pathPrefix; ?>admin/payments.php">
          <i class="bi bi-credit-card text-success"></i>
          Payments
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 <?php echo $activeSidebar === 'reports' ? 'bg-success text-white fw-bold active' : 'text-white-75'; ?>" href="<?php echo $pathPrefix; ?>admin/reports.php">
          <i class="bi bi-graph-up-arrow text-danger"></i>
          Reports
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 <?php echo $activeSidebar === 'settings' ? 'bg-success text-white fw-bold active' : 'text-white-75'; ?>" href="<?php echo $pathPrefix; ?>admin/settings.php">
          <i class="bi bi-gear text-secondary"></i>
          Settings
        </a>
      </li>
      <li class="nav-item mt-4 border-top border-secondary pt-2">
        <a class="nav-link text-danger d-flex align-items-center gap-2 rounded px-3 py-2" href="<?php echo $pathPrefix; ?>logout.php">
          <i class="bi bi-box-arrow-right"></i>
          Logout
        </a>
      </li>
    </ul>
  </div>
</div>

<style>
.admin-sidebar-nav .nav-link {
  transition: all 0.2s ease;
  color: rgba(255, 255, 255, 0.75) !important;
}
.admin-sidebar-nav .nav-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: #ffffff !important;
}
.admin-sidebar-nav .nav-link.active {
  background-color: #198754 !important;
  color: #ffffff !important;
}
.admin-sidebar-nav .nav-link.active i {
  color: #ffffff !important;
}
</style>
