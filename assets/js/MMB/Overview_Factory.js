// Các giá trị target
const TARGETS = {
    OEE: 89,
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

// Lưu trữ period hiện tại
let currentPeriod = 'today';

// Khởi tạo overview cards
function initFactoryOverviewCards() {
    console.log('Initializing factory overview cards...');
    
    // Lấy period được chọn từ các nút
    const periodButtons = document.querySelectorAll('.date-filter .btn');
    const activePeriod = Array.from(periodButtons).find(btn => btn.classList.contains('active'))?.dataset.period || 'today';
    
    // Cập nhật trạng thái period hiện tại
    currentPeriod = activePeriod;
    
    // Cập nhật dữ liệu với period đang chọn
    updateFactoryOverviewCards(currentPeriod);
    
    // Thêm sự kiện cho các nút period
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Lấy period từ nút được nhấn
            const period = this.dataset.period;
            
            // Cập nhật period hiện tại
            currentPeriod = period;
            
            // Cập nhật dữ liệu với period mới
            updateFactoryOverviewCards(period);
        });
    });
}

// Hàm cập nhật Overview Cards cho toàn nhà máy
async function updateFactoryOverviewCards(period = currentPeriod) {
    try {
        console.log('Fetching factory data for period:', period);
        const response = await fetch(`api/MMB/get_factory_overview.php?period=${period}`);
        const data = await response.json();
        console.log('Factory overview data:', data);
        
        // Cập nhật tổng sản lượng
        updateProductionCard(data);
        
        // Cập nhật OEE
        updateOEECard(data);
        
        // Cập nhật tiêu hao hơi
        updateSteamCard(data);
        
        // Cập nhật tiêu hao điện
        updatePowerCard(data);
        
    } catch (error) {
        console.error('Error updating factory overview cards:', error);
    }
}

function updateProductionCard(data) {
    // Tổng sản phẩm từ tất cả các xưởng
    const totalProd = data.total_production || 0;
    const productionTarget = data.total_production_plan || 0;
    
    const productionDiff = totalProd - productionTarget;
    const productionPercent = productionTarget > 0 ? (productionDiff / productionTarget * 100).toFixed(2) : 0;
    
    // Cập nhật tổng sản phẩm
    document.getElementById('total-production').textContent = formatNumber(totalProd);
    
    // Cập nhật phần trăm chênh lệch
    const element = document.querySelector('#total-production + span');
    if (element) {
        element.textContent = productionPercent > 0 ? '+' + productionPercent + '%' : productionPercent + '%';
        element.className = `ml-2 px-2 py-1 rounded text-sm ${
            productionPercent >= 0 ? 'bg-green-50 text-green-500' : 'bg-red-50 text-red-500'
        }`;
    }
    
    // Cập nhật sản lượng theo xưởng
    document.getElementById('f2-production').textContent = formatNumber(data.f2_production || 0);
    document.getElementById('f3-production').textContent = formatNumber(data.f3_production || 0);
    document.getElementById('csd-production').textContent = formatNumber(data.csd_production || 0);
    document.getElementById('fs-production').textContent = formatNumber(data.fs_production || 0);
}

function updateOEECard(data) {
    // Lấy OEE trung bình từ API
    const totalOEE = data.total_oee || 0;
    
    // Cập nhật OEE
    document.getElementById('total-oee').textContent = totalOEE.toFixed(2) + '%';
    
    // Cập nhật phần trăm chênh lệch so với target (89%)
    const oeeTarget = TARGETS.OEE;
    const oeePercent = ((totalOEE - oeeTarget) / oeeTarget * 100).toFixed(2);
    
    const element = document.querySelector('#total-oee + span');
    if (element) {
        element.textContent = oeePercent > 0 ? '+' + oeePercent + '%' : oeePercent + '%';
        element.className = `ml-2 px-2 py-1 rounded text-sm ${
            oeePercent >= 0 ? 'bg-green-50 text-green-500' : 'bg-red-50 text-red-500'
        }`;
    }
    
    // Cập nhật OEE theo xưởng
    document.getElementById('f2-oee').textContent = (data.f2_oee || 0).toFixed(2) + '%';
    document.getElementById('f3-oee').textContent = (data.f3_oee || 0).toFixed(2) + '%';
    document.getElementById('csd-oee').textContent = (data.csd_oee || 0).toFixed(2) + '%';
    document.getElementById('fs-oee').textContent = (data.fs_oee || 0).toFixed(2) + '%';
}

function updateSteamCard(data) {
    // Tính trung bình tiêu thụ hơi cho các xưởng có dữ liệu
    let validSteamCount = 0;
    let totalSteam = 0;
    
    if (data.f2_steam) {
        totalSteam += data.f2_steam;
        validSteamCount++;
    }
    
    if (data.f3_steam) {
        totalSteam += data.f3_steam;
        validSteamCount++;
    }
    
    if (data.csd_steam) {
        totalSteam += data.csd_steam;
        validSteamCount++;
    }
    
    if (data.fs_steam) {
        totalSteam += data.fs_steam;
        validSteamCount++;
    }
    
    const avgSteam = validSteamCount > 0 ? totalSteam / validSteamCount : 0;
    
    // Cập nhật tiêu thụ hơi
    document.getElementById('steam-consumption').textContent = avgSteam.toFixed(2);
    
    // Cập nhật phần trăm chênh lệch so với target
    const steamTarget = TARGETS.STEAM;
    const steamPercent = ((avgSteam - steamTarget) / steamTarget * 100).toFixed(2);
    
    const element = document.querySelector('#steam-consumption + span');
    if (element) {
        element.textContent = steamPercent + '%';
        element.className = `ml-2 px-2 py-1 rounded text-sm ${
            steamPercent <= 0 ? 'bg-green-50 text-green-500' : 'bg-red-50 text-red-500'
        }`;
    }
    
    // Cập nhật tiêu thụ hơi theo xưởng
    document.getElementById('f2-steam').textContent = (data.f2_steam || 0).toFixed(2);
    document.getElementById('f3-steam').textContent = (data.f3_steam || 0).toFixed(2);
    document.getElementById('csd-steam').textContent = (data.csd_steam || 0).toFixed(2);
    document.getElementById('fs-steam').textContent = (data.fs_steam || 0).toFixed(2);
}

function updatePowerCard(data) {
    // Tính tiêu thụ điện tổng
    const totalPower = (data.f2_power || 0) + (data.f3_power || 0) + (data.csd_power || 0) + (data.fs_power || 0);
    
    // Cập nhật tiêu thụ điện
    document.getElementById('power-consumption').textContent = Math.round(totalPower) + ' kW';
    
    // Cập nhật phần trăm so với target
    const powerTarget = TARGETS.POWER * 4; // Nhân 4 vì tính cho 4 xưởng
    const powerPercent = (totalPower / powerTarget * 100).toFixed(2);
    
    const element = document.querySelector('#power-consumption + span');
    if (element) {
        element.textContent = powerPercent + '%';
        element.className = `ml-2 px-2 py-1 rounded text-sm ${
            parseFloat(powerPercent) <= 100 ? 'bg-green-50 text-green-500' : 'bg-red-50 text-red-500'
        }`;
    }
    
    // Cập nhật tiêu thụ điện theo xưởng
    document.getElementById('f2-power').textContent = Math.round(data.f2_power || 0) + ' kW';
    document.getElementById('f3-power').textContent = Math.round(data.f3_power || 0) + ' kW';
    document.getElementById('csd-power').textContent = Math.round(data.csd_power || 0) + ' kW';
    document.getElementById('fs-power').textContent = Math.round(data.fs_power || 0) + ' kW';
}

// Khởi tạo khi trang load
document.addEventListener('DOMContentLoaded', initFactoryOverviewCards);

// Cập nhật tự động mỗi 60 giây
setInterval(() => {
    // Cập nhật dữ liệu với period hiện tại
    updateFactoryOverviewCards(currentPeriod);
}, 60000);

// Export function để các module khác có thể gọi
window.updateFactoryOverviewCards = updateFactoryOverviewCards;