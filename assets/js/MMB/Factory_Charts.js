// Khởi tạo tất cả biểu đồ cho trang factory
function initializeCharts() {
    console.log('Initializing all factory charts...');
    
    // Khởi tạo Factory OEE Chart
    if (typeof initFactoryOEEChart === 'function' && document.getElementById('factoryOEEChart')) {
        initFactoryOEEChart();
    }
    
    // Khởi tạo OEE by Line Chart
    if (typeof initOEEByLineChart === 'function' && document.getElementById('oeeByLineChart')) {
        initOEEByLineChart();
    }
    
    // Khởi tạo Factory Steam Chart
    if (typeof initFactorySteamChart === 'function' && document.getElementById('factorySteamChart')) {
        initFactorySteamChart();
    }
}

// Hàm cập nhật tất cả biểu đồ với period mới
function updateAllCharts(period) {
    console.log('Updating all factory charts for period:', period);
    
    // Cập nhật Factory OEE Chart
    if (typeof updateFactoryOEEChart === 'function' && document.getElementById('factoryOEEChart')) {
        updateFactoryOEEChart(period);
    }
    
    // Cập nhật OEE by Line Chart
    if (typeof updateOEEByLineChart === 'function' && document.getElementById('oeeByLineChart')) {
        updateOEEByLineChart(period);
    }
    
    // Cập nhật Factory Steam Chart
    if (typeof updateFactorySteamChart === 'function' && document.getElementById('factorySteamChart')) {
        updateFactorySteamChart(period);
    }
}

// Thiết lập sự kiện cho các nút period
function setupPeriodButtons() {
    const periodButtons = document.querySelectorAll('.date-filter .btn');
    
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Xóa class active khỏi tất cả các nút
            periodButtons.forEach(btn => btn.classList.remove('active'));
            
            // Thêm class active vào nút hiện tại
            this.classList.add('active');
            
            // Lấy period từ nút được nhấn
            const period = this.dataset.period;
            
            // Cập nhật tất cả biểu đồ với period mới
            updateAllCharts(period);
        });
    });
}

// Khởi tạo khi trang đã tải xong
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo biểu đồ
    initializeCharts();
    
    // Thiết lập sự kiện nút
    setupPeriodButtons();
});