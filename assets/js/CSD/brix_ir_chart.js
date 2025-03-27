// Biến lưu đối tượng biểu đồ
let brixChart = null;

// Hàm cập nhật dữ liệu biểu đồ BRIX
async function updateBrixChart() {
    try {
        const response = await fetch('api/CSD/get_brix_data.php');
        const data = await response.json();
        
        // Tạo hoặc cập nhật biểu đồ
        renderBrixChart(data);
        
        console.log('Biểu đồ BRIX đã được cập nhật');
    } catch (error) {
        console.error('Lỗi khi cập nhật biểu đồ BRIX:', error);
    }
}

// Hàm tạo và cập nhật biểu đồ BRIX dạng IR
function renderBrixChart(data) {
    const ctx = document.getElementById('brixChart').getContext('2d');
    
    // Tạo dữ liệu cho đường target
    const targetData = new Array(data.times.length).fill(data.brix.target);
    
    // Tạo dữ liệu giới hạn trên/dưới
    const upperLimitData = new Array(data.times.length).fill(data.brix.upperLimit);
    const lowerLimitData = new Array(data.times.length).fill(data.brix.lowerLimit);
    
    // Hủy biểu đồ cũ nếu đã tồn tại
    if (brixChart) {
        brixChart.destroy();
    }
    
    // Tạo biểu đồ mới kiểu IR Spectroscopy
    brixChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.times,
            datasets: [
                {
                    label: 'BRIX (°)',
                    data: data.brix.values,
                    borderColor: 'rgb(147, 51, 234)', // Tím
                    backgroundColor: 'rgba(147, 51, 234, 0.2)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0, // Không hiển thị điểm để tạo đường liền mạch
                    pointHoverRadius: 4 // Hiển thị điểm khi hover
                },
                {
                    label: 'Target',
                    data: targetData,
                    borderColor: 'rgb(59, 130, 246)', // Xanh dương
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    pointRadius: 0
                },
                {
                    label: 'Giới hạn trên',
                    data: upperLimitData,
                    borderColor: 'rgba(239, 68, 68, 0.7)', // Đỏ
                    borderWidth: 1,
                    borderDash: [3, 3],
                    fill: false,
                    pointRadius: 0
                },
                {
                    label: 'Giới hạn dưới',
                    data: lowerLimitData,
                    borderColor: 'rgba(239, 68, 68, 0.7)', // Đỏ
                    borderWidth: 1,
                    borderDash: [3, 3],
                    fill: false,
                    pointRadius: 0
                },
                // Thêm vùng tô màu giữa hai giới hạn
                {
                    label: 'Khoảng cho phép',
                    data: lowerLimitData,
                    borderColor: 'transparent',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)', // Xanh dương nhạt
                    fill: '+1', // Tô màu giữa dataset này và dataset tiếp theo
                    pointRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 15,
                        usePointStyle: true,
                        padding: 15,
                        filter: function(item, chart) {
                            // Ẩn "Khoảng cho phép" trong legend
                            return item.text !== 'Khoảng cho phép';
                        }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === 'Khoảng cho phép') return null;
                            
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                // Chỉ hiển thị 1 chữ số thập phân cho giá trị BRIX
                                label += context.parsed.y.toFixed(1) + '°';
                                
                                // Thêm thông tin về trạng thái
                                if (label.includes('BRIX')) {
                                    const value = context.parsed.y;
                                    if (value < data.brix.lowerLimit) {
                                        label += ' (Thấp)';
                                    } else if (value > data.brix.upperLimit) {
                                        label += ' (Cao)';
                                    } else {
                                        label += ' (OK)';
                                    }
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 10
                        },
                        autoSkip: true,
                        maxTicksLimit: 20 // Giới hạn số lượng nhãn trục x để tránh quá chật
                    }
                },
                y: {
                    min: Math.min(data.brix.min - 0.1, data.brix.lowerLimit - 0.2),
                    max: Math.max(data.brix.max + 0.1, data.brix.upperLimit + 0.2),
                    title: {
                        display: true,
                        text: 'Nồng độ BRIX (°)'
                    },
                    ticks: {
                        stepSize: 0.1,
                        callback: function(value) {
                            return value.toFixed(1) + '°';
                        }
                    },
                    grid: {
                        color: function(context) {
                            if (context.tick.value === data.brix.lowerLimit || 
                                context.tick.value === data.brix.upperLimit ||
                                context.tick.value === data.brix.target) {
                                return 'rgba(0, 0, 0, 0.1)';
                            }
                            return 'rgba(0, 0, 0, 0.05)';
                        }
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            },
            animations: {
                radius: {
                    duration: 400,
                    easing: 'linear',
                    loop: (context) => context.active
                }
            },
            elements: {
                line: {
                    tension: 0.4
                }
            }
        }
    });
    
    // Thêm đánh dấu trực quan cho các giá trị ngoài khoảng cho phép
    highlightOutOfRangeValues(data);
}

// Hàm đánh dấu các giá trị ngoài khoảng cho phép
function highlightOutOfRangeValues(data) {
    const container = document.getElementById('brixChart').parentNode;
    
    // Xóa các đánh dấu cũ nếu có
    const oldMarkers = container.querySelectorAll('.out-of-range-marker');
    oldMarkers.forEach(marker => marker.remove());
    
    // Tính toán kích thước của canvas
    const canvas = document.getElementById('brixChart');
    const rect = canvas.getBoundingClientRect();
    
    // Chỉ đánh dấu 20 giá trị gần đây nhất để tránh làm chật giao diện
    const recentValues = data.brix.values.slice(-20);
    const recentTimes = data.times.slice(-20);
    
    // Tạo đánh dấu cho các giá trị ngoài khoảng
    recentValues.forEach((value, index) => {
        if (value < data.brix.lowerLimit || value > data.brix.upperLimit) {
            const marker = document.createElement('div');
            marker.classList.add('out-of-range-marker');
            
            // Tính toán vị trí cho đánh dấu
            const xPos = (index + 1) * (rect.width / recentValues.length);
            const yPos = rect.height / 2; // Vị trí giữa biểu đồ
            
            // Thiết lập style cho marker
            marker.style.position = 'absolute';
            marker.style.left = `${xPos}px`;
            marker.style.top = `${yPos}px`;
            marker.style.width = '8px';
            marker.style.height = '8px';
            marker.style.borderRadius = '50%';
            marker.style.backgroundColor = value < data.brix.lowerLimit ? 'rgba(59, 130, 246, 0.8)' : 'rgba(239, 68, 68, 0.8)';
            marker.style.transform = 'translate(-50%, -50%)';
            marker.style.zIndex = '10';
            
            // Thêm tooltip cho marker
            marker.title = `Thời gian: ${recentTimes[index]}, BRIX: ${value.toFixed(1)}% (${value < data.brix.lowerLimit ? 'Thấp' : 'Cao'})`;
            
            container.appendChild(marker);
        }
    });
}

// Khởi tạo biểu đồ khi trang tải xong
document.addEventListener('DOMContentLoaded', () => {
    // Thêm style cho out-of-range markers nếu chưa được thêm
    if (!document.querySelector('#ir-chart-styles')) {
        const style = document.createElement('style');
        style.id = 'ir-chart-styles';
        style.textContent = `
            .out-of-range-marker {
                animation: pulse 1.5s infinite;
            }
            @keyframes pulse {
                0% { transform: translate(-50%, -50%) scale(1); opacity: 0.8; }
                50% { transform: translate(-50%, -50%) scale(1.5); opacity: 0.5; }
                100% { transform: translate(-50%, -50%) scale(1); opacity: 0.8; }
            }
        `;
        document.head.appendChild(style);
    }
    
    updateBrixChart();
    
    // Cập nhật biểu đồ mỗi 30 giây
    setInterval(() => {
        updateBrixChart();
    }, 30000);
});