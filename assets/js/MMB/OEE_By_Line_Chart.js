let oeeByLineChart = null;

async function updateOEEByLineChart(period) {
    try {
        // Lấy dữ liệu từ F2
        const f2Response = await fetch(`api/F2/get_oee_data.php?period=${period}`);
        const f2Data = await f2Response.json();
        
        // Lấy dữ liệu từ F3
        const f3Response = await fetch(`api/F3/get_oee_data.php?period=${period}`);
        const f3Data = await f3Response.json();
        
        // Lấy dữ liệu từ CSD
        const csdResponse = await fetch(`api/CSD/get_oee_data.php?period=${period}`);
        const csdData = await csdResponse.json();
        
        // Lấy dữ liệu từ FS
        const fsResponse = await fetch(`api/FS/get_oee_data.php?period=${period}`);
        const fsData = await fsResponse.json();
        
        // Xóa chart cũ nếu tồn tại
        if (oeeByLineChart) {
            oeeByLineChart.destroy();
        }
        
        // Tạo dữ liệu cho chart
        const lineLabels = ['L1', 'L2', 'L3', 'L4', 'L5', 'L6', 'L7', 'L8', 'CSD', 'FS'];
        
        // Lấy dữ liệu OEE mới nhất của từng line
        const lineOEEValues = [];
        
        // F2 (Line 1-4)
        if (f2Data.line1OEE && f2Data.line1OEE.length > 0) lineOEEValues.push(f2Data.line1OEE[0]);
        else lineOEEValues.push(0);
        
        if (f2Data.line2OEE && f2Data.line2OEE.length > 0) lineOEEValues.push(f2Data.line2OEE[0]);
        else lineOEEValues.push(0);
        
        if (f2Data.line3OEE && f2Data.line3OEE.length > 0) lineOEEValues.push(f2Data.line3OEE[0]);
        else lineOEEValues.push(0);
        
        if (f2Data.line4OEE && f2Data.line4OEE.length > 0) lineOEEValues.push(f2Data.line4OEE[0]);
        else lineOEEValues.push(0);
        
        // F3 (Line 5-8)
        if (f3Data.line5OEE && f3Data.line5OEE.length > 0) lineOEEValues.push(f3Data.line5OEE[0]);
        else lineOEEValues.push(0);
        
        if (f3Data.line6OEE && f3Data.line6OEE.length > 0) lineOEEValues.push(f3Data.line6OEE[0]);
        else lineOEEValues.push(0);
        
        if (f3Data.line7OEE && f3Data.line7OEE.length > 0) lineOEEValues.push(f3Data.line7OEE[0]);
        else lineOEEValues.push(0);
        
        if (f3Data.line8OEE && f3Data.line8OEE.length > 0) lineOEEValues.push(f3Data.line8OEE[0]);
        else lineOEEValues.push(0);
        
        // F1 (CSD, FS)
        if (csdData.values && csdData.values.length > 0) lineOEEValues.push(csdData.values[0]);
        else lineOEEValues.push(0);
        
        if (fsData.values && fsData.values.length > 0) lineOEEValues.push(fsData.values[0]);
        else lineOEEValues.push(0);
        
        // Tạo mảng màu sắc dựa trên giá trị OEE
        const barColors = lineOEEValues.map(value => value >= 89 ? 'rgba(46, 204, 113, 0.8)' : 'rgba(231, 76, 60, 0.8)');
        const borderColors = lineOEEValues.map(value => value >= 89 ? 'rgb(39, 174, 96)' : 'rgb(192, 57, 43)');
        
        // Tạo chart mới
        const ctx = document.getElementById('oeeByLineChart').getContext('2d');
        oeeByLineChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: lineLabels,
                datasets: [
                    {
                        label: 'OEE Line (%)',
                        data: lineOEEValues,
                        backgroundColor: barColors,
                        borderColor: borderColors,
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Target (89%)',
                        data: new Array(lineLabels.length).fill(89),
                        type: 'line',
                        borderColor: '#e74c3c',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                if (context.dataset.type === 'line') {
                                    return 'Target: 89%';
                                }
                                return `OEE: ${value.toFixed(1)}%`;
                            },
                            footer: function(tooltipItems) {
                                const value = tooltipItems[0].raw;
                                const difference = (value - 89).toFixed(1);
                                return `Chênh lệch: ${difference > 0 ? '+' : ''}${difference}%`;
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: function(value, context) {
                            if (context.dataset.type === 'line') return '';
                            return value.toFixed(1) + '%';
                        },
                        color: function(context) {
                            const value = context.dataset.data[context.dataIndex];
                            return value >= 89 ? '#27ae60' : '#c0392b';
                        },
                        font: {
                            weight: 'bold',
                            size: 11
                        },
                        padding: {
                            top: 4
                        },
                        offset: 2
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        console.log('OEE by Line chart updated successfully');
    } catch (error) {
        console.error('Error updating OEE by Line chart:', error);
    }
}

// Khởi tạo chart
function initOEEByLineChart() {
    console.log('Initializing OEE by Line chart...');
    updateOEEByLineChart('today');
}

// Hàm cập nhật OEE by Line Chart khi đổi period
function updateOEEbyLineChartByPeriod(period) {
    updateOEEByLineChart(period);
}

// Gắn sự kiện cho các nút period
document.addEventListener('DOMContentLoaded', function() {
    // Kiểm tra nếu chưa có sự kiện và đang ở trang factory
    const periodButtons = document.querySelectorAll('.date-filter .btn');
    
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            const period = this.dataset.period;
            
            // Cập nhật biểu đồ với period mới
            if (oeeByLineChart) {
                updateOEEByLineChart(period);
            }
        });
    });
    
    // Khởi tạo chart
    initOEEByLineChart();
});