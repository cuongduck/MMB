// Biến lưu trữ thông tin status toàn bộ các line
let allLinesStatus = {};
let lastTimestamp = null;

// Hàm lấy dữ liệu status của tất cả các line
function getAllLinesStatus(period = 'today') {
    console.log('Getting all lines status for period:', period);
    
    fetch(`api/MMB/get_status_all_lines.php?period=${period}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Kiểm tra xem có dữ liệu mới không
                if (data.timestamp !== lastTimestamp) {
                    lastTimestamp = data.timestamp;
                    
                    // Lưu dữ liệu vào biến để tiện sử dụng sau này
                    allLinesStatus = data.data;
                    
                    // Cập nhật UI với dữ liệu mới
                    updateAllLinesStatusUI();
                }
            } else {
                console.error('Error fetching all lines status:', data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching all lines status:', error);
        });
}

// Hàm cập nhật giao diện với dữ liệu status mới
function updateAllLinesStatusUI() {
    // Cập nhật status cho từng line
    for (const lineId in allLinesStatus) {
        const lineData = allLinesStatus[lineId];
        const lineNumber = lineId.toLowerCase(); // Ví dụ: 'L1' -> 'l1'
        
        // Cập nhật trạng thái
        const statusElement = document.getElementById(`${lineNumber}-status`);
        if (statusElement) {
            statusElement.className = 'status-badge';
            
            // Thêm class và nội dung dựa vào trạng thái
            // Chuyển đổi trạng thái về chữ thường để so sánh
            const statusLower = lineData.status.toLowerCase();
            
            if (statusLower === 'running') {
                statusElement.classList.add('running');
                statusElement.textContent = 'Đang chạy';
            } else if (statusLower === 'stopping') {
                statusElement.classList.add('stopping');
                statusElement.textContent = 'Đang dừng';
            } else if (statusLower === 'stopped') {
                statusElement.classList.add('stopped');
                statusElement.textContent = 'Đang dừng';
            } else {
                statusElement.classList.add('unknown');
                statusElement.textContent = 'Không xác định';
            }
        }
        
        // Cập nhật tên sản phẩm
        const productElement = document.getElementById(`${lineNumber}-product`);
        if (productElement) {
            productElement.textContent = lineData.product || '-';
        }
        
        // Cập nhật tốc độ
        const speedElement = document.getElementById(`${lineNumber}-speed`);
        if (speedElement) {
            // Xác định đơn vị đo tốc độ dựa vào line
            let speedUnit = 'Dao/phút';
            if (lineId === 'CSD' || lineId === 'FS') {
                speedUnit = 'Chai/H';
            }
            
            speedElement.textContent = `${lineData.speed || '0'} ${speedUnit}`;
        }
        
        // Cập nhật thông tin sản lượng nếu có phần tử tương ứng
        const productionElement = document.getElementById(`${lineNumber}-production`);
        if (productionElement && lineData.production) {
            productionElement.textContent = lineData.production;
        }
    }
}

// Hàm khởi tạo
function initStatusAllLines() {
    console.log('Initializing status for all lines...');
    
    // Lấy period từ URL nếu có
    const urlParams = new URLSearchParams(window.location.search);
    const period = urlParams.get('period') || 'today';
    
    // Lấy dữ liệu ban đầu
    getAllLinesStatus(period);
    
    // Thiết lập cập nhật tự động mỗi 10 giây
    setInterval(() => {
        getAllLinesStatus(period);
    }, 10000);
    
    // Xử lý sự kiện thay đổi period
    const periodButtons = document.querySelectorAll('[data-period]');
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            const newPeriod = this.dataset.period;
            getAllLinesStatus(newPeriod);
        });
    });
}

// Gọi hàm khởi tạo khi trang được tải
document.addEventListener('DOMContentLoaded', initStatusAllLines);

// Xuất hàm để các module khác có thể sử dụng
window.getAllLinesStatus = getAllLinesStatus;