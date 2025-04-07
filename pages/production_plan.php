<?php
// pages/production_plan.php
// Thêm vào đầu file pages/production_plan.php
// CSS inline để đẩy nội dung xuống dưới header
// Thêm CSS cho production_plan.php để khớp với giao diện mới
echo '<style>
/* Định dạng tổng thể */
.container {
    margin-top: 100px !important;
    padding-top: 20px;
    max-width: 100%;
    padding-left: 30px;
    padding-right: 30px;
}

/* Định dạng tiêu đề và menu tab */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.page-title {
    font-size: 24px;
    font-weight: bold;
    color: #0056b3;
}

.tab-buttons {
    display: flex;
    gap: 10px;
}

.tab-btn {
    background-color: #fff;
    border: 1px solid #ddd;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    color: #333;
}

.tab-btn.active {
    background-color: #0056b3;
    color: #fff;
    border-color: #0056b3;
}

/* Định dạng bộ lọc xưởng và nút thêm kế hoạch */
.controls-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.factory-tabs {
    display: flex;
    gap: 10px;
}

.factory-tab {
    background-color: #f0f0f0;
    border: 1px solid #ddd;
    padding: 6px 14px;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    color: #333;
}

.factory-tab.active {
    background-color: #4CAF50;
    color: white;
    border-color: #4CAF50;
}

/* Định dạng bộ lọc thời gian */
.filter-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-input {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 6px 12px;
}

/* Định dạng bảng */
.table-responsive {
    margin-top: 10px;
}

/* Định dạng nút sửa */
.edit-btn {
    color: #0056b3;
    text-decoration: none;
}

/* Thêm CSS cho loading indicator */
.loading-indicator {
    text-align: center;
    padding: 20px;
    font-size: 1.2em;
    color: #666;
}

/* Cải thiện khả năng hiển thị trên các màn hình nhỏ */
@media (max-width: 767px) {
    .controls-row {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .factory-tabs, .filter-controls {
        margin-bottom: 10px;
        flex-wrap: wrap;
    }
}
</style>';

if (!function_exists('isAdmin')) {
    function isAdmin() {
        // Đảm bảo thông tin quyền được tải
        if (!isset($_SESSION['group_member']) && isset($_SESSION['user_id'])) {
            global $conn;
            $user_id = $_SESSION['user_id'];
            
            $sql = "SELECT username, Group_member, Brandy FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['group_member'] = $user['Group_member'];
                $_SESSION['brandy'] = $user['Brandy'];
            }
        }
    
        return isset($_SESSION['group_member']) && $_SESSION['group_member'] == 'Full_Control';
    }
}
// Kiểm tra tab đang xem - lấy từ URL
$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'daily';

// Kiểm tra và xử lý tham số factory từ URL
$selectedFactory = isset($_GET['factory_filter']) ? $_GET['factory_filter'] : 'all';
?>

<div class="container">
    <!-- Tab lọc theo xưởng và nút thêm kế hoạch + bộ lọc -->
    <div class="controls-row">
        <div class="factory-tabs">
            <a href="?page=production_plan&tab=<?php echo $currentTab; ?>&factory_filter=all" 
               class="factory-tab <?php echo $selectedFactory == 'all' ? 'active' : ''; ?>">Tất cả</a>
            <a href="?page=production_plan&tab=<?php echo $currentTab; ?>&factory_filter=f2" 
               class="factory-tab <?php echo $selectedFactory == 'f2' ? 'active' : ''; ?>">Mì_F2</a>
            <a href="?page=production_plan&tab=<?php echo $currentTab; ?>&factory_filter=f3" 
               class="factory-tab <?php echo $selectedFactory == 'f3' ? 'active' : ''; ?>">Mì_F3</a>
            <a href="?page=production_plan&tab=<?php echo $currentTab; ?>&factory_filter=f1" 
               class="factory-tab <?php echo $selectedFactory == 'f1' ? 'active' : ''; ?>">F1</a>
        </div>
        
        <div class="d-flex align-items-center">
            <div class="filter-controls mr-3">
                <?php if ($currentTab == 'daily'): ?>
                <input type="date" id="dateFilter" class="filter-input" value="<?php echo date('Y-m-d'); ?>">
                <?php elseif ($currentTab == 'weekly'): ?>
                <input type="week" id="weekFilter" class="filter-input" value="<?php echo date('Y').'-W'.date('W'); ?>">
                <?php elseif ($currentTab == 'monthly'): ?>
                <input type="month" id="monthFilter" class="filter-input" value="<?php echo date('Y-m'); ?>">
                <?php endif; ?>
                <button class="btn btn-outline-secondary" id="filterBtn">Lọc</button>
            </div>
            
            <?php if (isAdmin()): ?>
            <div class="ml-2">
                <a href="?page=line_product_management" class="btn btn-outline-info mr-2">
                    <i class="fas fa-cog"></i> Quản lý Sản Phẩm
                </a>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPlanModal">
                    <i class="fas fa-plus"></i> Thêm Kế Hoạch
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bảng kế hoạch sản xuất -->
    <div class="table-responsive">
        <table class="table table-bordered" id="productionPlanTable">
            <thead class="thead-dark">
                <tr>
                    <th>Line</th>
                    <th>Mã SP</th> <!-- Thêm cột mã sản phẩm -->
                    <th>Tên Sản Phẩm</th>
                    <th>Thời gian bắt đầu</th>
                    <th>Thời gian kết thúc</th>
                    <th>SL KH</th>
                    <th>SL Thực tê</th>
                    <th>Nhân Sự</th>
                    <th>Ghi Chú</th>
                    <?php if (isAdmin()): ?>
                    <th>Hành Động</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="planTableBody">
                <!-- Dữ liệu sẽ được nạp bằng AJAX -->
                <tr>
                    <td colspan="10" class="text-center">Đang tải dữ liệu...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<!-- Modal Thêm Kế Hoạch -->
<?php if (isAdmin()): ?>
<div class="modal fade" id="addPlanModal" tabindex="-1" role="dialog" aria-labelledby="addPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPlanModalLabel">Thêm Kế Hoạch Sản Xuất</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addPlanForm">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="line_id">Line Sản Xuất</label>
                            <select class="form-control" id="line_id" name="line_id" required>
                                <option value="">Chọn Line</option>
                                <!-- Options loaded via AJAX -->
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="product_id">Sản Phẩm</label>
                            <select class="form-control" id="product_id" name="product_id" required>
                                <option value="">Chọn Sản Phẩm</option>
                                <!-- Options loaded via AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="start_time">Thời gian bắt đầu</label>
                            <input type="datetime-local" class="form-control" id="start_time" name="start_time" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="end_time">Thời gian kết thúc</label>
                            <input type="datetime-local" class="form-control" id="end_time" name="end_time" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="planned_quantity">SL Kế hoạch (g/l)</label>
                            <input type="number" class="form-control" id="planned_quantity" name="planned_quantity" min="1" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="total_personnel">Nhân Sự</label>
                            <input type="number" class="form-control" id="total_personnel" name="total_personnel" min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes">Ghi Chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    <div id="timeOverlapWarning" class="alert alert-danger d-none">
                        Cảnh báo: Thời gian đã bị trùng với kế hoạch khác trên cùng line!
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="submitPlan">Lưu Kế Hoạch</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chỉnh Sửa Kế Hoạch -->
<div class="modal fade" id="editPlanModal" tabindex="-1" role="dialog" aria-labelledby="editPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPlanModalLabel">Chỉnh Sửa KHSX</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editPlanForm">
                    <input type="hidden" id="edit_plan_id" name="plan_id">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_line_id">Line Sản Xuất</label>
                            <select class="form-control" id="edit_line_id" name="line_id" required>
                                <option value="">Chọn Line</option>
                                <!-- Options loaded via AJAX -->
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit_product_id">Sản Phẩm</label>
                            <select class="form-control" id="edit_product_id" name="product_id" required>
                                <option value="">Chọn Sản Phẩm</option>
                                <!-- Options loaded via AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_start_time">Time Bắt Đầu</label>
                            <input type="datetime-local" class="form-control" id="edit_start_time" name="start_time" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit_end_time">Time Kết Thúc</label>
                            <input type="datetime-local" class="form-control" id="edit_end_time" name="end_time" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_planned_quantity">SL KH (g/l)</label>
                            <input type="number" class="form-control" id="edit_planned_quantity" name="planned_quantity" min="1" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit_actual_quantity">SL Thực Tế (g/l)</label>
                            <input type="number" class="form-control" id="edit_actual_quantity" name="actual_quantity" min="0">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_total_personnel">Total Nhân sự</label>
                            <input type="number" class="form-control" id="edit_total_personnel" name="total_personnel" min="0">
                        </div>
                        <div class="form-group col-md-6">
                            <!-- Giữ khoảng trống cho đối xứng -->
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_notes">Ghi Chú</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                    </div>
                    <div id="editTimeOverlapWarning" class="alert alert-danger d-none">
                        Cảnh báo: Thời gian đã bị trùng với kế hoạch khác trên cùng line!
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger mr-auto" id="deletePlan">Xóa Kế Hoạch</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="updatePlan">Cập Nhật</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Thêm scripts của trang -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/MMB/production_plan.js?v=<?php echo time(); ?>"></script>