<?php
// pages/line_product_management.php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireLogin();

// Kiểm tra quyền admin
if (!isAdmin()) {
    echo '<div class="alert alert-danger">Bạn không có quyền truy cập trang này</div>';
    exit;
}

include 'includes/header.php';
// Xử lý thêm sản phẩm mới
if (isset($_GET['action']) && $_GET['action'] == 'add_product') {
    $product_name = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
    $product_code = isset($_POST['product_code']) ? trim($_POST['product_code']) : '';
    $product_group = isset($_POST['product_group']) && $_POST['product_group'] != 'other' ? $_POST['product_group'] : (isset($_POST['other_group']) ? trim($_POST['other_group']) : '');
    $color_code = isset($_POST['color_code']) ? $_POST['color_code'] : '#3498db';
    
    if (!empty($product_name) && !empty($product_code) && !empty($product_group)) {
        // Kiểm tra product_code đã tồn tại chưa
        $check_query = "SELECT id FROM products WHERE product_code = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $product_code);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            echo '<div class="alert alert-danger">Mã sản phẩm đã tồn tại trong hệ thống!</div>';
        } else {
            // Thêm sản phẩm mới
            $insert_query = "INSERT INTO products (product_name, product_code, product_group, color_code) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ssss", $product_name, $product_code, $product_group, $color_code);
            
            if ($insert_stmt->execute()) {
                // Xóa cache nếu có
                $cache_file = 'cache/products.json';
                if (file_exists($cache_file)) {
                    unlink($cache_file);
                }
                
                echo '<div class="alert alert-success">Đã thêm sản phẩm mới thành công!</div>';
                
                // Tải lại danh sách sản phẩm
                $products_query = "SELECT id, product_name, product_code, product_group, color_code FROM products ORDER BY product_group, product_name";
                $products_result = $conn->query($products_query);
                $products = [];
                if ($products_result && $products_result->num_rows > 0) {
                    while ($row = $products_result->fetch_assoc()) {
                        $products[] = $row;
                    }
                }
                
                // Nhóm sản phẩm theo product_group
                $grouped_products = [];
                foreach ($products as $product) {
                    if (!isset($grouped_products[$product['product_group']])) {
                        $grouped_products[$product['product_group']] = [];
                    }
                    $grouped_products[$product['product_group']][] = $product;
                }
            } else {
                echo '<div class="alert alert-danger">Lỗi khi thêm sản phẩm: ' . $insert_stmt->error . '</div>';
            }
        }
    } else {
        echo '<div class="alert alert-danger">Vui lòng điền đầy đủ thông tin sản phẩm!</div>';
    }
}
// Kiểm tra xem bảng line_products đã tồn tại chưa, nếu chưa thì tạo mới
$check_table_query = "SHOW TABLES LIKE 'line_products'";
$check_table_result = $conn->query($check_table_query);

if ($check_table_result && $check_table_result->num_rows == 0) {
    // Bảng chưa tồn tại, tạo mới
    $create_table_query = "CREATE TABLE IF NOT EXISTS `line_products` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `line_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `line_product_unique` (`line_id`, `product_id`),
        KEY `line_id` (`line_id`),
        KEY `product_id` (`product_id`),
        CONSTRAINT `fk_lp_line` FOREIGN KEY (`line_id`) REFERENCES `production_lines` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_lp_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $conn->query($create_table_query);
    
    echo '<div class="alert alert-info">Đã tạo bảng line_products</div>';
}

// CSS cho form
echo '<style>
.line-product-container {
    margin-top: 100px !important;
    padding-top: 20px;
    max-width: 100%;
    padding-left: 30px;
    padding-right: 30px;
}
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 15px;
}
.product-item {
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 5px;
    background-color: #f9f9f9;
}
.product-item.selected {
    background-color: #d4edda;
    border-color: #c3e6cb;
}
.line-selector {
    margin-bottom: 20px;
}
.product-search {
    margin-bottom: 15px;
}
.product-group-header {
    font-weight: bold;
    margin-top: 15px;
    padding: 5px;
    background-color: #f0f0f0;
    border-radius: 3px;
}
</style>';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_line_products'])) {
    $line_id = isset($_POST['line_id']) ? intval($_POST['line_id']) : 0;
    $product_ids = isset($_POST['product_ids']) ? $_POST['product_ids'] : [];
    
    if ($line_id > 0) {
        // Xóa tất cả liên kết cũ
        $delete_query = "DELETE FROM line_products WHERE line_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $line_id);
        $delete_stmt->execute();
        
        // Thêm liên kết mới
        if (!empty($product_ids)) {
            $insert_query = "INSERT INTO line_products (line_id, product_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            
            foreach ($product_ids as $product_id) {
                $insert_stmt->bind_param("ii", $line_id, $product_id);
                $insert_stmt->execute();
            }
        }
        
        // Xóa cache
        $cache_file = 'cache/products_line_' . $line_id . '.json';
        if (file_exists($cache_file)) {
            unlink($cache_file);
        }
        
        echo '<div class="alert alert-success">Đã cập nhật danh sách sản phẩm cho line</div>';
    }
}

// Lấy danh sách line
$lines_query = "SELECT id, line_name, line_code, factory FROM production_lines ORDER BY factory, line_name";
$lines_result = $conn->query($lines_query);
$lines = [];
if ($lines_result && $lines_result->num_rows > 0) {
    while ($row = $lines_result->fetch_assoc()) {
        $lines[] = $row;
    }
}

// Lấy danh sách sản phẩm
$products_query = "SELECT id, product_name, product_code, product_group, color_code FROM products ORDER BY product_group, product_name";
$products_result = $conn->query($products_query);
$products = [];
if ($products_result && $products_result->num_rows > 0) {
    while ($row = $products_result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Nhóm sản phẩm theo product_group
$grouped_products = [];
foreach ($products as $product) {
    if (!isset($grouped_products[$product['product_group']])) {
        $grouped_products[$product['product_group']] = [];
    }
    $grouped_products[$product['product_group']][] = $product;
}

// Lấy danh sách sản phẩm đã được liên kết với line được chọn
$selected_line_id = isset($_GET['line_id']) ? intval($_GET['line_id']) : (isset($_POST['line_id']) ? intval($_POST['line_id']) : 0);
$selected_product_ids = [];

if ($selected_line_id > 0) {
    $selected_query = "SELECT product_id FROM line_products WHERE line_id = ?";
    $selected_stmt = $conn->prepare($selected_query);
    $selected_stmt->bind_param("i", $selected_line_id);
    $selected_stmt->execute();
    $selected_result = $selected_stmt->get_result();
    
    while ($row = $selected_result->fetch_assoc()) {
        $selected_product_ids[] = $row['product_id'];
    }
}
?>

<div class="line-product-container">
    <h2>Quản lý sản phẩm theo line</h2>
    
<div class="d-flex justify-content-between mb-3">
    <a href="?page=production_plan" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại kế hoạch sản xuất
    </a>
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addProductModal">
        <i class="fas fa-plus"></i> Thêm sản phẩm mới
    </button>
</div>
    
    <form method="get" class="line-selector">
        <input type="hidden" name="page" value="line_product_management">
        <div class="form-row align-items-center">
            <div class="col-auto">
                <label for="line_id">Chọn Line:</label>
                <select name="line_id" id="line_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Chọn Line --</option>
                    <?php foreach ($lines as $line): ?>
                    <option value="<?php echo $line['id']; ?>" <?php echo $selected_line_id == $line['id'] ? 'selected' : ''; ?>>
                        <?php echo $line['line_name'] . ' (' . $line['factory'] . ')'; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>
    
    <?php if ($selected_line_id > 0): ?>
    <form method="post">
        <input type="hidden" name="line_id" value="<?php echo $selected_line_id; ?>">
        
        <div class="form-group">
            <label>Chọn sản phẩm có thể chạy trên line này:</label>
            <p class="text-muted">Đã chọn: <span id="selectedCount"><?php echo count($selected_product_ids); ?></span> sản phẩm</p>
            
            <div class="product-search">
                <input type="text" id="productSearch" class="form-control" placeholder="Tìm kiếm sản phẩm...">
            </div>
            
            <?php foreach ($grouped_products as $group => $group_products): ?>
            <div class="product-group">
                <div class="product-group-header"><?php echo $group; ?></div>
                <div class="product-grid">
                    <?php foreach ($group_products as $product): ?>
                    <div class="product-item <?php echo in_array($product['id'], $selected_product_ids) ? 'selected' : ''; ?>" data-product-name="<?php echo strtolower($product['product_name']); ?>" data-product-code="<?php echo strtolower($product['product_code']); ?>">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="product_<?php echo $product['id']; ?>" 
                                   name="product_ids[]" value="<?php echo $product['id']; ?>"
                                   <?php echo in_array($product['id'], $selected_product_ids) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="product_<?php echo $product['id']; ?>">
                                <strong><?php echo $product['product_code']; ?></strong><br>
                                <?php echo $product['product_name']; ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="form-group mt-3">
            <button type="submit" name="save_line_products" class="btn btn-primary">Lưu thay đổi</button>
        </div>
    </form>
    
    <script>
    // Cập nhật số lượng sản phẩm đã chọn
    document.querySelectorAll('input[name="product_ids[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const parentItem = this.closest('.product-item');
            if (this.checked) {
                parentItem.classList.add('selected');
            } else {
                parentItem.classList.remove('selected');
            }
            
            // Cập nhật số lượng
            const selectedCount = document.querySelectorAll('input[name="product_ids[]"]:checked').length;
            document.getElementById('selectedCount').textContent = selectedCount;
        });
    });
    
    // Tìm kiếm sản phẩm
    document.getElementById('productSearch').addEventListener('input', function() {
        const searchText = this.value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(item => {
            const productName = item.dataset.productName;
            const productCode = item.dataset.productCode;
            const isMatch = productName.includes(searchText) || productCode.includes(searchText);
            item.style.display = isMatch ? 'block' : 'none';
        });
        
        // Hiển thị/ẩn group header nếu không có sản phẩm nào
        document.querySelectorAll('.product-group').forEach(group => {
            const visibleProducts = group.querySelectorAll('.product-item[style="display: block"]').length;
            const hiddenProducts = group.querySelectorAll('.product-item[style="display: none"]').length;
            const totalProducts = visibleProducts + hiddenProducts;
            
            if (visibleProducts === 0) {
                group.style.display = 'none';
            } else {
                group.style.display = 'block';
            }
        });
    });

    // Chọn/bỏ chọn tất cả
    function selectAll(checked) {
        document.querySelectorAll('input[name="product_ids[]"]').forEach(checkbox => {
            checkbox.checked = checked;
            const parentItem = checkbox.closest('.product-item');
            if (checked) {
                parentItem.classList.add('selected');
            } else {
                parentItem.classList.remove('selected');
            }
        });
        
        // Cập nhật số lượng
        const selectedCount = document.querySelectorAll('input[name="product_ids[]"]:checked').length;
        document.getElementById('selectedCount').textContent = selectedCount;
    }
    </script>
    <?php endif; ?>
    <!-- Modal Thêm Sản Phẩm -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Thêm Sản Phẩm Mới</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addProductForm" method="post" action="?page=line_product_management&action=add_product">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="product_name">Tên sản phẩm</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="product_code">Mã sản phẩm</label>
                            <input type="text" class="form-control" id="product_code" name="product_code" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="product_group">Nhóm sản phẩm</label>
                            <select class="form-control" id="product_group" name="product_group" required>
                                <option value="">Chọn nhóm sản phẩm</option>
                                <?php
                                // Lấy danh sách nhóm sản phẩm độc nhất
                                $product_groups = [];
                                foreach ($products as $product) {
                                    if (!in_array($product['product_group'], $product_groups)) {
                                        $product_groups[] = $product['product_group'];
                                    }
                                }
                                sort($product_groups);
                                foreach ($product_groups as $group) {
                                    echo "<option value=\"$group\">$group</option>";
                                }
                                ?>
                                <option value="other">Khác (Nhập mới)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6" id="other_group_container" style="display: none;">
                            <label for="other_group">Nhóm sản phẩm mới</label>
                            <input type="text" class="form-control" id="other_group" name="other_group">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="color_code">Mã màu</label>
                            <div class="input-group">
                                <input type="color" class="form-control" id="color_code" name="color_code" value="#3498db" style="height: 38px;">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="colorPreview" style="background-color: #3498db; width: 40px;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="submitProduct">Lưu sản phẩm</button>
            </div>
        </div>
    </div>
</div>

<script>
// Xử lý sự kiện khi chọn nhóm sản phẩm
document.getElementById('product_group').addEventListener('change', function() {
    const otherGroupContainer = document.getElementById('other_group_container');
    if (this.value === 'other') {
        otherGroupContainer.style.display = 'block';
        document.getElementById('other_group').setAttribute('required', 'required');
    } else {
        otherGroupContainer.style.display = 'none';
        document.getElementById('other_group').removeAttribute('required');
    }
});

// Cập nhật xem trước màu khi thay đổi
document.getElementById('color_code').addEventListener('input', function() {
    document.getElementById('colorPreview').style.backgroundColor = this.value;
});

// Xử lý sự kiện submit form thêm sản phẩm
document.getElementById('submitProduct').addEventListener('click', function() {
    const form = document.getElementById('addProductForm');
    
    // Kiểm tra dữ liệu form
    const productName = document.getElementById('product_name').value;
    const productCode = document.getElementById('product_code').value;
    let productGroup = document.getElementById('product_group').value;
    
    if (!productName || !productCode) {
        alert('Vui lòng điền đầy đủ thông tin!');
        return;
    }
    
    // Xử lý trường hợp nhóm sản phẩm khác
    if (productGroup === 'other') {
        const otherGroup = document.getElementById('other_group').value;
        if (!otherGroup) {
            alert('Vui lòng nhập tên nhóm sản phẩm mới!');
            return;
        }
        productGroup = otherGroup;
        
        // Cập nhật giá trị trong select box
        const productGroupSelect = document.getElementById('product_group');
        const newOption = document.createElement('option');
        newOption.value = otherGroup;
        newOption.text = otherGroup;
        newOption.selected = true;
        productGroupSelect.add(newOption, 0);
    }
    
    // Submit form
    form.submit();
});
</script>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>