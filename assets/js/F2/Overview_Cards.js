// Các giá trị target
const TARGETS = {
    OEE: 89,
    STEAM: 98,
    POWER: 4.5
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
        const response = await fetch(`api/F2/get_filtered_data.php?period=${period}`);
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
    const totalProd = parseInt(data.total_production) || 0;
    const l1Prod = parseInt(data.l1_production) || 0;
    const l2Prod = parseInt(data.l2_production) || 0;
    const l3Prod = parseInt(data.l3_production) || 0;
    const l4Prod = parseInt(data.l4_production) || 0;
    const productionTarget = parseInt(data.total_plan) || 0;
    
    // Tính chênh lệch theo giá trị tuyệt đối
    const productionDiff = totalProd - productionTarget;
    
    // Cập nhật UI với định dạng số mới
    document.getElementById('total-production').textContent = formatNumber(totalProd);
    document.getElementById('l1-production').textContent = formatNumber(l1Prod);
    document.getElementById('l2-production').textContent = formatNumber(l2Prod);
    document.getElementById('l3-production').textContent = formatNumber(l3Prod);
    document.getElementById('l4-production').textContent = formatNumber(l4Prod);
    
    // Cập nhật phần trăm bằng giá trị chênh lệch
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
    const l1OEE = parseFloat(data.l1_oee) || 0;
    const l2OEE = parseFloat(data.l2_oee) || 0;
    const l3OEE = parseFloat(data.l3_oee) || 0;
    const l4OEE = parseFloat(data.l4_oee) || 0;
    // Cập nhật UI
    document.getElementById('total-oee').textContent = totalOEE.toFixed(2) + '%';
    document.getElementById('l1-oee').textContent = l1OEE.toFixed(2) + '%';
    document.getElementById('l2-oee').textContent = l2OEE.toFixed(2) + '%';
    document.getElementById('l3-oee').textContent = l3OEE.toFixed(2) + '%';
    document.getElementById('l4-oee').textContent = l4OEE.toFixed(2) + '%';
    // Tính và hiển thị % so với target
    const oeePercent = ((totalOEE - TARGETS.OEE) / TARGETS.OEE * 100).toFixed(2);
    updatePercentageTag('total-oee', oeePercent);
}

function updateSteamCard(data) {
    const totalSteam = parseFloat(data.total_steam) || 0;
    const l1Steam = parseFloat(data.l1_steam) || 0;
    const l2Steam = parseFloat(data.l2_steam) || 0;
    const l3Steam = parseFloat(data.l3_steam) || 0;
    
    // Cập nhật UI
    document.getElementById('steam-consumption').textContent = totalSteam.toFixed(2);
    document.getElementById('l1-steam').textContent = l1Steam.toFixed(2);
    document.getElementById('l2-steam').textContent = l2Steam.toFixed(2);
    document.getElementById('l3-steam').textContent = l3Steam.toFixed(2);
    
    // Tính phần trăm (giá trị thấp hơn là tốt hơn)
    const steamPercent = ((totalSteam - TARGETS.STEAM) / TARGETS.STEAM * 100).toFixed(2);
    updatePercentageTag('steam-consumption', steamPercent);
}

function updatePowerCard(data) {
    const totalPower = parseFloat(data.total_power) || 0;
    const powerTarget = parseFloat(data.power_target) || 0;
    const l1Power = parseFloat(data.l1_power) || 0;
    const l2Power = parseFloat(data.l2_power) || 0;
    const l3Power = parseFloat(data.l3_power) || 0;
    const l4Power = parseFloat(data.l4_power) || 0;
    const mnkPower = parseFloat(data.mnk_power) || 0;
    const ahuPower = parseFloat(data.ahu_power) || 0;
    
    // Làm tròn số nguyên và thêm đơn vị Kw
    document.getElementById('power-consumption').textContent = Math.round(totalPower) + ' Kw';
    document.getElementById('l1-power').textContent = Math.round(l1Power) + ' Kw';
    document.getElementById('l2-power').textContent = Math.round(l2Power) + ' Kw';
    document.getElementById('l3-power').textContent = Math.round(l3Power) + ' Kw';
    document.getElementById('l4-power').textContent = Math.round(l4Power) + ' Kw';
    document.getElementById('mnk-power').textContent = Math.round(mnkPower) + ' Kw';
    document.getElementById('ahu-power').textContent = Math.round(ahuPower) + ' Kw';
    
    // Cập nhật phần trăm cho tiêu thụ điện
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