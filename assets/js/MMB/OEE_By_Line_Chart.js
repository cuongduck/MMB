// Biến lưu trữ biểu đồ OEE theo line
let oeeByLineChart = null;

// Hàm cập nhật biểu đồ OEE theo line
async function updateOEEByLineChart(period = 'today') {
    try {
        console.log('Updating OEE by line chart with period:', period);
        
        // Sử dụng API mới để lấy OEE của tất cả các line
        const response = await fetch(`api/MMB/get_all_lines_oee.php?period=${period}`);
        const data = await response.json();
        
        if (data.error) {
            console.error('Error fetching OEE data:', data.message);
            return;
        }
        
        console.log('OEE data for all lines:', data);
        
        const lines = data.lines || [];
        const values = data.values || [];
        
        // Target OEE là 89%
        const targetData = Array(lines.length).fill(89);
        
        // Xóa biểu đồ cũ nếu đã tồn tại
        if (oeeByLineChart) {
            oeeByLineChart.destroy();
        }
        
        // Tạo biểu đồ mới
        const ctx = document.getElementById('oeeByLineChart').getContext('2d');
        
        // Custom tooltip function
        const getOrCreateTooltip = (chart) => {
            let tooltipEl = chart.canvas.parentNode.querySelector('div');
            
            if (!tooltipEl) {
                tooltipEl = document.createElement('div');
                tooltipEl.style.background = 'rgba(255, 255, 255, 0.95)';
                tooltipEl.style.borderRadius = '3px';
                tooltipEl.style.color = 'black';
                tooltipEl.style.opacity = 1;
                tooltipEl.style.pointerEvents = 'none';
                tooltipEl.style.position = 'absolute';
                tooltipEl.style.transform = 'translate(-50%, 0)';
                tooltipEl.style.transition = 'all .1s ease';
                tooltipEl.style.zIndex = '98';
                tooltipEl.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
                tooltipEl.style.border = '1px solid rgba(0,0,0,0.1)';
                
                const table = document.createElement('table');
                table.style.margin = '0px';
                
                tooltipEl.appendChild(table);
                chart.canvas.parentNode.appendChild(tooltipEl);
            }
            
            return tooltipEl;
        };
        
        // Tạo mảng màu cho các bar
        const barColors = values.map((value, index) => {
            // Chỉ sử dụng 2 màu: xanh cho OEE >= 89%, đỏ cho OEE < 89%
            if (value >= 89) {
                return 'rgba(52, 152, 219, 0.8)'; // Xanh lam cho OEE >= 89%
            } else {
                return 'rgba(231, 76, 60, 0.8)'; // Đỏ cho OEE < 89%
            }
        });
        
        // Sắp xếp dữ liệu giảm dần theo giá trị OEE
        const combinedData = lines.map((line, index) => ({
            line: line,
            value: values[index]
        }));
        
        combinedData.sort((a, b) => b.value - a.value);
        
        const sortedLines = combinedData.map(item => item.line);
        const sortedValues = combinedData.map(item => item.value);
        const sortedColors = combinedData.map(item => {
            if (item.value >= 89) {
                return 'rgba(52, 152, 219, 0.8)'; // Xanh lam cho OEE >= 89%
            } else {
                return 'rgba(231, 76, 60, 0.8)'; // Đỏ cho OEE < 89%
            }
        });
        
        // Tạo biểu đồ
        oeeByLineChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: sortedLines,
                datasets: [
                    {
                        label: 'OEE Line',
                        data: sortedValues,
                        backgroundColor: sortedColors,
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1,
                        borderRadius: 5,
                        barThickness: 40,
                        maxBarThickness: 60
                    },
                    {
                        label: 'Target (89%)',
                        data: Array(sortedLines.length).fill(89),
                        type: 'line',
                        borderColor: '#e74c3c',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false,
                        order: 0,
                        datalabels: {
                            display: false // Ẩn nhãn cho đường target
                        }
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100, // Giới hạn trục y đến 100%
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            },
                            font: {
                                size: 11
                            }
                        },
                        title: {
                            display: true,
                            text: 'OEE (%)',
                            font: {
                                size: 12
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
                        display: true,
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        enabled: false,
                        position: 'nearest',
                        external: function(context) {
                            const {chart, tooltip} = context;
                            const tooltipEl = getOrCreateTooltip(chart);

                            // Ẩn tooltip nếu không có dữ liệu
                            if (tooltip.opacity === 0) {
                                tooltipEl.style.opacity = 0;
                                return;
                            }

                            // Thiết lập nội dung
                            if (tooltip.body) {
                                const titleLines = tooltip.title || [];
                                const bodyLines = tooltip.body.map(b => b.lines);
                                const dataIndex = tooltip.dataPoints[0].dataIndex;
                                
                                const lineValue = sortedValues[dataIndex];
                                const targetValue = 89;
                                const difference = (lineValue - targetValue).toFixed(1);
                                const diffColor = difference >= 0 ? '#2ecc71' : '#e74c3c';

                                // Tạo header tooltip
                                const tableHead = document.createElement('thead');
                                let tableHTML = `
                                    <tr>
                                        <th style="text-align: center; font-weight: 600; padding: 8px; font-size: 13px; color: #333;">
                                            ${titleLines[0]}
                                        </th>
                                    </tr>
                                `;
                                tableHead.innerHTML = tableHTML;

                                // Tạo nội dung tooltip
                                const tableBody = document.createElement('tbody');
                                tableHTML = `
                                    <tr>
                                        <td style="padding: 8px;">
                                            <div style="margin: 2px 0; font-size: 13px;">
                                                <span style="display: inline-block; width: 8px; height: 8px; background: ${sortedColors[dataIndex]}; border-radius: 50%; margin-right: 8px;"></span>
                                                <span style="color: #666;">OEE:</span>
                                                <span style="float: right; font-weight: 600; color: ${lineValue >= 89 ? 'rgba(52, 152, 219, 1)' : 'rgba(231, 76, 60, 1)'}">${lineValue.toFixed(1)}%</span>
                                            </div>
                                            <div style="margin: 2px 0; font-size: 13px;">
                                                <span style="display: inline-block; width: 8px; height: 8px; background: #e74c3c; border-radius: 50%; margin-right: 8px;"></span>
                                                <span style="color: #666;">Target:</span>
                                                <span style="float: right; font-weight: 600; color: #e74c3c">89.0%</span>
                                            </div>
                                            <div style="margin: 2px 0; font-size: 13px; border-top: 1px solid #eee; padding-top: 4px; margin-top: 4px;">
                                                <span style="color: #666;">Chênh lệch:</span>
                                                <span style="float: right; font-weight: 600; color: ${diffColor}">${difference > 0 ? '+' : ''}${difference}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                tableBody.innerHTML = tableHTML;

                                const tableRoot = tooltipEl.querySelector('table');
                                // Xóa nội dung tooltip cũ
                                while (tableRoot.firstChild) {
                                    tableRoot.firstChild.remove();
                                }

                                // Thêm nội dung tooltip mới
                                tableRoot.appendChild(tableHead);
                                tableRoot.appendChild(tableBody);
                            }

                            const {offsetLeft: positionX, offsetTop: positionY} = chart.canvas;

                            // Hiển thị, vị trí và thiết lập styles
                            tooltipEl.style.opacity = 1;
                            tooltipEl.style.left = positionX + tooltip.caretX + 'px';
                            tooltipEl.style.top = positionY + tooltip.caretY + 'px';
                            tooltipEl.style.padding = tooltip.options.padding + 'px ' + tooltip.options.padding + 'px';
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: function(value) {
                            return value.toFixed(1) + '%';
                        },
                        color: function(context) {
                            const index = context.dataIndex;
                            const value = context.dataset.data[index];
                            return value >= 89 ? 'rgba(52, 152, 219, 1)' : 'rgba(231, 76, 60, 1)';
                        },
                        font: {
                            weight: 'bold',
                            size: 11
                        },
                        offset: 2
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
        
        console.log('OEE by line chart updated successfully');
    } catch (error) {
        console.error('Error updating OEE by line chart:', error);
    }
}

// Hàm khởi tạo biểu đồ với period mặc định là 'today'
function initOEEByLineChart() {
    updateOEEByLineChart('today');
}

// Gán sự kiện cho các nút chọn period
document.addEventListener('DOMContentLoaded', function() {
    const periodButtons = document.querySelectorAll('.date-filter .btn');
    
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            const period = this.dataset.period;
            
            // Cập nhật biểu đồ với period mới
            if (typeof updateOEEByLineChart === 'function') {
                updateOEEByLineChart(period);
            }
        });
    });
    
    // Khởi tạo biểu đồ
    initOEEByLineChart();
});