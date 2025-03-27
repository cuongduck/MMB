let steamUsageChart = null;

async function updateSteamUsageChart(period) {
    try {
        const response = await fetch(`api/F2/get_steam_data.php?period=${period}`);
        const data = await response.json();

        if (steamUsageChart) {
            steamUsageChart.destroy();
        }

        const ctx = document.getElementById('steamUsageChart').getContext('2d');
        steamUsageChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.dates,
                datasets: [
                    {
                        label: 'Hấp Line 1',
                        data: data.line1Hap,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',  // Xanh dương
                        borderColor: 'rgb(54, 162, 235)',
                        borderWidth: 1,
                        stack: 'Stack 0'
                    },
                    {
                        label: 'Chiên Line 1',
                        data: data.line1Chien,
                        backgroundColor: 'rgba(75, 192, 192, 0.8)',  // Xanh lá nhạt
                        borderColor: 'rgb(75, 192, 192)',
                        borderWidth: 1,
                        stack: 'Stack 0'
                    },
                    {
                        label: 'Hấp Line 2',
                        data: data.line2Hap,
                        backgroundColor: 'rgba(255, 99, 132, 0.8)',  // Hồng
                        borderColor: 'rgb(255, 99, 132)',
                        borderWidth: 1,
                        stack: 'Stack 0'
                    },
                    {
                        label: 'Chiên Line 2',
                        data: data.line2Chien,
                        backgroundColor: 'rgba(255, 159, 64, 0.8)',  // Cam
                        borderColor: 'rgb(255, 159, 64)',
                        borderWidth: 1,
                        stack: 'Stack 0'
                    },
                    {
                        label: 'Hấp Line 3',
                        data: data.line3Hap,
                        backgroundColor: 'rgba(153, 102, 255, 0.8)',  // tím
                        borderColor: 'rgb(153, 102, 255)',
                        borderWidth: 1,
                        stack: 'Stack 0'
                    },
                    {
                        label: 'Chiên Line 3',
                        data: data.line3Chien,
                        backgroundColor: 'rgba(255, 206, 86, 0.8)',  // Vang
                        borderColor: 'rgb(255, 206, 86)',
                        borderWidth: 1,
                        stack: 'Stack 0'
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
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Tổng hơi (kg)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('en-US', { maximumFractionDigits: 2 }) + ' kg';
                            }
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Tổng lượng hơi sử dụng',
                        font: {
                            size: 16
                        }
                    },
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
                                
                                if (context.datasetIndex <= 5) { // 4 dataset đầu tiên là các thành phần hơi
                                    let total = 0;
                                    for (let i = 0; i <= 5; i++) {
                                        total += context.chart.data.datasets[i].data[context.dataIndex] || 0;
                                    }
                                    if (context.datasetIndex === 5) { // Dataset cuối cùng của stack
                                        return [
                                            `${label}: ${value.toLocaleString('en-US', { maximumFractionDigits: 2 })} kg`,
                                            `Tổng: ${total.toLocaleString('en-US', { maximumFractionDigits: 2 })} kg`
                                        ];
                                    }
                                    return `${label}: ${value.toLocaleString('en-US', { maximumFractionDigits: 2 })} kg`;
                                }
                            }
                        }
                    }
                }
            }
        });

        console.log('Steam usage chart updated successfully');
    } catch (error) {
        console.error('Error updating Steam usage chart:', error);
    }
}

// Khởi tạo chart
function initSteamUsageChart() {
    console.log('Initializing Steam usage chart...');
    updateSteamUsageChart('today');
}