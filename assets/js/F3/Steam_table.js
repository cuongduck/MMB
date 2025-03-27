let steamTable = null;

async function updateSteamTable(period) {
    try {
        const response = await fetch(`api/F3/get_steam_table.php?period=${period}`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const result = await response.json();
        const data = result.data;
        const averages = result.averages;
        
        const tableBody = document.getElementById('steamTableContent');
        let html = '';
        
        // Render các hàng dữ liệu
        data.forEach((row, index) => {
            const time = new Date(row.time);
            const formattedTime = time.toLocaleString('vi-VN', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            
            const rowClass = index % 2 === 0 ? 'bg-white' : 'bg-gray-50';
            
            html += `
                <tr class="${rowClass} hover:bg-gray-100 transition-colors">
                    <td class="px-2 py-2 border border-gray-200">${formattedTime}</td>
                    <td class="px-2 py-2 border border-gray-200 text-right">${row.L5_Hap.toFixed(1)}</td>
                    <td class="px-2 py-2 border border-gray-200 text-right">${row.L5_Chien.toFixed(1)}</td>
                    <td class="px-2 py-2 border border-gray-200 text-right">${row.L6_Hap.toFixed(1)}</td>
                    <td class="px-2 py-2 border border-gray-200 text-right">${row.L6_Chien.toFixed(1)}</td>
                    <td class="px-2 py-2 border border-gray-200 text-right"></td> 
                    <td class="px-2 py-2 border border-gray-200 text-right"></td> 
                    <td class="px-2 py-2 border border-gray-200 text-right"></td> 
                    <td class="px-2 py-2 border border-gray-200 text-right"></td> 
                </tr>
            `;
        });
        
        // Thêm hàng tổng hợp
        html += `
            <tr class="bg-blue-50 font-bold sticky bottom-0">
                <td class="px-2 py-2 border border-gray-200">TB: </td>
                <td class="px-2 py-2 border border-gray-200 text-right">${parseFloat(averages.avg_L5_Hap).toFixed(1)}</td>
                <td class="px-2 py-2 border border-gray-200 text-right">${parseFloat(averages.avg_L5_Chien).toFixed(1)}</td>
                <td class="px-2 py-2 border border-gray-200 text-right">${parseFloat(averages.avg_L6_Hap).toFixed(1)}</td>
                <td class="px-2 py-2 border border-gray-200 text-right">${parseFloat(averages.avg_L6_Chien).toFixed(1)}</td>

            </tr>
        `;
        
        tableBody.innerHTML = html;
        
        console.log('Cập nhật bảng so hoi thành công');
    } catch (error) {
        console.error('Lỗi khi cập nhật bảng so hoi:', error);
    }
}

// Khởi tạo bảng
function initSteamTable() {
    updateSteamTable('today');
}

// Đăng ký sự kiện khi DOM đã sẵn sàng
document.addEventListener('DOMContentLoaded', function() {
    initSteamTable();
});