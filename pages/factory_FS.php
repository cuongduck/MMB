<div class="container mx-auto p-4">


    <!-- Line Status Cards -->
   

<!-- Status Card -->
<!-- Status Card -->
<div class="card mb-6 rounded-lg overflow-hidden shadow-lg border-t-4 border-blue-600">
    <div class="bg-gradient-to-r from-blue-50 to-white p-2">
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Trạng thái -->
            <div class="bg-white p-2 rounded-lg shadow-md transform hover:scale-105 transition-transform duration-300">
                <div class="text-sm text-gray-600 mb-2">Trạng thái</div>
                <div id="fs-status" class="status text-xl font-bold p-2 rounded-md text-center"></div>
            </div>
            
            <!-- Tốc độ -->
            <div class="bg-white p-2 rounded-lg shadow-md transform hover:scale-105 transition-transform duration-300">
                <div class="text-sm text-gray-600 mb-2">Tốc độ</div>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <div id="fs-speed" class="text-xl font-bold text-blue-800">0 Chai/H</div>
                </div>
            </div>
            
            <!-- SP đang chạy -->
            <div class="bg-white p-2 rounded-lg shadow-md transform hover:scale-105 transition-transform duration-300">
                <div class="text-sm text-gray-600 mb-2">SP đang chạy</div>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <div id="fs-product" class="text-xl font-bold text-blue-800">-</div>
                </div>
            </div>
            
            <!-- Sản lượng thực tế -->
            <div class="bg-white p-2 rounded-lg shadow-md transform hover:scale-105 transition-transform duration-300">
                <div class="text-sm text-gray-600 mb-2">Sản lượng thực tế</div>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <div id="fs-production" class="text-xl font-bold text-blue-800">-</div>
                </div>
            </div>

            
        </div>
    </div>
</div>
    <!-- Realtime panel container -->

 <!-- Kết thúc Stutus line -->
   <!-- Overview Cards -->
<!-- Trong factory.php -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
<!-- Tổng chai -->
<!-- Tổng Chai -->
<div class="bg-white rounded-lg p-4 shadow">
    <div class="flex items-center gap-2 mb-3">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2z"/>
        </svg>
        <span class="text-gray-600 font-medium">TỔNG CHAI</span>
    </div>
    <div class="mb-2">
        <span id="total-production" class="text-blue-600 text-3xl font-bold">0</span>
        <span class="ml-2 px-2 py-1 bg-red-50 text-red-500 text-sm rounded">0</span>
    </div>

</div>

    <!-- OEE -->
    <div class="bg-white rounded-lg p-4 shadow">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-gray-600 font-medium">OEE</span>
        </div>
        <div class="mb-2">
            <span id="total-oee" class="text-yellow-500 text-3xl font-bold">0.00%</span>
            <span class="ml-2 px-2 py-1 bg-red-50 text-red-500 text-sm rounded">0.00%</span>
        </div>

    </div>

    <!-- Tiêu hao hơi -->
    <div class="bg-white rounded-lg p-4 shadow">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span class="text-gray-600 font-medium">TIÊU THỤ HƠI</span>
        </div>
        <div class="mb-2">
            <span id="steam-consumption" class="text-blue-600 text-3xl font-bold">0.00</span>
            <span class="ml-2 px-2 py-1 bg-red-50 text-red-500 text-sm rounded">0.00%</span>
        </div>

    </div>

    <!-- Tiêu hao điện -->
<!-- Tiêu hao điện -->
<div class="bg-white rounded-lg p-4 shadow">
    <div class="flex items-center gap-2 mb-3">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <span class="text-gray-600 font-medium">LƯỢNG ĐIỆN TIÊU THỤ</span>
    </div>
    <div class="mb-2">
        <span id="power-consumption" class="text-blue-600 text-3xl font-bold">0.00</span>
        <span class="ml-2 px-2 py-1 bg-red-50 text-red-500 text-sm rounded">0.00%</span>
    </div>

</div>
</div>
  <!-- kết thúc overview -->
   <!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- OEE Chart -->
    <div class="card">
        <div class="chart-header" style="min-height: 10px;">
            <h3 class="text-lg font-semibold mb-4">OEE Chart</h3>
        </div>
        <div class="chart-container" style="height: 300px;">
            <canvas id="oeeChart"></canvas>
        </div>
    </div>
    
    <!-- Biểu đồ trend tốc độ dạng điện tim đồ -->
    <div class="card ecg-container">
        <div class="chart-header" style="min-height: 20px;">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">Biểu đồ trend tốc độ FS</h3>
   
            </div>
        </div>
        
        <!-- Nút điều khiển ECG -->
        <div class="ecg-controls px-1">
            <button onclick="goToStart()" class="ecg-control-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="19 20 9 12 19 4 19 20"></polygon>
                    <line x1="5" y1="19" x2="5" y2="5"></line>
                </svg>
                Đầu
            </button>
            <button onclick="moveLeft()" class="ecg-control-btn primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="19 20 9 12 19 4 19 20"></polygon>
                </svg>
                Trước
            </button>
            <button id="autoScrollButton" onclick="toggleAutoScroll()" class="ecg-control-btn success">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="6" y="4" width="4" height="16"></rect>
                    <rect x="14" y="4" width="4" height="16"></rect>
                </svg>
                Dừng cuộn
            </button>
            <button onclick="moveRight()" class="ecg-control-btn primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="5 4 15 12 5 20 5 4"></polygon>
                </svg>
                Sau
            </button>
            <button onclick="goToEnd()" class="ecg-control-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="5 4 15 12 5 20 5 4"></polygon>
                    <line x1="19" y1="5" x2="19" y2="19"></line>
                </svg>
                Cuối
            </button>
        </div>
        
        <!-- Container biểu đồ với lớp papyrus -->
        <div class="chart-container ecg-paper ecg-running position-relative" style="height: 300px;">
            <!-- Hiệu ứng scanner -->
            <div class="ecg-scanner"></div>
            <canvas id="speedChart"></canvas>
        </div>
        
        <!-- Phần thông tin phân trang -->
        <div id="ecg-pagination" class="ecg-status">
            <div class="ecg-status-indicator">
                <div class="ecg-status-dot"></div>
                <span>Đang cập nhật</span>
            </div>
            <span id="ecg-page-info">Đang tải dữ liệu...</span>
        </div>
    </div>
</div>

<!-- Các phần khác của trang -->

<!-- Kết thúc Stutus line -->



<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Steam Consumption Chart -->
    <div class="card">
        <h3 class="text-lg font-semibold mb-4">Hơi/Sp theo xưởng</h3>
        <div class="chart-container" style="height: 300px;">
            <canvas id="steamChart"></canvas>
        </div>
    </div>
    <!-- Steam Consumption by line Chart -->
    <div class="card">
        <h3 class="text-lg font-semibold mb-4">Lượng Hơi theo Khu vực</h3>
        <div class="chart-container" style="height: 300px;">
            <canvas id="steamUsageChart"></canvas>
        </div>
    </div>
</div>


<!-- Downtime Chart -->

<div class="card mt-6">
        <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Downtime Line FS</h3>
     </div>
    <div class="chart-container" style="height: 390px;">
        <canvas id="downtimeChart"></canvas>
    </div>
</div>
     <!-- Downtime table -->
<div class="card mt-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Chi Tiết Downtime </h3>
      <!--   <select id="line-select" class="px-3 py-1 border rounded">
            <option value="all">Tất cả line</option>
            <option value="FS">FS</option>
            <option value="FS">FS</option>

        </select>-->
    </div>
    <div class="table-container" style="height: 350px; overflow-y: auto;">
        <table class="min-w-full">
            <thead class="table-header sticky top-0 bg-[#4472C4]">
    <tr>
        <th class="px-2 py-2 text-left text-white border border-[#8EA9DB]">Thời gian</th>
        <th class="px-2 py-2 text-left text-white border border-[#8EA9DB]">Line</th>
        <th class="px-4 py-2 text-left text-white border border-[#8EA9DB]">Tên lỗi</th>
        <th class="px-2 py-2 text-left text-white border border-[#8EA9DB]">Thời gian dừng</th>
        <th class="px-2 py-2 text-left text-white border border-[#8EA9DB]">Ghi chú</th>
        <th class="px-2 py-2 text-left text-white border border-[#8EA9DB]">Bắt đầu</th>
        <th class="px-2 py-2 text-left text-white border border-[#8EA9DB]">Kết thúc</th>
       <th class="px-2 py-2 text-left text-white border border-[#8EA9DB]">Action</th>
    </tr>
</thead>
            <tbody id="downtimeTableContent">
         </tbody>
        </table>
    </div>
</div>
<div class="mt-6"></div>

<!-- Container cho 2 biểu đồ điện năng -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
    <!-- Power Donut Chart -->
    <div class="lg:col-span-4">
        <div class="card">
            <h3 class="text-lg font-semibold mb-4">Điện Năng Theo Khu Vực</h3>
            <div class="chart-container" style="height: 320px;">
                <canvas id="powerDonutChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Power Line Chart -->
    <div class="lg:col-span-8">
        <div class="card">
            <h3 class="text-lg font-semibold mb-4">Trend Theo Thời Gian</h3>
            <div class="chart-container" style="height: 320px;">
                <canvas id="powerLineChart"></canvas>
            </div>
        </div>
    </div>
 </div>   
<!-- Biểu đồ Nhiệt độ Servo -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Biểu đồ Nhiệt độ Motor -->
    <div class="card">
        <h3 class="text-lg font-semibold mb-4">Nhiệt độ Motor Servo</h3>
        <div class="chart-container" style="height: 350px;">
            <canvas id="motorTemperatureChart"></canvas>
        </div>
    </div>
    
    <!-- Biểu đồ Nhiệt độ Driver -->
    <div class="card">
        <h3 class="text-lg font-semibold mb-4">Nhiệt độ Driver Servo</h3>
        <div class="chart-container" style="height: 350px;">
            <canvas id="driverTemperatureChart"></canvas>
        </div>
    </div>
</div>


<!-- Power Usage Table -->

<!-- Steam Table -->

<!-- TLTB Table -->

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo biểu đồ nhiệt độ servo
    updateServoTemperatureChart();
});
</script
<!-- Modal Container -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    initSteamUsageChart();
});
</script>
<?php
include 'includes/footer_FS.php';
?>