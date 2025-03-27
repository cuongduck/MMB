// Variables to store current values for animation
let currentSpeed = 0;
let currentProduction = 0;
let currentCo2 = 0;
let currentBrix = 0;

// Lưu giá trị cũ để so sánh sự thay đổi
let previousStatus = '';
let previousProduct = '';

// Hàm khởi tạo realtime status
function initRealtimeStatus() {
    console.log('Initializing realtime status...');
    
    // Cập nhật dữ liệu lần đầu tiên
    updateRealtimeStatus();
    
    // Thiết lập cập nhật tự động mỗi 0.5 giây
    setInterval(updateRealtimeStatus, 500);
}

// Hàm cập nhật status card
async function updateRealtimeStatus() {
    try {
        // Lấy dữ liệu từ API
        const response = await fetch('api/CSD/get_realtime_data.php');
        const data = await response.json();
        
        if (data.error) {
            console.error('Error fetching realtime data:', data.message);
            return;
        }
        
        // Cập nhật trạng thái với hiệu ứng nếu có thay đổi
        if (data.status !== previousStatus) {
            updateStatus(data.status);
            previousStatus = data.status;
            pulseElement('csd-status');
        }
        
        // Xử lý tốc độ - lấy giá trị số từ chuỗi
        let speedValue = parseFloat(data.speed.replace(/,/g, '').replace('', '').trim());
        
        // Cập nhật tốc độ với hiệu ứng số
        animateValue('csd-speed', currentSpeed, speedValue, 500);
        currentSpeed = speedValue;
        
        // Format hiển thị với "Chai/H"
        document.getElementById('csd-speed').textContent = new Intl.NumberFormat().format(speedValue) + ' ';
        
        // Cập nhật sản phẩm đang chạy với hiệu ứng nếu có thay đổi
        if (data.product !== previousProduct) {
            document.getElementById('csd-product').textContent = data.product;
            previousProduct = data.product;
            pulseElement('csd-product');
        }
        
        // Cập nhật sản lượng thực tế với hiệu ứng số
        const productionValue = parseInt(data.production);
        animateValue('csd-production', currentProduction, productionValue, 500);
        currentProduction = productionValue;
        
        // Cập nhật Co2 với hiệu ứng số - chuyển từ chuỗi sang số thực
        const co2Value = parseFloat(data.co2);
        animateValue('csd-co2', currentCo2, co2Value, 500, 3);
        currentCo2 = co2Value;
        
        // Cập nhật Brix với hiệu ứng số - chuyển từ chuỗi sang số thực
        const brixValue = parseFloat(data.brix);
        animateValue('csd-brix', currentBrix, brixValue, 500, 3);
        currentBrix = brixValue;
        
    } catch (error) {
        console.error('Error updating realtime status:', error);
    }
}

// Hàm cập nhật trạng thái với màu sắc tương ứng và hiệu ứng đẹp
function updateStatus(status) {
    const statusElement = document.getElementById('csd-status');
    
    // Đặt nội dung
    statusElement.textContent = status;
    
    // Đặt màu sắc và kiểu dáng dựa trên trạng thái
    switch(status.toLowerCase()) {
        case 'running':
            statusElement.className = 'status text-xl font-bold px-4 py-2 rounded-md text-center bg-gradient-to-r from-green-400 to-green-500 text-white inline-block mx-auto';
            break;
        case 'stopped':
            statusElement.className = 'status text-xl font-bold px-4 py-2 rounded-md text-center bg-gradient-to-r from-red-400 to-red-500 text-white inline-block mx-auto';
            break;
        case 'paused':
            statusElement.className = 'status text-xl font-bold px-4 py-2 rounded-md text-center bg-gradient-to-r from-yellow-400 to-yellow-500 text-white inline-block mx-auto';
            break;
        default:
            statusElement.className = 'status text-xl font-bold px-4 py-2 rounded-md text-center bg-gradient-to-r from-gray-400 to-gray-500 text-white inline-block mx-auto';
    }
}

// Hàm tạo hiệu ứng nhảy số với định dạng đẹp hơn
function animateValue(elementId, start, end, duration, decimals = 0) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    // Nếu giá trị bắt đầu và kết thúc giống nhau, không cần animation
    if (start === end) return;
    
    const startTime = performance.now();
    const formatNumber = (num) => {
        if (decimals > 0) {
            return num.toFixed(decimals);
        } else {
            return Math.floor(num).toLocaleString('vi-VN');
        }
    };
    
    function updateAnimationFrame(currentTime) {
        const elapsedTime = currentTime - startTime;
        
        if (elapsedTime > duration) {
            // Xử lý đặc biệt cho tốc độ
            if (elementId === 'csd-speed') {
                element.textContent = new Intl.NumberFormat().format(end) + '';
            } else {
                element.textContent = formatNumber(end);
            }
            
            // Thêm hiệu ứng nhấp nháy khi giá trị kết thúc thay đổi
            if (Math.abs(end - start) / Math.max(1, end) > 0.01) { // Nếu thay đổi hơn 1%
                pulseElement(elementId);
            }
            return;
        }
        
        const progress = elapsedTime / duration;
        const currentValue = start + (end - start) * progress;
        
        // Xử lý đặc biệt cho tốc độ
        if (elementId === 'csd-speed') {
            element.textContent = new Intl.NumberFormat().format(Math.floor(currentValue)) + '';
        } else {
            element.textContent = formatNumber(currentValue);
        }
        
        requestAnimationFrame(updateAnimationFrame);
    }
    
    requestAnimationFrame(updateAnimationFrame);
}

// Hàm tạo hiệu ứng pulsing (nhấp nháy) khi giá trị thay đổi
function pulseElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    // Thêm lớp animation
    element.classList.add('pulse-effect');
    
    // Xóa lớp animation sau khi hoàn thành để có thể sử dụng lại
    setTimeout(() => {
        element.classList.remove('pulse-effect');
    }, 1000);
}

// Thêm style cho hiệu ứng nhấp nháy
function addPulseEffectStyle() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .pulse-effect {
            animation: pulse 0.5s ease-in-out;
        }
        
        .status, #csd-speed, #csd-product, #csd-production, #csd-co2, #csd-brix {
            transition: all 0.2s ease-in-out;
        }
        
        .bg-white {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        /* Style cho statusElement để ôm sát chữ */
        #csd-status {
            display: inline-block !important;
            width: auto !important;
            min-width: 100px;
            max-width: fit-content;
            margin: 0 auto;
        }
    `;
    document.head.appendChild(style);
}

// Khởi tạo khi trang load
document.addEventListener('DOMContentLoaded', () => {
    addPulseEffectStyle();
    initRealtimeStatus();
});