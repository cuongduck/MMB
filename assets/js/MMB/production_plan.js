// assets/js/MMB/production_plan.js
document.addEventListener('DOMContentLoaded', function() {
    // Biến để theo dõi trạng thái đang tải
    let isLoading = false;
    
    // Khởi tạo các biến toàn cục
    let productionLines = [];
    let products = [];
    let cachedProductsByLine = {}; // Lưu trữ sản phẩm theo line để tránh gọi API nhiều lần
    
    // Xác định tab hiện tại từ URL
    let currentTab = 'daily';
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('tab')) {
        currentTab = urlParams.get('tab');
    }
    
    // Xác định xưởng đang được lọc
    let selectedFactory = 'all';
    if (urlParams.has('factory_filter')) {
        selectedFactory = urlParams.get('factory_filter');
    }
    
    // Hiển thị trạng thái đang tải
    function showLoading() {
        isLoading = true;
        const tableBody = document.getElementById('planTableBody');
        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="10" class="text-center">Đang tải dữ liệu...</td></tr>';
        }
    }
    
    // Ẩn trạng thái đang tải
    function hideLoading() {
        isLoading = false;
    }
    
    // Lấy dữ liệu ban đầu
    showLoading();
    loadProductionLines()
        .then(() => loadProducts())
        .then(() => loadProductionPlans())
        .then(() => hideLoading())
        .catch(error => {
            console.error('Error during initial load:', error);
            hideLoading();
            // Hiển thị lỗi trong bảng
            const tableBody = document.getElementById('planTableBody');
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Đã xảy ra lỗi khi tải dữ liệu. Vui lòng thử lại sau.</td></tr>';
            }
        });
    
    // Bắt sự kiện bộ lọc thời gian
    if (document.getElementById('filterBtn')) {
        document.getElementById('filterBtn').addEventListener('click', function() {
            showLoading();
            loadProductionPlans()
                .then(() => hideLoading())
                .catch(error => {
                    console.error('Error during filter:', error);
                    hideLoading();
                    // Hiển thị lỗi trong bảng
                    const tableBody = document.getElementById('planTableBody');
                    if (tableBody) {
                        tableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Đã xảy ra lỗi khi lọc dữ liệu. Vui lòng thử lại sau.</td></tr>';
                    }
                });
        });
    }
    
    // Bắt sự kiện thay đổi line trong form thêm mới
    if (document.getElementById('line_id')) {
        document.getElementById('line_id').addEventListener('change', function() {
            const lineId = this.value;
            if (lineId) {
                loadProductsByLine(lineId, 'product_id');
            } else {
                // Reset select box sản phẩm
                const productSelect = document.getElementById('product_id');
                productSelect.innerHTML = '<option value="">Chọn Sản Phẩm</option>';
            }
        });
    }
    
    // Bắt sự kiện thay đổi line trong form chỉnh sửa
    if (document.getElementById('edit_line_id')) {
        document.getElementById('edit_line_id').addEventListener('change', function() {
            const lineId = this.value;
            if (lineId) {
                loadProductsByLine(lineId, 'edit_product_id');
            } else {
                // Reset select box sản phẩm
                const productSelect = document.getElementById('edit_product_id');
                productSelect.innerHTML = '<option value="">Chọn Sản Phẩm</option>';
            }
        });
    }
    // Bắt sự kiện kiểm tra trùng lặp thời gian
    if (document.getElementById('start_time') && document.getElementById('end_time')) {
        document.getElementById('start_time').addEventListener('change', checkTimeOverlap);
        document.getElementById('end_time').addEventListener('change', checkTimeOverlap);
        document.getElementById('line_id').addEventListener('change', checkTimeOverlap);
    }
    
    if (document.getElementById('edit_start_time') && document.getElementById('edit_end_time')) {
        document.getElementById('edit_start_time').addEventListener('change', checkEditTimeOverlap);
        document.getElementById('edit_end_time').addEventListener('change', checkEditTimeOverlap);
        document.getElementById('edit_line_id').addEventListener('change', checkEditTimeOverlap);
    }
    
    // Bắt sự kiện submit form thêm kế hoạch
    if (document.getElementById('submitPlan')) {
        document.getElementById('submitPlan').addEventListener('click', function() {
            const timeOverlapWarning = document.getElementById('timeOverlapWarning');
            if (timeOverlapWarning && !timeOverlapWarning.classList.contains('d-none')) {
                alert('Không thể lưu kế hoạch do trùng thời gian với kế hoạch khác!');
                return;
            }
            
            submitPlanForm();
        });
    }
    
    // Bắt sự kiện submit form chỉnh sửa kế hoạch
    if (document.getElementById('updatePlan')) {
        document.getElementById('updatePlan').addEventListener('click', function() {
            const timeOverlapWarning = document.getElementById('editTimeOverlapWarning');
            if (timeOverlapWarning && !timeOverlapWarning.classList.contains('d-none')) {
                alert('Không thể cập nhật kế hoạch do trùng thời gian với kế hoạch khác!');
                return;
            }
            
            updatePlanForm();
        });
    }
    
    // Bắt sự kiện xóa kế hoạch
    if (document.getElementById('deletePlan')) {
        document.getElementById('deletePlan').addEventListener('click', function() {
            if (confirm('Bạn có chắc chắn muốn xóa kế hoạch này?')) {
                deletePlan();
            }
        });
    }
    
    // Hàm tải danh sách các line sản xuất
    function loadProductionLines() {
        // Kiểm tra cache trong localStorage
        const cachedData = localStorage.getItem('productionLines');
        const cacheTime = localStorage.getItem('productionLinesTime');
        
        if (cachedData && cacheTime && (Date.now() - parseInt(cacheTime)) < 3600000) { // 1 giờ
            productionLines = JSON.parse(cachedData);
            updateLineSelects(productionLines);
            return Promise.resolve();
        }
        
        return fetch('api/MMB/production_plan.php?action=get_lines')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    productionLines = data.data;
                    
                    // Cập nhật các select box
                    updateLineSelects(productionLines);
                    
                    // Lưu vào localStorage
                    localStorage.setItem('productionLines', JSON.stringify(productionLines));
                    localStorage.setItem('productionLinesTime', Date.now().toString());
                }
            })
            .catch(error => console.error('Error loading production lines:', error));
    }
    
    // Cập nhật select box line
    function updateLineSelects(lines) {
        const lineSelects = document.querySelectorAll('#line_id, #edit_line_id');
        lineSelects.forEach(select => {
            if (select) {
                // Giữ lại option đầu tiên
                const firstOption = select.options[0];
                select.innerHTML = '';
                select.appendChild(firstOption);
                
                // Thêm các option mới
                lines.forEach(line => {
                    const option = document.createElement('option');
                    option.value = line.id;
                    option.textContent = `${line.line_name} (${line.factory})`;
                    option.dataset.lineCode = line.line_code;
                    option.dataset.factory = line.factory;
                    select.appendChild(option);
                });
            }
        });
    }
    
    // Hàm tải danh sách sản phẩm
    function loadProducts() {
        // Kiểm tra cache trong localStorage
        const cachedData = localStorage.getItem('products');
        const cacheTime = localStorage.getItem('productsTime');
        
        if (cachedData && cacheTime && (Date.now() - parseInt(cacheTime)) < 3600000) { // 1 giờ
            products = JSON.parse(cachedData);
            return Promise.resolve();
        }
        
        return fetch('api/MMB/production_plan.php?action=get_products')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    products = data.data;
                    
                    // Lưu vào localStorage
                    localStorage.setItem('products', JSON.stringify(products));
                    localStorage.setItem('productsTime', Date.now().toString());
                }
            })
            .catch(error => console.error('Error loading products:', error));
    }
    // Hàm tải danh sách sản phẩm theo line
    function loadProductsByLine(lineId, targetSelectId) {
        // Kiểm tra cache
        if (cachedProductsByLine[lineId]) {
            updateProductSelect(cachedProductsByLine[lineId], targetSelectId);
            return Promise.resolve();
        }
        
        // Kiểm tra cache trong localStorage
        const cachedData = localStorage.getItem(`productsByLine_${lineId}`);
        const cacheTime = localStorage.getItem(`productsByLine_${lineId}_time`);
        
        if (cachedData && cacheTime && (Date.now() - parseInt(cacheTime)) < 3600000) { // 1 giờ
            const parsedData = JSON.parse(cachedData);
            cachedProductsByLine[lineId] = parsedData;
            updateProductSelect(parsedData, targetSelectId);
            return Promise.resolve();
        }
        
        return fetch(`api/MMB/production_plan.php?action=get_products_by_line&line_id=${lineId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Lưu vào cache
                    cachedProductsByLine[lineId] = data.data;
                    updateProductSelect(data.data, targetSelectId);
                    
                    // Lưu vào localStorage
                    localStorage.setItem(`productsByLine_${lineId}`, JSON.stringify(data.data));
                    localStorage.setItem(`productsByLine_${lineId}_time`, Date.now().toString());
                }
            })
            .catch(error => console.error('Error loading products by line:', error));
    }
    
    // Cập nhật select box sản phẩm
    function updateProductSelect(productList, targetSelectId) {
        const productSelect = document.getElementById(targetSelectId);
        if (!productSelect) return;
        
        // Giữ lại option đầu tiên
        const firstOption = productSelect.options[0];
        productSelect.innerHTML = '';
        productSelect.appendChild(firstOption);
        
        // Nhóm các sản phẩm theo product_group
        const groupedProducts = {};
        productList.forEach(product => {
            if (!groupedProducts[product.product_group]) {
                groupedProducts[product.product_group] = [];
            }
            groupedProducts[product.product_group].push(product);
        });
        
        // Thêm các optgroup và option
        Object.keys(groupedProducts).sort().forEach(group => {
            const optgroup = document.createElement('optgroup');
            optgroup.label = group;
            
            groupedProducts[group].sort((a, b) => a.product_name.localeCompare(b.product_name)).forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = `${product.product_code} - ${product.product_name}`;
                option.dataset.color = product.color_code;
                option.dataset.code = product.product_code;
                optgroup.appendChild(option);
            });
            
            productSelect.appendChild(optgroup);
        });
    }
    
    // Hàm tải kế hoạch sản xuất
    function loadProductionPlans() {
        let filterType = currentTab;
        let filterValue = '';
        
        // Lấy giá trị bộ lọc tùy theo tab
        switch (filterType) {
            case 'daily':
                const dateFilter = document.getElementById('dateFilter');
                filterValue = dateFilter ? dateFilter.value : new Date().toISOString().split('T')[0];
                break;
            case 'weekly':
                const weekFilter = document.getElementById('weekFilter');
                filterValue = weekFilter ? weekFilter.value : getDefaultWeek();
                break;
            case 'monthly':
                const monthFilter = document.getElementById('monthFilter');
                filterValue = monthFilter ? monthFilter.value : new Date().toISOString().slice(0, 7);
                break;
        }
        
        // Thêm tham số factory_filter vào URL API
        const apiUrl = `api/MMB/production_plan.php?action=get_plans&filter_type=${filterType}&filter_value=${filterValue}&factory_filter=${selectedFactory}`;
        
        return fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderProductionPlans(data.data);
                }
            })
            .catch(error => console.error('Error loading production plans:', error));
    }
    // Hàm hiển thị kế hoạch sản xuất
    function renderProductionPlans(plans) {
        const tableBody = document.getElementById('planTableBody');
        if (!tableBody) return;
        
        tableBody.innerHTML = '';
        
        // Nhóm các kế hoạch theo line_id
        const groupedPlans = {};
        plans.forEach(plan => {
            if (!groupedPlans[plan.line_id]) {
                groupedPlans[plan.line_id] = [];
            }
            groupedPlans[plan.line_id].push(plan);
        });
        
        // Nếu không có kế hoạch nào, hiển thị thông báo
        if (Object.keys(groupedPlans).length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = document.getElementById('productionPlanTable').rows[0].cells.length;
            cell.textContent = 'Không có kế hoạch sản xuất nào trong khoảng thời gian này';
            cell.className = 'text-center';
            row.appendChild(cell);
            tableBody.appendChild(row);
            return;
        }
        
        // Sắp xếp các line theo tên
        const sortedLineIds = Object.keys(groupedPlans).sort((a, b) => {
            const lineA = productionLines.find(l => l.id == a);
            const lineB = productionLines.find(l => l.id == b);
            return lineA && lineB ? lineA.line_name.localeCompare(lineB.line_name) : 0;
        });
        
        // Hiển thị các kế hoạch theo từng line
        sortedLineIds.forEach(lineId => {
            const line = productionLines.find(l => l.id == lineId);
            if (!line) return;
            
            // Thêm hàng tiêu đề line
            const headerRow = document.createElement('tr');
            headerRow.className = 'bg-light';
            
            const headerCell = document.createElement('td');
            headerCell.colSpan = document.getElementById('productionPlanTable').rows[0].cells.length;
            headerCell.className = 'font-weight-bold';
            headerCell.textContent = `${line.line_name} (${line.factory})`;
            
            headerRow.appendChild(headerCell);
            tableBody.appendChild(headerRow);
            
            // Sắp xếp các kế hoạch theo thời gian bắt đầu
            const sortedPlans = groupedPlans[lineId].sort((a, b) => new Date(a.start_time) - new Date(b.start_time));
            
            // Hiển thị từng kế hoạch
            sortedPlans.forEach(plan => {
                const row = document.createElement('tr');
                
                // Tìm thông tin sản phẩm
                const product = products.find(p => p.id == plan.product_id) || plan;
                
                // Tạo style cho ô sản phẩm dựa trên màu sắc
                const productStyle = product ? `background-color: ${product.color_code}; color: ${getContrastColor(product.color_code)}; font-weight: bold;` : '';
                
                // Thêm các ô thông tin
                row.innerHTML = `
                    <td>${plan.line_name}</td>
                    <td>${product.product_code || ''}</td>
                    <td style="${productStyle}">${product.product_name}</td>
                    <td>${formatDateTime(plan.start_time)}</td>
                    <td>${formatDateTime(plan.end_time)}</td>
                    <td class="text-right">${plan.planned_quantity.toLocaleString()}</td>
                    <td class="text-right">${plan.actual_quantity ? plan.actual_quantity.toLocaleString() : '0'}</td>
                    <td class="text-right">${plan.total_personnel || '0'}</td>
                    <td>${plan.notes || ''}</td>
                    ${document.getElementById('productionPlanTable').rows[0].cells.length > 9 ? `<td>
                        <a href="#" class="edit-btn" data-id="${plan.id}">Sửa</a>
                    </td>` : ''}
                `;
                
                tableBody.appendChild(row);
            });
        });
        
        // Thêm sự kiện cho các nút sửa
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const planId = this.dataset.id;
                const plan = plans.find(p => p.id == planId);
                if (plan) {
                    openEditModal(plan);
                }
            });
        });
    }
    // Hàm mở modal chỉnh sửa kế hoạch
    // Hàm mở modal chỉnh sửa kế hoạch
function openEditModal(plan) {
    const editModal = document.getElementById('editPlanModal');
    if (!editModal) return;
    
    console.log('Original plan data:', plan); // Debug: log dữ liệu gốc
    
    // Điền thông tin vào form
    document.getElementById('edit_plan_id').value = plan.id;
    document.getElementById('edit_line_id').value = plan.line_id;
    
    // Tải sản phẩm theo line trước khi đặt giá trị cho product_id
    loadProductsByLine(plan.line_id, 'edit_product_id')
        .then(() => {
            // Sau khi tải sản phẩm xong, mới set giá trị cho product_id
            setTimeout(() => {
                document.getElementById('edit_product_id').value = plan.product_id;
            }, 300);
        });
    
    // Xử lý thời gian
    try {
        console.log('Start time string:', plan.start_time); // Debug: log chuỗi thời gian gốc
        
        // Phân tích chuỗi thời gian thành các thành phần
        // Format từ MySQL: YYYY-MM-DD HH:MM:SS
        const startTimeStr = plan.start_time.replace(' ', 'T');
        const endTimeStr = plan.end_time.replace(' ', 'T');
        
        console.log('Formatted start time:', startTimeStr); // Debug: log chuỗi đã format
        
        document.getElementById('edit_start_time').value = startTimeStr;
        document.getElementById('edit_end_time').value = endTimeStr;
    } catch (e) {
        console.error('Error setting datetime:', e);
        
        // Phương án dự phòng nếu cách trên không hoạt động
        const startTime = new Date(plan.start_time);
        const endTime = new Date(plan.end_time);
        
        document.getElementById('edit_start_time').value = formatDateTimeForInput(startTime);
        document.getElementById('edit_end_time').value = formatDateTimeForInput(endTime);
    }
    
    document.getElementById('edit_planned_quantity').value = plan.planned_quantity;
    document.getElementById('edit_actual_quantity').value = plan.actual_quantity || 0;
    document.getElementById('edit_total_personnel').value = plan.total_personnel || 0;
    document.getElementById('edit_notes').value = plan.notes || '';
    
    // Kiểm tra trùng lặp thời gian
    setTimeout(() => {
        checkEditTimeOverlap();
    }, 500);
    
    // Hiện modal
    $(editModal).modal('show');
}
    
    // Hàm kiểm tra trùng lặp thời gian khi thêm mới
    function checkTimeOverlap() {
        const lineId = document.getElementById('line_id').value;
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;
        const warningElement = document.getElementById('timeOverlapWarning');
        
        if (!lineId || !startTime || !endTime || new Date(startTime) >= new Date(endTime)) {
            if (warningElement) {
                warningElement.classList.add('d-none');
            }
            return;
        }
        
        // Gửi yêu cầu kiểm tra trùng lặp
        const formData = new FormData();
        formData.append('line_id', lineId);
        formData.append('start_time', startTime);
        formData.append('end_time', endTime);
        
        fetch('api/MMB/production_plan.php?action=check_overlap', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && warningElement) {
                if (data.has_overlap) {
                    warningElement.classList.remove('d-none');
                } else {
                    warningElement.classList.add('d-none');
                }
            }
        })
        .catch(error => console.error('Error checking time overlap:', error));
    }
    
    // Hàm kiểm tra trùng lặp thời gian khi chỉnh sửa
    function checkEditTimeOverlap() {
        const planId = document.getElementById('edit_plan_id').value;
        const lineId = document.getElementById('edit_line_id').value;
        const startTime = document.getElementById('edit_start_time').value;
        const endTime = document.getElementById('edit_end_time').value;
        const warningElement = document.getElementById('editTimeOverlapWarning');
        
        if (!planId || !lineId || !startTime || !endTime || new Date(startTime) >= new Date(endTime)) {
            if (warningElement) {
                warningElement.classList.add('d-none');
            }
            return;
        }
        
        // Gửi yêu cầu kiểm tra trùng lặp
        const formData = new FormData();
        formData.append('plan_id', planId);
        formData.append('line_id', lineId);
        formData.append('start_time', startTime);
        formData.append('end_time', endTime);
        
        fetch('api/MMB/production_plan.php?action=check_overlap', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && warningElement) {
                if (data.has_overlap) {
                    warningElement.classList.remove('d-none');
                } else {
                    warningElement.classList.add('d-none');
                }
            }
        })
        .catch(error => console.error('Error checking time overlap:', error));
    }
    // Hàm submit form thêm kế hoạch
    function submitPlanForm() {
        const form = document.getElementById('addPlanForm');
        if (!form) return;
        
        // Kiểm tra dữ liệu form
        const lineId = document.getElementById('line_id').value;
        const productId = document.getElementById('product_id').value;
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;
        const plannedQuantity = document.getElementById('planned_quantity').value;
        
        if (!lineId || !productId || !startTime || !endTime || !plannedQuantity || parseInt(plannedQuantity) <= 0) {
            alert('Vui lòng điền đầy đủ thông tin!');
            return;
        }
        
        if (new Date(startTime) >= new Date(endTime)) {
            alert('Thời gian kết thúc phải sau thời gian bắt đầu!');
            return;
        }
        
        // Hiển thị trạng thái đang xử lý
        const submitBtn = document.getElementById('submitPlan');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Đang xử lý...';
        
        // Gửi yêu cầu thêm kế hoạch
        const formData = new FormData(form);
        
        fetch('api/MMB/production_plan.php?action=add_plan', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Khôi phục nút submit
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            
            if (data.status === 'success') {
                alert(data.message);
                $('#addPlanModal').modal('hide');
                form.reset();
                
                // Tải lại dữ liệu
                showLoading();
                loadProductionPlans()
                    .then(() => hideLoading())
                    .catch(error => {
                        console.error('Error reloading data:', error);
                        hideLoading();
                    });
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error adding production plan:', error);
            // Khôi phục nút submit
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            alert('Đã xảy ra lỗi khi thêm kế hoạch');
        });
    }
    
    // Hàm cập nhật kế hoạch
    function updatePlanForm() {
        const form = document.getElementById('editPlanForm');
        if (!form) return;
        
        // Kiểm tra dữ liệu form
        const planId = document.getElementById('edit_plan_id').value;
        const lineId = document.getElementById('edit_line_id').value;
        const productId = document.getElementById('edit_product_id').value;
        const startTime = document.getElementById('edit_start_time').value;
        const endTime = document.getElementById('edit_end_time').value;
        const plannedQuantity = document.getElementById('edit_planned_quantity').value;
        
        if (!planId || !lineId || !productId || !startTime || !endTime || !plannedQuantity || parseInt(plannedQuantity) <= 0) {
            alert('Vui lòng điền đầy đủ thông tin!');
            return;
        }
        
        if (new Date(startTime) >= new Date(endTime)) {
            alert('Thời gian kết thúc phải sau thời gian bắt đầu!');
            return;
        }
        
        // Hiển thị trạng thái đang xử lý
        const updateBtn = document.getElementById('updatePlan');
        const originalText = updateBtn.innerHTML;
        updateBtn.disabled = true;
        updateBtn.innerHTML = 'Đang xử lý...';
        
        // Gửi yêu cầu cập nhật kế hoạch
        const formData = new FormData(form);
        
        fetch('api/MMB/production_plan.php?action=update_plan', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Khôi phục nút update
            updateBtn.disabled = false;
            updateBtn.innerHTML = originalText;
            
            if (data.status === 'success') {
                alert(data.message);
                $('#editPlanModal').modal('hide');
                
                // Tải lại dữ liệu
                showLoading();
                loadProductionPlans()
                    .then(() => hideLoading())
                    .catch(error => {
                        console.error('Error reloading data:', error);
                        hideLoading();
                    });
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error updating production plan:', error);
            // Khôi phục nút update
            updateBtn.disabled = false;
            updateBtn.innerHTML = originalText;
            alert('Đã xảy ra lỗi khi cập nhật kế hoạch');
        });
    }
    // Hàm xóa kế hoạch
    function deletePlan() {
        const planId = document.getElementById('edit_plan_id').value;
        if (!planId) return;
        
        // Hiển thị trạng thái đang xử lý
        const deleteBtn = document.getElementById('deletePlan');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = 'Đang xử lý...';
        
        const formData = new FormData();
        formData.append('plan_id', planId);
        
        fetch('api/MMB/production_plan.php?action=delete_plan', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Khôi phục nút delete
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
            
            if (data.status === 'success') {
                alert(data.message);
                $('#editPlanModal').modal('hide');
                
                // Tải lại dữ liệu
                showLoading();
                loadProductionPlans()
                    .then(() => hideLoading())
                    .catch(error => {
                        console.error('Error reloading data:', error);
                        hideLoading();
                    });
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting production plan:', error);
            // Khôi phục nút delete
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
            alert('Đã xảy ra lỗi khi xóa kế hoạch');
        });
    }
    
    // Các hàm tiện ích
    
    // Định dạng thời gian cho hiển thị
    function formatDateTime(dateTimeStr) {
        const date = new Date(dateTimeStr);
        return date.toLocaleString('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Định dạng thời gian cho input datetime-local
    function formatDateTimeForInput(date) {
        // Đảm bảo date là instance của Date
        if (!(date instanceof Date)) {
            date = new Date(date);
        }
        
        // Lấy offset múi giờ local (phút)
        const tzOffset = date.getTimezoneOffset();
        
        // Điều chỉnh date theo offset (chuyển về giờ UTC)
        const localDate = new Date(date.getTime() - (tzOffset * 60000));
        
        // Chuyển về định dạng YYYY-MM-DDTHH:MM
        return localDate.toISOString().slice(0, 16);
    }
    
    // Lấy tuần mặc định (hiện tại)
    function getDefaultWeek() {
        const now = new Date();
        const year = now.getFullYear();
        const weekNumber = getWeekNumber(now);
        return `${year}-W${weekNumber.toString().padStart(2, '0')}`;
    }
    
    // Tính số tuần trong năm
    function getWeekNumber(date) {
        const firstDayOfYear = new Date(date.getFullYear(), 0, 1);
        const pastDaysOfYear = (date - firstDayOfYear) / 86400000;
        return Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7);
    }
    
    // Tính màu chữ tương phản với màu nền
    function getContrastColor(hexColor) {
        // Nếu không phải mã màu hợp lệ, trả về màu đen
        if (!hexColor || hexColor.indexOf('#') !== 0) {
            return '#000000';
        }
        
        // Chuyển mã màu hex sang RGB
        const r = parseInt(hexColor.slice(1, 3), 16);
        const g = parseInt(hexColor.slice(3, 5), 16);
        const b = parseInt(hexColor.slice(5, 7), 16);
        
        // Tính độ sáng (theo công thức YIQ)
        const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
        
        // Trả về màu đen hoặc trắng tùy theo độ sáng
        return (yiq >= 128) ? '#000000' : '#FFFFFF';
    }
});