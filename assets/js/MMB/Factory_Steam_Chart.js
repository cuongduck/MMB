let factorySteamChart = null;

async function updateFactorySteamChart(period) {
    try {
        const response = await fetch(`api/MMB/get_factory_steam_data.php?period=${period}`);
        const data = await response.json();
        
        if (!data.success) {
            console.error('Error loading factory steam data:', data.message);
            return;
        }

        // Xóa chart cũ nếu tồn tại
        if (factorySteamChart) {
            factorySteamChart.destroy();
        }

        const ctx = document.getElementById('factorySteamChart').getContext('2d');
        factorySteamChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.dates,
                datasets: [
                    {
                        label: 'Hơi F2',
                        data: data.f2_steam,
                        borderColor: '#3498db', // Xanh lam
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#3498db',
                        fill: true,
                        tension: 0.4,
                        order: 1
                    },
                    {
                        label: 'Hơi F3',
                        data: data.f3_steam,
                        borderColor: '#2ecc71', // Xanh lá
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#2ecc71',
                        fill: true,
                        tension: 0.4,
                        order: 2
                    },
                    {
                        label: 'Hơi CSD',
                        data: data.csd_steam,
                        borderColor: '#e74c3c', // Đỏ
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#e74c3c',
                        fill: true,
                        tension: 0.4,
                        order: 3
                    },
                    {
                        label: 'Hơi FS',
                        data: data.fs_steam,
                        borderColor: '#f39c12', // Cam
                        backgroundColor: 'rgba(243, 156, 18, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#f39c12',
                        fill: true,
                        tension: 0.4,
                        order: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Hơi/1000SP'
                        },
                        grid: {
                            drawOnChartArea: true
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('en-US', { maximumFractionDigits: 2 });
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
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
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#333',
                        titleFont: {
                            size: 12,
                            weight: 'bold'
                        },
                        bodyColor: '#666',
                        bodyFont: {
                            size: 11
                        },
                        padding: 10,
                        displayColors: true,
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                let value = context.parsed.y || 0;
                                return `${label}: ${value.toLocaleString('en-US', { maximumFractionDigits: 2 })}`;
                            }
                        }
                    }
                }
            }
        });

        console.log('Factory steam chart updated successfully');
    } catch (error) {
        console.error('Error updating factory steam chart:', error);
    }
}

// Khởi tạo chart
function initFactorySteamChart() {
    console.log('Initializing factory steam chart...');
    updateFactorySteamChart('today');
}

// Hàm cập nhật Factory Steam Chart khi đổi period
function updateFactorySteamChartByPeriod(period) {
    updateFactorySteamChart(period);
}

// Gắn sự kiện cho các nút period
document.addEventListener('DOMContentLoaded', function() {
    const periodButtons = document.querySelectorAll('.date-filter .btn');
    
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            const period = this.dataset.period;
            
            // Cập nhật biểu đồ với period mới
            if (factorySteamChart) {
                updateFactorySteamChart(period);
            }
        });
    });
    
    // Khởi tạo chart
    initFactorySteamChart();
});