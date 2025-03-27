// Biến toàn cục để lưu biểu đồ
let motorTemperatureChart = null;
let driverTemperatureChart = null;

async function updateServoTemperatureChart(startTime = null, endTime = null) {
    try {
        // Xây dựng URL với tham số
        const url = new URL('api/FS/get_servo_trend.php', window.location.origin);
        if (startTime) url.searchParams.append('start_time', startTime);
        if (endTime) url.searchParams.append('end_time', endTime);

        const response = await fetch(url.toString());
        const data = await response.json();

        // Kiểm tra xem data có phải mảng không
        if (!Array.isArray(data)) {
            console.error('Dữ liệu không đúng định dạng', data);
            return;
        }
// Sắp xếp dữ liệu theo thời gian từ bé đến lớn
        data.sort((a, b) => new Date(a.Time) - new Date(b.Time));
        
        // Chuẩn bị dữ liệu cho biểu đồ
        const motorDatasets = [];
        const driverDatasets = [];

        // Chuẩn bị màu sắc cho các trục
        const motorColors = [
            'rgb(54, 162, 235)',   // Xanh dương
            'rgb(255, 99, 132)',   // Hồng
            'rgb(75, 192, 192)',   // Ngọc lam
            'rgb(255, 206, 86)',   // Vàng
            'rgb(153, 102, 255)',  // Tím
            'rgb(255, 159, 64)',   // Cam
            'rgb(199, 199, 199)',  // Xám
            'rgb(83, 102, 255)',   // Xanh dương nhạt
            'rgb(255, 99, 64)'     // Đỏ cam
        ];

        // Tạo dataset cho motor
        // for (let i = 1; i <= 9; i++) {
            // motorDatasets.push({
                // label: `Motor ${i}`, //em muốn chỉnh tên Motor thành trục j thì thay chữ "Motor" đó nhưng nó sẻ có số từ 1 đến 9 
                // data: data.map(item => parseFloat(item[`T_motor${i}`])),
                // borderColor: motorColors[i-1],
                // backgroundColor: motorColors[i-1].replace('rgb', 'rgba').replace(')', ', 0.1)'),
                // borderWidth: 2,
                // fill: false,
                // tension: 0.4,
                // pointRadius: 0,  // Điều chỉnh kích thước điểm
                // pointHoverRadius: 3  // Kích thước điểm khi hover
            // });
			// Danh sách tên của các motor
		const motorNames = [
			"Motor Carrousel",
			"Motor Front Table",
			"Motor Rinser",
			"Motor Labeller",
			"Motor Infeed Rinser",
			"Motor Outlet Rinser",
			"Motor Star Transfer",
			"Motor Infeed Filler",
			"Motor Capping Head"
			];

		// Tạo dataset cho motor
		for (let i = 1; i <= 9; i++) {
			motorDatasets.push({
				label: motorNames[i - 1],  // Sử dụng tên motor từ mảng motorNames
				data: data.map(item => parseFloat(item[`T_motor${i}`])),
				borderColor: motorColors[i - 1],
				backgroundColor: motorColors[i - 1].replace('rgb', 'rgba').replace(')', ', 0.1)'),
				borderWidth: 2,
				fill: false,
				tension: 0.4,
				pointRadius: 0,  // Điều chỉnh kích thước điểm
				pointHoverRadius: 3  // Kích thước điểm khi hover
			});
        }

        //Tạo dataset cho driver
        // for (let i = 1; i <= 9; i++) {
            // driverDatasets.push({
                // label: `Driver ${i}`, //em muốn chỉnh tên Diriver thành trục j thì thay chữ "Dirive" đó nhưng nó sẻ có số từ 1 đến 9
                // data: data.map(item => parseFloat(item[`T_drive${i}`])),
                // borderColor: motorColors[i-1],
                // backgroundColor: motorColors[i-1].replace('rgb', 'rgba').replace(')', ', 0.1)'),
                // borderWidth: 2,
                // fill: false,
                // tension: 0.4,
                // pointRadius: 0,  // Điều chỉnh kích thước điểm
                // pointHoverRadius: 3  // Kích thước điểm khi hover
            // });
        // }
		// Danh sách tên của các driver
		const driverNames = [
				"Drive Carrousel",
				"Drive Front Table",
				"Drive Rinser",
				"Drive Labeller",
				"Drive Infeed Rinser",
				"Drive Outlet Rinser",
				"Drive Star Transfer",
				"Drive Infeed Filler",
				"Drive Capping Head"
		];

		// Tạo dataset cho driver
		for (let i = 1; i <= driverNames.length; i++) {
			driverDatasets.push({
				label: driverNames[i - 1],  // Sử dụng tên driver từ mảng driverNames
				data: data.map(item => parseFloat(item[`T_drive${i}`])),
				borderColor: motorColors[i - 1],
				backgroundColor: motorColors[i - 1].replace('rgb', 'rgba').replace(')', ', 0.1)'),
				borderWidth: 1,
				fill: false,
				tension: 0.4,
				pointRadius: 0,  // Điều chỉnh kích thước điểm
				pointHoverRadius: 3  // Kích thước điểm khi hover
			});
		}

// Nhãn thời gian
const labels = data.map(item => {
    const date = new Date(item.Time);
    return date.toLocaleTimeString('vi-VN', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
});
        // Hàm tạo biểu đồ
        function createChart(ctx, datasets, title) {
            return new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: title
                        },
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 15
                            }
                        },
                        zoom: {
                            zoom: {
                                wheel: {
                                    enabled: true,
                                },
                                pinch: {
                                    enabled: true
                                },
                                mode: 'xy',
                            },
                            pan: {
                                enabled: true,
                                mode: 'xy',
                            }
                        }
                    },
                    scales: {
                        y: {
                            title: {
                                display: true,
                                text: 'Nhiệt độ (°C)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Thời gian'
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                },
                plugins: [ChartZoom]
            });
        }

        // Vẽ biểu đồ motor
        const motorCtx = document.getElementById('motorTemperatureChart');
        if (motorCtx) {
            // Hủy biểu đồ cũ nếu tồn tại
            if (motorTemperatureChart) {
                motorTemperatureChart.destroy();
            }
            motorTemperatureChart = createChart(motorCtx, motorDatasets, 'Nhiệt độ Motor Servo');
        }

        // Vẽ biểu đồ driver
        const driverCtx = document.getElementById('driverTemperatureChart');
        if (driverCtx) {
            // Hủy biểu đồ cũ nếu tồn tại
            if (driverTemperatureChart) {
                driverTemperatureChart.destroy();
            }
            driverTemperatureChart = createChart(driverCtx, driverDatasets, 'Nhiệt độ Driver Servo');
        }

    } catch (error) {
        console.error('Lỗi khi tải dữ liệu nhiệt độ:', error);
    }
}

// Hàm xử lý khi người dùng chọn khoảng thời gian
function handleServoTemperatureFilter() {
    const startTimeInput = document.getElementById('servo-start-time');
    const endTimeInput = document.getElementById('servo-end-time');
    const filterButton = document.getElementById('servo-filter-button');

    filterButton.addEventListener('click', () => {
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;

        if (startTime && endTime) {
            updateServoTemperatureChart(startTime, endTime);
        } else {
            alert('Vui lòng chọn cả thời gian bắt đầu và kết thúc');
        }
    });

    // Nút reset để hiển thị toàn bộ dữ liệu
    const resetButton = document.getElementById('servo-reset-button');
    resetButton.addEventListener('click', () => {
        startTimeInput.value = '';
        endTimeInput.value = '';
        updateServoTemperatureChart();
    });
}

// Khởi tạo khi trang tải
document.addEventListener('DOMContentLoaded', () => {
    updateServoTemperatureChart();
    handleServoTemperatureFilter();
});