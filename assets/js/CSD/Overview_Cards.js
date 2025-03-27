// Các giá trị target
const TARGETS = {
    OEE: 90,
    STEAM: 5.4,
    POWER: 300
};

// Format số thành to K, M (nghìn, triệu)
function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(2) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

// Khởi tạo overview cards
function initOverviewCards() {
    console.log('Initializing overview cards...');
    updateOverviewCards('today');
}

// Hàm cập nhật Overview Cards
async function updateOverviewCards(period = 'today') {
    try {
        console.log('Fetching data for period:', period);
        const response = await fetch(`api/CSD/get_filtered_data.php?period=${period}`);
        const data = await response.json();
        console.log('Received data:', data);
        
        // Cập nhật tổng sản lượng
        updateProductionCard(data);
        
        // Cập nhật OEE
        updateOEECard(data);
        
        // Cập nhật tiêu hao hơi
        updateSteamCard(data);
        
        // Cập nhật tiêu hao điện
        updatePowerCard(data);
        
    } catch (error) {
        console.error('Error updating overview cards:', error);
    }
}

function updateProductionCard(data) {
    // Change this line to match the API response
    const totalProd = parseInt(data.CSD_production) || 0;
    const productionTarget = parseInt(data.CSD_production_plan) || 0;

    
    const productionDiff = totalProd - productionTarget;
    
    // Change this to match your HTML ID
    document.getElementById('total-production').textContent = formatNumber(totalProd);
    
    const element = document.querySelector('#total-production + span');
    if (element) {
        const displayValue = productionDiff > 0 ? '+' + formatNumber(productionDiff) : formatNumber(productionDiff);
        element.textContent = displayValue;
        element.className = `ml-2 px-2 py-1 rounded text-sm ${
            productionDiff >= 0 ? 'bg-green-50 text-green-500' : 'bg-red-50 text-red-500'
        }`;
    }
}

function updateOEECard(data) {
    const totalOEE = parseFloat(data.total_oee) || 0;

    document.getElementById('total-oee').textContent = totalOEE.toFixed(2) + '%';

    const oeePercent = ((totalOEE - TARGETS.OEE) / TARGETS.OEE * 100).toFixed(2);
    updatePercentageTag('total-oee', oeePercent);
}

function updateSteamCard(data) {
    const totalSteam = parseFloat(data.total_steam) || 0;
    
    document.getElementById('steam-consumption').textContent = totalSteam.toFixed(2);
    
    const steamPercent = ((totalSteam - TARGETS.STEAM) / TARGETS.STEAM * 100).toFixed(2);
    updatePercentageTag('steam-consumption', steamPercent);
}

function updatePowerCard(data) {
    const totalPower = parseFloat(data.total_power) || 0;
    // You don't have power_target in your API response, so use TARGETS.POWER
    const powerTarget = TARGETS.POWER;
    
    document.getElementById('power-consumption').textContent = Math.round(totalPower) + ' Kw';
    
    const element = document.querySelector('#power-consumption + span');
    if (element) {
        const powerPercent = ((totalPower / powerTarget) * 100).toFixed(2);
        element.textContent = powerPercent + '%';
        element.className = `ml-2 px-2 py-1 rounded text-sm ${
            parseFloat(powerPercent) > 50 ? 'bg-red-50 text-red-500' : 'bg-green-50 text-green-500'
        }`;
    }
}

function updatePercentageTag(elementId, percentage) {
    const element = document.querySelector(`#${elementId} + span`);
    if (element) {
        // Xử lý riêng cho power-consumption
        if (elementId === 'power-consumption') {
            return; // Bỏ qua vì đã xử lý trong updatePowerCard
        }
        
        // Xử lý cho các trường hợp khác
        const isPositiveMetric = ['total-oee'].includes(elementId);
        const displayValue = isPositiveMetric ? percentage : -percentage;
        const isGood = isPositiveMetric ? (percentage >= 0) : (percentage < 0);
        
        element.textContent = (displayValue > 0 ? '+' : '') + displayValue + '%';
        element.className = `ml-2 px-2 py-1 rounded text-sm ${
            isGood ? 'bg-green-50 text-green-500' : 'bg-red-50 text-red-500'
        }`;
    }
}

// Khởi tạo khi trang load
document.addEventListener('DOMContentLoaded', initOverviewCards);