let powerDonutChart = null;

async function updatePowerDonutChart(period) {
    try {
        console.log('Đang cập nhật biểu đồ tròn điện năng với period:', period);
        const response = await fetch(`api/MMB/get_power_data.php?period=${period}`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const rawData = await response.json();
        console.log('Dữ liệu biểu đồ tròn điện năng:', rawData);

        // Xóa biểu đồ cũ nếu tồn tại
        if (powerDonutChart) {
            powerDonutChart.destroy();
        }

        // Màu sắc cho từng khu vực
        const backgroundColors = [
            'rgba(54, 162, 235, 0.8)',   // Xưởng F2
            'rgba(75, 192, 192, 0.8)',   // Hồn Chuẩn
            'rgba(255, 206, 86, 0.8)',   // F1 Mắm
            'rgba(153, 102, 255, 0.8)',  // F1 CSD
            'rgba(255, 159, 64, 0.8)',   // DNP
            'rgba(255, 99, 132, 0.8)',   // F3 Xưởng
            'rgba(199, 199, 199, 0.8)',  // Trí Việt
            'rgba(83, 102, 255, 0.8)'    // Utility
        ];

        // Tạo dữ liệu đã kết hợp và sắp xếp
        let combinedData = [];
        for (let i = 0; i < rawData.labels.length; i++) {
            combinedData.push({
                label: rawData.labels[i],
                value: rawData.values[i],
                percentage: rawData.percentages[i],
                color: backgroundColors[i]
            });
        }

        // Sắp xếp theo phần trăm giảm dần
        combinedData.sort((a, b) => b.percentage - a.percentage);

        // Lấy dữ liệu đã sắp xếp
        const labels = combinedData.map(item => item.label);
        const values = combinedData.map(item => item.value);
        const colors = combinedData.map(item => item.color);
        const percentages = combinedData.map(item => item.percentage);

        console.log('Labels đã sắp xếp:', labels);

        // Tạo custom labels để thêm phần trăm
        const customLabels = labels.map((label, i) => `${label} (${percentages[i]}%)`);

        const ctx = document.getElementById('powerDonutChart').getContext('2d');
        powerDonutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: customLabels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderColor: colors.map(color => color.replace('0.8', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 20,
                        bottom: 20
                    }
                },
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 11
                            },
                            padding: 10
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const percentage = percentages[context.dataIndex];
                                return [
                                    context.label,
                                    `Giá trị: ${value.toFixed(1)} kW`,
                                    `Phần trăm: ${percentage}%`
                                ];
                            }
                        }
                    },
                    datalabels: {
                        formatter: (value, ctx) => {
                            const percentage = percentages[ctx.dataIndex];
                            return percentage > 5 ? percentage + '%' : '';
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 11
                        },
                        display: function(context) {
                            return context.dataset.data[context.dataIndex] > 0;
                        }
                    }
                },
                cutout: '60%'
            },
            plugins: [{
                id: 'centerText',
                beforeDraw: function(chart) {
                    const width = chart.width;
                    const height = chart.height;
                    const ctx = chart.ctx;
                    
                    ctx.restore();
                    
                    // Tính toán vị trí chính giữa của vòng tròn
                    const chartArea = chart.chartArea;
                    const centerX = (chartArea.left + chartArea.right) / 2;
                    const centerY = (chartArea.top + chartArea.bottom) / 2;
                    
                    // Vẽ tổng số
                    ctx.font = 'bold 24px Arial';
                    ctx.textBaseline = 'middle';
                    ctx.textAlign = 'center';
                    ctx.fillStyle = '#333';
                    ctx.fillText(rawData.total.toFixed(1), centerX, centerY - 10);
                    
                    // Vẽ text "kW" phía dưới
                    ctx.font = '14px Arial';
                    ctx.fillText('kW', centerX, centerY + 15);
                    
                    ctx.save();
                }
            }, ChartDataLabels]
        });

        console.log('Cập nhật biểu đồ điện năng thành công');
    } catch (error) {
        console.error('Lỗi khi cập nhật biểu đồ điện năng:', error);
    }
}

// Khởi tạo biểu đồ
function initPowerDonutChart() {
    console.log('Đang khởi tạo biểu đồ tròn điện năng...');
    updatePowerDonutChart('today');
}