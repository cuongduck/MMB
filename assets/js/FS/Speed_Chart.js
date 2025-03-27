// Biến toàn cục cho biểu đồ và dữ liệu
let speedChart;
let speedChartData = [];
let currentOffset = 0;
let dataLimit = 100;
let totalRecords = 0;
let isAutoScrolling = true;
const standardSpeed = 36000; // Tốc độ chuẩn (chai/giờ)
let autoUpdateInterval; // Biến để theo dõi interval

// Khởi tạo biểu đồ tốc độ
function initSpeedChart() {
    // Kiểm tra xem canvas có tồn tại không
    const canvas = document.getElementById('speedChart');
    if (!canvas) {
        console.warn('Speed chart canvas not found');
        return;
    }

    const ctx = canvas.getContext('2d');
    
    // Tạo gradient cho nền ECG
    const gridLineColor = '#e0e0e0';
    const standardLineColor = '#ff6384';
    
    speedChart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [
                {
                    label: 'Tốc độ thực tế',
                    data: [],
                    borderColor: function(context) {
                        // Tô màu đỏ cho các điểm dưới tốc độ chuẩn
                        const index = context.dataIndex;
                        const value = context.dataset.data[index]?.y;
                        return value < standardSpeed * 0.9 ? '#f44336' : '#4caf50';
                    },
                    borderWidth: 2,
                    pointRadius: 1,
                    pointHoverRadius: 5,
                    fill: false,
                    tension: 0.1, // Đường cong nhẹ để tạo hiệu ứng ECG
                    spanGaps: true
                },
                {
                    label: 'Tốc độ chuẩn',
                    data: [],
                    borderColor: standardLineColor,
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false,
                    borderDash: [5, 5],
                    tension: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 0 // Tắt animation để tăng hiệu suất
            },
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'minute',
                        displayFormats: {
                            minute: 'HH:mm'
                        },
                        tooltipFormat: 'YYYY-MM-DD HH:mm:ss'
                    },
                    grid: {
                        color: gridLineColor,
                        borderColor: '#9e9e9e',
                        tickColor: '#9e9e9e'
                    },
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 10
                    }
                },
                y: {
                    min: 0,
                    max: standardSpeed * 1.1, // Cao hơn 10% so với tốc độ chuẩn
                    grid: {
                        color: gridLineColor,
                        borderColor: '#9e9e9e',
                        tickColor: '#9e9e9e'
                    },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    title: {
                        display: true,
                        text: 'Tốc độ (chai/giờ)'
                    }
                }
            },
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.raw?.y !== undefined) {
                                label += context.raw.y.toLocaleString() + ' chai/giờ';
                            }
                            return label;
                        }
                    }
                },
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
    
    // Đầu tiên, lấy tổng số bản ghi để tính offset của trang cuối cùng
    fetchTotalRecords();
}

// Hàm mới: Lấy tổng số bản ghi trước
function fetchTotalRecords() {
    fetch(`api/FS/get_speed_trend.php?limit=1&offset=0`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                totalRecords = data.total;
                
                // Tính toán offset của trang cuối cùng
                const lastPageOffset = Math.max(0, Math.floor((totalRecords - 1) / dataLimit) * dataLimit);
                
                // Tải dữ liệu của trang cuối cùng
                loadSpeedData(lastPageOffset);
            } else {
                console.error('Lỗi khi lấy tổng số bản ghi:', data.message);
                // Nếu có lỗi, tải trang đầu tiên
                loadSpeedData(0);
            }
        })
        .catch(error => {
            console.error('Lỗi khi tải dữ liệu ban đầu:', error);
            // Nếu có lỗi, tải trang đầu tiên
            loadSpeedData(0);
        });
}

// Tải dữ liệu tốc độ từ API
function loadSpeedData(goToSpecificOffset) {
    if (goToSpecificOffset !== undefined) {
        currentOffset = goToSpecificOffset;
    }
    
    // Hiển thị chỉ báo đang tải nếu element tồn tại
    const indicator = document.querySelector('.ecg-status-indicator');
    if (indicator) {
        indicator.classList.add('loading');
    }
    
    fetch(`api/FS/get_speed_trend.php?limit=${dataLimit}&offset=${currentOffset}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                totalRecords = data.total;
                updateSpeedChartData(data.data);
                updatePaginationInfo();
                
                // Ẩn chỉ báo đang tải
                if (indicator) {
                    indicator.classList.remove('loading');
                }
            } else {
                console.error('Lỗi khi tải dữ liệu:', data.message);
            }
        })
        .catch(error => {
            console.error('Lỗi khi lấy dữ liệu tốc độ:', error);
            if (indicator) {
                indicator.classList.remove('loading');
            }
        });
}

// Cập nhật biểu đồ với dữ liệu mới
function updateSpeedChartData(data) {
    if (!speedChart || !data || data.length === 0) return;
    
    // Xóa dữ liệu biểu đồ trước đó
    speedChart.data.datasets[0].data = [];
    speedChart.data.datasets[1].data = [];
    
    // Lưu trữ dữ liệu thô để phân trang
    speedChartData = data;
    
    // Xử lý dữ liệu cho biểu đồ
    data.forEach(item => {
        // Thêm dữ liệu tốc độ thực tế
        speedChart.data.datasets[0].data.push({
            x: item.timestamp,
            y: item.speed
        });
        
        // Thêm đường tham chiếu tốc độ chuẩn
        speedChart.data.datasets[1].data.push({
            x: item.timestamp,
            y: standardSpeed
        });
    });
    
    // Cập nhật biểu đồ
    speedChart.update();
}

// Cập nhật thông tin phân trang
function updatePaginationInfo() {
    const infoElement = document.getElementById('ecg-page-info');
    if (!infoElement) return;
    
    const startIdx = currentOffset + 1;
    const endIdx = Math.min(currentOffset + dataLimit, totalRecords);
    const totalPages = Math.ceil(totalRecords / dataLimit);
    const currentPage = Math.floor(currentOffset / dataLimit) + 1;
    
    infoElement.textContent = 
        `Hiển thị ${startIdx}-${endIdx} trên tổng số ${totalRecords} bản ghi | Trang ${currentPage}/${totalPages}`;
}

// Các hàm điều hướng
function goToStart() {
    if (currentOffset !== 0) {
        isAutoScrolling = false;
        updateAutoScrollButton();
        loadSpeedData(0);
    }
}

function moveLeft() {
    if (currentOffset - dataLimit >= 0) {
        isAutoScrolling = false;
        updateAutoScrollButton();
        loadSpeedData(currentOffset - dataLimit);
    }
}

function moveRight() {
    if (currentOffset + dataLimit < totalRecords) {
        isAutoScrolling = false;
        updateAutoScrollButton();
        loadSpeedData(currentOffset + dataLimit);
    }
}

function goToEnd() {
    const lastPageOffset = Math.floor((totalRecords - 1) / dataLimit) * dataLimit;
    if (currentOffset !== lastPageOffset) {
        isAutoScrolling = true; // Đặt thành true để tiếp tục tự động cuộn
        updateAutoScrollButton();
        loadSpeedData(lastPageOffset);
    }
}

// Bật/tắt tự động cuộn
function toggleAutoScroll() {
    isAutoScrolling = !isAutoScrolling;
    updateAutoScrollButton();
    
    if (isAutoScrolling) {
        goToEnd();
        // Thiết lập polling cho dữ liệu mới
        setupDataPolling();
    } else {
        // Nếu tắt tự động cuộn, xóa interval hiện tại
        if (autoUpdateInterval) {
            clearInterval(autoUpdateInterval);
            autoUpdateInterval = null;
        }
    }
}

// Cập nhật nút tự động cuộn
function updateAutoScrollButton() {
    const button = document.getElementById('autoScrollButton');
    if (!button) return;
    
    if (isAutoScrolling) {
        button.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="6" y="4" width="4" height="16"></rect>
                <rect x="14" y="4" width="4" height="16"></rect>
            </svg>
            Dừng cuộn
        `;
        button.classList.add('success');
        const paper = document.querySelector('.ecg-paper');
        if (paper) paper.classList.add('ecg-running');
    } else {
        button.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="5 3 19 12 5 21 5 3"></polygon>
            </svg>
            Tự động cuộn
        `;
        button.classList.remove('success');
        const paper = document.querySelector('.ecg-paper');
        if (paper) paper.classList.remove('ecg-running');
    }
}

// Thiết lập polling cho dữ liệu mới - Đã giảm xuống còn 3 giây
function setupDataPolling() {
    // Xóa interval hiện tại nếu có
    if (autoUpdateInterval) {
        clearInterval(autoUpdateInterval);
    }
    
    // Tạo interval mới
    autoUpdateInterval = setInterval(() => {
        if (isAutoScrolling) {
            // Chỉ lấy dữ liệu mới nếu đang ở trang cuối
            const lastPageOffset = Math.floor((totalRecords - 1) / dataLimit) * dataLimit;
            if (currentOffset === lastPageOffset) {
                loadSpeedData(lastPageOffset);
            }
        }
    }, 3000); // 3 giây
}

// Khởi tạo khi DOM đã tải xong
document.addEventListener('DOMContentLoaded', function() {
    // Đảm bảo tất cả các thư viện đã được tải
    if (typeof Chart !== 'undefined' && typeof moment !== 'undefined') {
        // Khởi tạo biểu đồ tốc độ
        initSpeedChart();
        
        // Cập nhật trạng thái ban đầu của nút tự động cuộn
        updateAutoScrollButton();
        
        // Thiết lập polling dữ liệu
        setupDataPolling();
    } else {
        console.error('Không tìm thấy Chart.js hoặc moment.js. Đảm bảo thư viện đã được tải.');
    }
});

// Dọn dẹp khi trang được tải lại hoặc đóng
window.addEventListener('beforeunload', function() {
    if (autoUpdateInterval) {
        clearInterval(autoUpdateInterval);
    }
});