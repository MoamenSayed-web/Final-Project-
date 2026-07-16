<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$conn = getDatabaseConnection();
$errorMsg = '';
$successMsg = '';

// Handle actions (Add, Edit, Delete)
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0.0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $image = trim($_POST['image'] ?? '');
        $discount = (int)($_POST['discount'] ?? 0);
        $expiry = trim($_POST['expiration_date'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!empty($name) && $price > 0 && $categoryId > 0 && !empty($expiry)) {
            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO `product` (`name`, `description`, `price`, `quantity`, `category_id`, `image`, `discount`, `expiration_date`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdiisiss", $name, $description, $price, $quantity, $categoryId, $image, $discount, $expiry, $status);
                if ($stmt->execute()) {
                    $successMsg = "Product added successfully!";
                } else {
                    $errorMsg = "Failed to add product.";
                }
                $stmt->close();
            } else {
                $stmt = $conn->prepare("UPDATE `product` SET `name` = ?, `description` = ?, `price` = ?, `quantity` = ?, `category_id` = ?, `image` = ?, `discount` = ?, `expiration_date` = ?, `status` = ? WHERE `id` = ?");
                $stmt->bind_param("ssdiisissi", $name, $description, $price, $quantity, $categoryId, $image, $discount, $expiry, $status, $productId);
                if ($stmt->execute()) {
                    $successMsg = "Product updated successfully!";
                } else {
                    $errorMsg = "Failed to update product.";
                }
                $stmt->close();
            }
        } else {
            $errorMsg = "Please fill in all required fields (Name, Price, Category, Expiration Date).";
        }
    }
} elseif ($action === 'delete' && $productId > 0) {
    $stmt = $conn->prepare("DELETE FROM `product` WHERE `id` = ?");
    $stmt->bind_param("i", $productId);
    if ($stmt->execute()) {
        $successMsg = "Product deleted successfully!";
    } else {
        $errorMsg = "Failed to delete product.";
    }
    $stmt->close();
}

// Search and Filters
$search = trim($_GET['search'] ?? '');
$categoryFilter = (int)($_GET['category'] ?? 0);

$query = "SELECT p.*, c.name as category_name FROM `product` p LEFT JOIN `category` c ON p.category_id = c.id WHERE 1=1";
$types = "";
$params = [];

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $types .= "ss";
    $searchWild = "%" . $search . "%";
    $params[] = $searchWild;
    $params[] = $searchWild;
}

if ($categoryFilter > 0) {
    $query .= " AND p.category_id = ?";
    $types .= "i";
    $params[] = $categoryFilter;
}

$query .= " ORDER BY p.id DESC";

$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$productsResult = $stmt->get_result();
$products = [];
while ($row = $productsResult->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

// Fetch categories for form options
$categoriesResult = $conn->query("SELECT * FROM `category` ORDER BY `name` ASC");
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch single product for edit form
$editProduct = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM `product` WHERE `id` = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editProduct = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$pageTitle = "Inventory Management - Fresh Rescue";
$pathPrefix = '../';
$activeSidebar = 'products';

include __DIR__ . '/../includes/head.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 fw-bold text-dark">Inventory Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <a href="products.php?action=new" class="btn btn-success bg-primary-custom rounded-pill px-3 py-2 fs-8 fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Add New Product
          </a>
        </div>
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

      <!-- Add/Edit form -->
      <?php if (isset($_GET['action']) && $_GET['action'] === 'new' || $editProduct): ?>
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-success text-white fw-bold py-3">
            <i class="bi bi-pencil-square me-2"></i> <?php echo $editProduct ? 'Edit Product' : 'Add New Product'; ?>
          </div>
          <div class="card-body p-4 bg-white">
            <form action="products.php" method="POST" class="needs-validation" novalidate>
              <input type="hidden" name="action" value="<?php echo $editProduct ? 'edit' : 'add'; ?>">
              <?php if ($editProduct): ?>
                <input type="hidden" name="product_id" value="<?php echo $editProduct['id']; ?>">
              <?php endif; ?>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold fs-8">Product Name *</label>
                  <input type="text" class="form-control rounded-custom" name="name" value="<?php echo esc($editProduct['name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold fs-8">Category *</label>
                  <select class="form-select rounded-custom" name="category_id" required>
                    <option value="">Choose category...</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?php echo $cat['id']; ?>" <?php echo (isset($editProduct['category_id']) && $editProduct['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo esc($cat['name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold fs-8">Price ($) *</label>
                  <input type="number" step="0.01" class="form-control rounded-custom" name="price" value="<?php echo esc($editProduct['price'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold fs-8">Discount (%)</label>
                  <input type="number" class="form-control rounded-custom" name="discount" value="<?php echo esc($editProduct['discount'] ?? '0'); ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold fs-8">Stock Level *</label>
                  <input type="number" class="form-control rounded-custom" name="quantity" value="<?php echo esc($editProduct['quantity'] ?? '0'); ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold fs-8">Expiration Date *</label>
                  <input type="date" class="form-control rounded-custom" name="expiration_date" value="<?php echo esc($editProduct['expiration_date'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold fs-8">Image URL</label>
                  <input type="text" class="form-control rounded-custom" name="image" value="<?php echo esc($editProduct['image'] ?? ''); ?>">
                </div>
                <div class="col-md-12">
                  <label class="form-label fw-semibold fs-8">Status</label>
                  <select class="form-select rounded-custom" name="status">
                    <option value="active" <?php echo (isset($editProduct['status']) && $editProduct['status'] === 'active') ? 'selected' : ''; ?>>Active (Visible)</option>
                    <option value="hidden" <?php echo (isset($editProduct['status']) && $editProduct['status'] === 'hidden') ? 'selected' : ''; ?>>Hidden</option>
                  </select>
                </div>
                <div class="col-md-12">
                  <label class="form-label fw-semibold fs-8">Description</label>
                  <textarea class="form-control rounded-custom" name="description" rows="3"><?php echo esc($editProduct['description'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-12 d-flex gap-2 justify-content-end mt-4">
                  <button type="submit" class="btn btn-success bg-primary-custom rounded-pill px-4">Save Product</button>
                  <a href="products.php" class="btn btn-light border rounded-pill px-4">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <!-- Filter Controls -->
      <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 bg-white">
          <form action="products.php" method="GET" class="row g-2 align-items-center">
            <div class="col-md-6 col-lg-5">
              <input type="text" class="form-control form-control-sm rounded-custom" name="search" placeholder="Search product name..." value="<?php echo esc($search); ?>">
            </div>
            <div class="col-md-4 col-lg-4">
              <select class="form-select form-select-sm rounded-custom" name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>><?php echo esc($cat['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2 col-lg-3 d-grid">
              <button type="submit" class="btn btn-success btn-sm bg-primary-custom rounded-custom">Search</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Inventory Table -->
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-8">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">Image</th>
                  <th>Name</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Discount</th>
                  <th>Stock</th>
                  <th>Expiry Date</th>
                  <th>Status</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($products)): ?>
                  <tr><td colspan="9" class="text-center text-muted py-4">No products found in inventory.</td></tr>
                <?php else: ?>
                  <?php foreach ($products as $p): 
                      $isOut = $p['quantity'] <= 0;
                  ?>
                    <tr>
                      <td class="ps-4">
                        <img src="<?php echo esc($p['image']); ?>" alt="Product image" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                      </td>
                      <td class="fw-bold text-dark"><?php echo esc($p['name']); ?></td>
                      <td><?php echo esc($p['category_name']); ?></td>
                      <td class="fw-semibold">$<?php echo number_format($p['price'], 2); ?></td>
                      <td><?php echo $p['discount']; ?>% OFF</td>
                      <td class="<?php echo $isOut ? 'text-danger fw-bold' : ''; ?>"><?php echo $isOut ? 'Out of Stock' : $p['quantity']; ?></td>
                      <td><?php echo esc($p['expiration_date']); ?></td>
                      <td>
                        <span class="badge <?php echo $p['status'] === 'active' ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary'; ?>">
                          <?php echo esc(ucfirst($p['status'])); ?>
                        </span>
                      </td>
                      <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                          <a href="products.php?edit_id=<?php echo $p['id']; ?>" class="btn btn-outline-success btn-sm py-1 px-2 rounded-custom fs-9"><i class="bi bi-pencil-square"></i> Edit</a>
                          <a href="products.php?action=delete&product_id=<?php echo $p['id']; ?>" class="btn btn-outline-danger btn-sm py-1 px-2 rounded-custom fs-9" onclick="return confirm('Are you sure you want to delete this product?');"><i class="bi bi-trash-fill"></i> Delete</a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
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
