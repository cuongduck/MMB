let steamChart = null;

async function updateSteamChart(period) {
    try {
        const response = await fetch(`api/F2/get_steam_data.php?period=${period}`);
        const data = await response.json();

        if (steamChart) {
            steamChart.destroy();
        }

        const ctx = document.getElementById('steamChart').getContext('2d');
        steamChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.dates,
                datasets: [
                    {
                        label: 'Hơi F2',
                        data: data.steamPerProduct,
                        borderColor: '#9c27b0',
                        backgroundColor: 'rgba(156, 39, 176, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#9c27b0',
                        fill: true,
                        tension: 0.4,
                        order: 1
                    },
                    {
                        label: 'Hơi Line 1',
                        data: data.line1SteamPerProduct,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                        tension: 0.4,
                        order: 2
                    },
                    {
                        label: 'Hơi Line 2',
                        data: data.line2SteamPerProduct,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                        tension: 0.4,
                        order: 3
                    },
                    {
                        label: 'Hơi Line 3',
                        data: data.line3SteamPerProduct,
                        borderColor: 'rgb(255, 159, 64)',
                        backgroundColor: 'rgba(255, 159, 64, 0.1)',
                        borderWidth: 2,
                        pointRadius: 3,
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

        console.log('Steam chart updated successfully');
    } catch (error) {
        console.error('Error updating Steam chart:', error);
    }
}

// Khởi tạo chart
function initSteamChart() {
    console.log('Initializing Steam chart...');
    updateSteamChart('today');
}