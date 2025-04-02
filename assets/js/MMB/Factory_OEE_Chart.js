let factoryOEEChart = null;

async function updateFactoryOEEChart(period) {
    try {
        const response = await fetch(`api/MMB/get_factory_oee_data.php?period=${period}`);
        const data = await response.json();
        
        // Tạo dữ liệu target line (89%)
        const targetData = new Array(data.dates.length).fill(89);

        // Tính toán độ rộng của bar dựa trên period
        const getBarThickness = (period) => {
            switch(period) {
                case 'today': return 35;
                case 'yesterday': return 50;
                case 'week': return 35;
                case 'last_week': return 35;
                case 'month': return 40;
                default: return 35;
            }
        };

        // Custom tooltip HTML
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
                tooltipEl.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
                tooltipEl.style.border = '1px solid rgba(0,0,0,0.1)';
        
                const table = document.createElement('table');
                table.style.margin = '0px';
        
                tooltipEl.appendChild(table);
                chart.canvas.parentNode.appendChild(tooltipEl);
            }
        
            return tooltipEl;
        };

        // Xóa chart cũ nếu tồn tại
        if (factoryOEEChart) {
            factoryOEEChart.destroy();
        }

        // Tạo chart mới
        const ctx = document.getElementById('factoryOEEChart').getContext('2d');
        factoryOEEChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.dates,
                datasets: [
                    {
                        label: 'OEE Xưởng F2',
                        data: data.f2_oee,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        barThickness: getBarThickness(period),
                        borderRadius: 4,
                        order: 2,
                        categoryPercentage: 0.85,
                        barPercentage: 0.9
                    },
                    {
                        label: 'OEE Xưởng F3',
                        data: data.f3_oee,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        barThickness: getBarThickness(period),
                        borderRadius: 4,
                        order: 2,
                        categoryPercentage: 0.85,
                        barPercentage: 0.9
                    },
                    {
                        label: 'OEE Xưởng F1 (CSD+FS)',
                        data: data.f1_oee,
                        backgroundColor: 'rgba(255, 159, 64, 0.7)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1,
                        barThickness: getBarThickness(period),
                        borderRadius: 4,
                        order: 2,
                        categoryPercentage: 0.85,
                        barPercentage: 0.9
                    },
                    {
                        label: 'Target (89%)',
                        data: targetData,
                        type: 'line',
                        borderColor: '#e74c3c',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false,
                        order: 0
                    },
                    {
                        label: 'OEE Toàn Nhà Máy',
                        data: data.factory_oee,
                        type: 'line',
                        borderColor: '#2ecc71',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#2ecc71',
                        fill: false,
                        order: 1
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
                            maxRotation: period === 'today' ? 45 : 0,
                            minRotation: period === 'today' ? 45 : 0,
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
                        enabled: false,
                        position: 'nearest',
                        external: function(context) {
                            const {chart, tooltip} = context;
                            const tooltipEl = getOrCreateTooltip(chart);

                            // Hide if no tooltip
                            if (tooltip.opacity === 0) {
                                tooltipEl.style.opacity = 0;
                                return;
                            }

                            // Set Text
                            if (tooltip.body) {
                                const titleLines = tooltip.title || [];
                                const bodyLines = tooltip.body.map(b => b.lines);
                                const dataIndex = tooltip.dataPoints[0].dataIndex;
                                
                                const f2Value = data.f2_oee[dataIndex];
                                const f3Value = data.f3_oee[dataIndex];
                                const f1Value = data.f1_oee[dataIndex];
                                const csdValue = data.csd_oee[dataIndex];
                                const fsValue = data.fs_oee[dataIndex];
                                const factoryValue = data.factory_oee[dataIndex];
                                const target = 89;
                                
                                const difference = (factoryValue - target).toFixed(1);
                                const diffColor = difference >= 0 ? '#4CAF50' : '#F44336';

                                const tableHead = document.createElement('thead');
                                tableHead.style.borderBottom = '2px solid #ddd';
                                
                                let tableHTML = `
                                    <tr>
                                        <th style="text-align: center; font-weight: 600; padding: 8px; font-size: 13px; color: #333;">
                                            ${titleLines[0]}
                                        </th>
                                    </tr>
                                `;
                                tableHead.innerHTML = tableHTML;

                                const tableBody = document.createElement('tbody');
                                tableHTML = `
                                    <tr>
                                        <td style="padding: 8px;">
                                            <div style="margin: 2px 0; font-size: 12px;">
                                                <span style="display: inline-block; width: 8px; height: 8px; background: #2ecc71; border-radius: 50%; margin-right: 8px;"></span>
                                                <span style="color: #666;">OEE Toàn Nhà Máy:</span>
                                                <span style="float: right; font-weight: 600; color: ${factoryValue >= 89 ? '#2ecc71' : '#F44336'}">${factoryValue.toFixed(1)}%</span>
                                            </div>
                                            <div style="margin: 2px 0; font-size: 12px;">
                                                <span style="display: inline-block; width: 8px; height: 8px; background: rgba(54, 162, 235, 1); border-radius: 50%; margin-right: 8px;"></span>
                                                <span style="color: #666;">Xưởng F2 (L1-L4):</span>
                                                <span style="float: right; font-weight: 600; color: ${f2Value >= 89 ? '#2ecc71' : '#F44336'}">${f2Value.toFixed(1)}%</span>
                                            </div>
                                            <div style="margin: 2px 0; font-size: 12px;">
                                                <span style="display: inline-block; width: 8px; height: 8px; background: rgba(75, 192, 192, 1); border-radius: 50%; margin-right: 8px;"></span>
                                                <span style="color: #666;">Xưởng F3 (L5-L8):</span>
                                                <span style="float: right; font-weight: 600; color: ${f3Value >= 89 ? '#2ecc71' : '#F44336'}">${f3Value.toFixed(1)}%</span>
                                            </div>
                                            <div style="margin: 2px 0; font-size: 12px;">
                                                <span style="display: inline-block; width: 8px; height: 8px; background: rgba(255, 159, 64, 1); border-radius: 50%; margin-right: 8px;"></span>
                                                <span style="color: #666;">Xưởng F1:</span>
                                                <span style="float: right; font-weight: 600; color: ${f1Value >= 89 ? '#2ecc71' : '#F44336'}">${f1Value.toFixed(1)}%</span>
                                            </div>
                                            <div style="margin: 2px 0; font-size: 12px; padding-left: 16px;">
                                                <span style="color: #666;">― CSD:</span>
                                                <span style="float: right; font-weight: 600; color: ${csdValue >= 89 ? '#2ecc71' : '#F44336'}">${csdValue.toFixed(1)}%</span>
                                            </div>
                                            <div style="margin: 2px 0; font-size: 12px; padding-left: 16px;">
                                                <span style="color: #666;">― FS:</span>
                                                <span style="float: right; font-weight: 600; color: ${fsValue >= 89 ? '#2ecc71' : '#F44336'}">${fsValue.toFixed(1)}%</span>
                                            </div>
                                            <div style="margin: 4px 0; font-size: 12px; border-top: 1px dashed #eee; padding-top: 4px; margin-top: 4px;">
                                                <span style="display: inline-block; width: 8px; height: 8px; background: #e74c3c; border-radius: 50%; margin-right: 8px;"></span>
                                                <span style="color: #666;">Target:</span>
                                                <span style="float: right; font-weight: 600; color: #e74c3c">89.0%</span>
                                            </div>
                                            <div style="margin: 2px 0; font-size: 12px; border-top: 1px solid #eee; padding-top: 4px; margin-top: 4px;">
                                                <span style="color: #666;">Chênh lệch:</span>
                                                <span style="float: right; font-weight: 600; color: ${diffColor}">${difference > 0 ? '+' : ''}${difference}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                tableBody.innerHTML = tableHTML;

                                const tableRoot = tooltipEl.querySelector('table');
                                // Clear previous tooltip content
                                while (tableRoot.firstChild) {
                                    tableRoot.firstChild.remove();
                                }

                                // Add new tooltip content
                                tableRoot.appendChild(tableHead);
                                tableRoot.appendChild(tableBody);
                            }

                            const {offsetLeft: positionX, offsetTop: positionY} = chart.canvas;

                            // Display, position, and set styles for font
                            tooltipEl.style.opacity = 1;
                            tooltipEl.style.left = positionX + tooltip.caretX + 'px';
                            tooltipEl.style.top = positionY + tooltip.caretY + 'px';
                            tooltipEl.style.padding = tooltip.options.padding + 'px ' + tooltip.options.padding + 'px';
                        }
                    },
                    datalabels: {
                        anchor: function(context) {
                            // For line chart (factory total) use 'center', for bars use 'end'
                            return context.dataset.type === 'line' ? 'center' : 'end';
                        },
                        align: function(context) {
                            // For line chart (factory total) use 'top', for bars use 'top'
                            return context.dataset.type === 'line' && context.dataset.label !== 'Target (89%)' ? 'bottom' : 'top';
                        },
                        formatter: function(value, context) {
                            // Don't show labels for Target line
                            if (context.dataset.label === 'Target (89%)') return '';
                            
                            // For factory OEE (the line chart with green color)
                            if (context.dataset.type === 'line' && context.dataset.label === 'OEE Toàn Nhà Máy') {
                                return value.toFixed(1) + '%';
                            }
                            
                            // For bars, only show if value is significant
                            if (context.dataset.type === 'bar' && value > 0) {
                                return value.toFixed(1) + '%';
                            }
                            
                            return '';
                        },
                        color: function(context) {
                            const value = context.dataset.data[context.dataIndex];
                            
                            // For factory OEE (line chart)
                            if (context.dataset.type === 'line' && context.dataset.label === 'OEE Toàn Nhà Máy') {
                                return value >= 89 ? '#27ae60' : '#c0392b';
                            }
                            
                            // For bars
                            return value >= 89 ? '#27ae60' : '#c0392b';
                        },
                        font: {
                            weight: 'bold',
                            size: 11
                        },
                        padding: {
                            top: 4,
                            bottom: 4
                        },
                        offset: 2
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        console.log('Factory OEE chart updated successfully');
    } catch (error) {
        console.error('Error updating Factory OEE chart:', error);
    }
}

// Khởi tạo chart
function initFactoryOEEChart() {
    console.log('Initializing Factory OEE chart...');
    updateFactoryOEEChart('today');
}

// Hàm cập nhật Factory OEE Chart khi đổi period
function updateFactoryChartByPeriod(period) {
    updateFactoryOEEChart(period);
}

// Gắn sự kiện cho các nút period nếu đang ở trang factory và chưa được gắn
document.addEventListener('DOMContentLoaded', function() {
    // Kiểm tra nếu chưa có sự kiện và đang ở trang factory
    const periodButtons = document.querySelectorAll('.date-filter .btn');
    
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            const period = this.dataset.period;
            
            // Cập nhật biểu đồ với period mới
            if (factoryOEEChart) {
                updateFactoryOEEChart(period);
            }
        });
    });
    
    // Khởi tạo chart
    initFactoryOEEChart();
});