<?php
$pathPrefix = $pathPrefix ?? '';
?>
  <footer class="site-footer" id="contact">
    <div class="container">
      <div class="row gy-4">

        <div class="col-md-6 col-lg-3">
          <a href="<?php echo $pathPrefix; ?>index.php" class="navbar-brand text-dark fs-3 d-inline-flex align-items-center mb-3">
            <i class="bi bi-shield-heart-fill text-success me-2"></i>Fresh<span class="text-success">Rescue</span>
          </a>
          <p class="mb-4">Saving food. Supporting communities. Building a more sustainable tomorrow.</p>
          <div class="d-flex flex-column gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-geo-alt text-success fs-5"></i>
              <span>123 Green Street, Cairo, Egypt</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-telephone text-success fs-5"></i>
              <span>+20 123 456 7890</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-envelope text-success fs-5"></i>
              <span>support@freshrescue.com</span>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-3">
          <h5 class="fw-bold mb-3">Quick Links</h5>
          <ul class="list-unstyled footer-links">
            <li><a href="<?php echo $pathPrefix; ?>index.php">Home</a></li>
            <li><a href="<?php echo $pathPrefix; ?>products.php">Shop Products</a></li>
            <li><a href="<?php echo $pathPrefix; ?>index.php#categories">Categories</a></li>
            <li><a href="<?php echo $pathPrefix; ?>orders.php">My Orders</a></li>
            <li><a href="<?php echo $pathPrefix; ?>index.php#contact">Contact Us</a></li>
          </ul>
        </div>

        <div class="col-md-6 col-lg-3">
          <h5 class="fw-bold mb-3">Customer Service</h5>
          <ul class="list-unstyled footer-links">
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Terms & Conditions</a></li>
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Refund Policy</a></li>
          </ul>
        </div>

        <div class="col-md-6 col-lg-3">
          <h5 class="fw-bold mb-3">Follow Us</h5>
          <div class="d-flex gap-3 mb-4">
            <a href="#" class="social-icon-btn"><i class="bi bi-facebook"></i></a>
            <a href="#" class="social-icon-btn"><i class="bi bi-twitter-x"></i></a>
            <a href="#" class="social-icon-btn"><i class="bi bi-instagram"></i></a>
            <a href="#" class="social-icon-btn"><i class="bi bi-youtube"></i></a>
          </div>
          <h5 class="fw-bold mb-3">We Accept</h5>
          <div class="d-flex gap-2 accept-cards">
            <span class="badge bg-light text-dark border p-2"><i class="bi bi-credit-card-2-front text-success me-1"></i>Visa</span>
            <span class="badge bg-light text-dark border p-2"><i class="bi bi-wallet2 text-success me-1"></i>Wallet</span>
            <span class="badge bg-light text-dark border p-2"><i class="bi bi-cash text-success me-1"></i>COD</span>
          </div>
        </div>

      </div>

      <div class="footer-trust-row">
        <div class="footer-trust-item">
          <i class="bi bi-shield-check"></i>
          <span>100% Secure Payments</span>
        </div>
        <div class="trust-divider d-none d-md-block"></div>
        <div class="footer-trust-item">
          <i class="bi bi-leaf"></i>
          <span>Fresh & Quality Products</span>
        </div>
        <div class="trust-divider d-none d-md-block"></div>
        <div class="footer-trust-item">
          <i class="bi bi-truck"></i>
          <span>Fast & Reliable Delivery</span>
        </div>
      </div>

      <div class="site-footer-bottom">
        <p class="m-0">&copy; <?php echo date('Y'); ?> Fresh Rescue. All rights reserved. &nbsp;|&nbsp; Made with <i
            class="bi bi-heart-fill text-success"></i> for a better planet.</p>
      </div>

    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
