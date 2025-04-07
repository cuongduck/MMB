let powerLineChart = null;

async function updatePowerLineChart(period) {
    try {
        console.log('Đang cập nhật biểu đồ đường điện năng với period:', period);
        const response = await fetch(`api/MMB/get_power_trend.php?period=${period}`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        console.log('Dữ liệu biểu đồ đường điện năng:', data);

        const colors = [
            'rgb(54, 162, 235)',   // Xưởng F2
            'rgb(75, 192, 192)',   // Hồn Chuẩn
            'rgb(255, 206, 86)',   // F1 Mắm
            'rgb(153, 102, 255)',  // F1 CSD
            'rgb(255, 159, 64)',   // DNP
            'rgb(255, 99, 132)',   // F3 Xưởng
            'rgb(199, 199, 199)',  // Trí Việt
            'rgb(83, 102, 255)'    // Utility
        ];

        // Chuẩn bị dữ liệu cho datasets
        const datasets = [];
        const labelsMapping = {
            'Xuong_F2': 'Xưởng F2',
            'Hon_chuan': 'Hồn Chuẩn',
            'F1_Mam': 'F1 Mắm',
            'F1_CSD': 'F1 CSD',
            'DNP': 'DNP',
            'F3_Xuong': 'F3 Xưởng',
            'Tri_Viet': 'Trí Việt',
            'Utility': 'Utility'
        };

        // Tạo datasets cho từng loại dữ liệu
        const keys = ['Xuong_F2', 'Hon_chuan', 'F1_Mam', 'F1_CSD', 'DNP', 'F3_Xuong', 'Tri_Viet', 'Utility'];
        
        for (let i = 0; i < keys.length; i++) {
            const key = keys[i];
            const label = labelsMapping[key];
            
            // Tính tổng của dataset này để sắp xếp theo tầm quan trọng
            const values = data.datasets.map(d => d[key]);
            const total = values.reduce((a, b) => a + b, 0);
            
            datasets.push({
                label: label,
                data: values,
                total: total,
                borderColor: colors[i],
                backgroundColor: colors[i],
                fill: false,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 2
            });
        }
        
        // Sắp xếp datasets theo tổng giảm dần
        datasets.sort((a, b) => b.total - a.total);

        // Chuẩn bị nhãn thời gian cho trục x
        const timeLabels = data.datasets.map(d => {
            const time = new Date(d.time);
            if (period === 'today' || period === 'yesterday') {
                return time.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
            } else {
                return time.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
            }
        });

        // Xóa chart cũ nếu tồn tại
        if (powerLineChart) {
            powerLineChart.destroy();
        }

        const ctx = document.getElementById('powerLineChart').getContext('2d');
        powerLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: timeLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 11
                            },
                            boxWidth: 15,
                            generateLabels: function(chart) {
                                const datasets = chart.data.datasets;
                                // Tạo labels và giữ thứ tự đã sắp xếp trước đó
                                return datasets.map((dataset, i) => ({
                                    text: `${dataset.label} (${dataset.total.toFixed(1)} kW)`,
                                    fillStyle: dataset.borderColor,
                                    strokeStyle: dataset.borderColor,
                                    lineWidth: 2,
                                    hidden: !chart.isDatasetVisible(i),
                                    index: i,
                                    datasetIndex: i,
                                }));
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toFixed(1) + ' kW';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Điện năng (kW)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });

        console.log('Cập nhật biểu đồ đường điện năng thành công');
    } catch (error) {
        console.error('Lỗi khi cập nhật biểu đồ đường điện năng:', error);
    }
}

// Khởi tạo biểu đồ
function initPowerLineChart() {
    console.log('Đang khởi tạo biểu đồ đường điện năng...');
    updatePowerLineChart('today');
}