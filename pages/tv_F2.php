<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Không yêu cầu đăng nhập cho TV display
// require_once 'includes/auth.php';
// requireLogin();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Dashboard MMB - TV Display</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico?v=<?php echo time(); ?>">
    <link rel="apple-touch-icon" href="favicon.png?v=<?php echo time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-annotation/2.1.0/chartjs-plugin-annotation.min.js"></script>
    
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            background-color: #F3F4F6;
            color: #1F2937;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .slide-container {
            width: 100vw;
            height: 100vh;
            position: relative;
        }
        
        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .slide.active {
            opacity: 1;
            z-index: 1;
        }
        
        .chart-container {
            flex: 1;
            position: relative;
            width: 100%;
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #E5E7EB;
        }
        
        h1 {
            font-size: 2.5rem;
            text-align: center;
            margin: 10px 0 30px 0;
            color: #1E40AF;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: white;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #E5E7EB;
        }
        
        .date-time {
            font-size: 1.2rem;
            color: #4B5563;
            font-weight: 500;
        }
        
        .logo {
            max-height: 60px;
        }
        
        .chart-title {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #1E40AF;
            text-align: center;
            font-weight: 600;
        }
        
        .slide-indicator {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }
        
        .indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #D1D5DB;
            transition: all 0.3s ease;
        }
        
        .indicator.active {
            background-color: #2563EB;
            transform: scale(1.3);
        }
        
        .chart-info {
            position: absolute;
            bottom: 10px;
            right: 20px;
            font-size: 0.9rem;
            color: #6B7280;
        }

        /* Make charts dark text on light background */
        #oeeChart, #oeeByLineChart, #steamChart, #steamUsageChart, 
        #weightChart, #downtimeChart, #powerDonutChart, #powerLineChart, 
        #steamTableContainer {
            color: #1F2937;
        }
        
        /* Table styling */
        .table-container {
            width: 100%;
            height: 90%;
            overflow: hidden;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            color: #1F2937;
            font-size: 1.1rem;
        }
        
        th {
            background-color: #2563EB;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            position: sticky;
            top: 0;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #E5E7EB;
        }
        
        tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        
        tr:hover {
            background-color: #F3F4F6;
        }
        
        /* Progress bar for transition */
        .progress-bar-container {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background-color: #E5E7EB;
            z-index: 100;
        }
        
        .progress-bar {
            height: 100%;
            background-color: #2563EB;
            width: 0%;
            transition: width linear;
        }
    </style>
</head>
<body>
    <div class="slide-container">
        <!-- Header shared across all slides -->
        <div class="header">
            <img src="images/logo.png" alt="MMB Logo" class="logo">
            <div class="date-time" id="datetime">Loading...</div>
        </div>
        
        <!-- Slide 1: OEE Xưởng -->
        <div class="slide" id="slide1">
            <h1>Dashboard Xưởng Mì MMB</h1>
            <div class="chart-title">OEE Xưởng</div>
            <div class="chart-container">
                <canvas id="oeeChart"></canvas>
            </div>
        </div>
        
        <!-- Slide 2: OEE Theo Line -->
        <div class="slide" id="slide2">
            <h1>Dashboard Xưởng Mì MMB</h1>
            <div class="chart-title">OEE Theo Line</div>
            <div class="chart-container">
                <canvas id="oeeByLineChart"></canvas>
            </div>
        </div>
        
        <!-- Slide 3: Hơi/Sp theo xưởng -->
        <div class="slide" id="slide3">
            <h1>Dashboard Xưởng Mì MMB</h1>
            <div class="chart-title">Hơi/Sp theo xưởng</div>
            <div class="chart-container">
                <canvas id="steamChart"></canvas>
            </div>
        </div>
        
        <!-- Slide 4: Lượng Hơi theo Khu vực -->
        <div class="slide" id="slide4">
            <h1>Dashboard Xưởng Mì MMB</h1>
            <div class="chart-title">Lượng Hơi theo Khu vực</div>
            <div class="chart-container">
                <canvas id="steamUsageChart"></canvas>
            </div>
        </div>
        
        <!-- Slide 5: Trọng Lượng Các Line -->
        <div class="slide" id="slide5">
            <h1>Dashboard Xưởng Mì MMB</h1>
            <div class="chart-title">Trọng Lượng Các Line</div>
            <div class="chart-container">
                <canvas id="weightChart"></canvas>
            </div>
        </div>
        
        <!-- Slide 6: Downtime Xưởng -->
        <div class="slide" id="slide6">
            <h1>Dashboard Xưởng Mì MMB</h1>
            <div class="chart-title">Downtime Xưởng</div>
            <div class="chart-container">
                <canvas id="downtimeChart"></canvas>
            </div>
        </div>
        
        <!-- Slide 7: Điện Năng Theo Khu Vực và Trend -->
        <div class="slide" id="slide7">
            <h1>Dashboard Xưởng Mì MMB</h1>
            <div class="chart-title">Điện Năng Theo Khu Vực và Trend</div>
            <div class="chart-container" style="display: flex; gap: 20px">
                <div style="flex: 0.4">
                    <canvas id="powerDonutChart"></canvas>
                </div>
                <div style="flex: 0.6">
                    <canvas id="powerLineChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Slide 8: Chi Tiết Hơi sử dụng Theo khu vực -->
        <div class="slide" id="slide8">
            <h1>Dashboard Xưởng Mì MMB</h1>
            <div class="chart-title">Chi Tiết Hơi sử dụng Theo khu vực</div>
            <div class="chart-container" id="steamTableContainer">
                <div class="table-container">
                    <table id="steamTable">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>L1_Hấp</th>
                                <th>L1_Chiên</th>
                                <th>L2_Hấp</th>
                                <th>L2_Chiên</th>
                                <th>L3_Hấp</th>
                                <th>L3_Chiên</th>
                                <th>L5_Hấp</th>
                                <th>L5_Chiên</th>
                                <th>L6_Hấp</th>
                                <th>L6_Chiên</th>
                                <th>Trí_Việt</th>
                                <th>Mắm</th>
                                <th>Tổng_F2</th>
                                <th>Cl_F2</th>
                            </tr>
                        </thead>
                        <tbody id="steamTableContent">
                            <!-- Data will be loaded via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Slide indicators -->
        <div class="slide-indicator">
            <div class="indicator" data-slide="1"></div>
            <div class="indicator" data-slide="2"></div>
            <div class="indicator" data-slide="3"></div>
            <div class="indicator" data-slide="4"></div>
            <div class="indicator" data-slide="5"></div>
            <div class="indicator" data-slide="6"></div>
            <div class="indicator" data-slide="7"></div>
            <div class="indicator" data-slide="8"></div>
        </div>
        
        <!-- Progress bar for transition timing -->
        <div class="progress-bar-container">
            <div class="progress-bar" id="slideProgress"></div>
        </div>
    </div>
    
    <!-- Auto refresh script -->
    <script src="assets/js/auto-refresh.js?v=<?php echo time(); ?>"></script>
    
    <!-- Load required scripts -->
    <script src="assets/js/Oee_Chart.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/oeeByLine.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/Steam_Chart.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/steamUsageChart.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/Weight_Chart.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/Downtime_Chart.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/Power_Chart.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/Power_Line_Chart.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/Steam_table.js?v=<?php echo time(); ?>"></script>
    
    <!-- TV Slideshow Script -->
    <script>
        // Avoid caching
        window.onload = function() {
            // Add a timestamp to force refresh
            if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_RELOAD) {
                // If it's a normal reload (F5), add cache-busting parameter
                if (!window.location.href.includes('nocache=')) {
                    const timestamp = new Date().getTime();
                    const separator = window.location.href.includes('?') ? '&' : '?';
                    window.location.href = window.location.href + separator + 'nocache=' + timestamp;
                    return;
                }
            }
            
            // Normal initialization
            initSlideshow();
        };
        
        // Slideshow configuration
        const slideInterval = 30000; // 30 seconds in milliseconds
        let currentSlide = 1;
        let totalSlides = 8;
        let slideTimer;
        
        // Function to update date and time
        function updateDateTime() {
            const now = new Date();
            const formattedDate = now.toLocaleDateString('vi-VN', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            const formattedTime = now.toLocaleTimeString('vi-VN', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
            
            document.getElementById('datetime').textContent = `${formattedDate} - ${formattedTime}`;
        }
        
        // Show a specific slide
        function showSlide(slideNumber) {
            // Hide all slides
            document.querySelectorAll('.slide').forEach(slide => {
                slide.classList.remove('active');
            });
            
            // Show the requested slide
            document.getElementById(`slide${slideNumber}`).classList.add('active');
            
            // Update indicators
            document.querySelectorAll('.indicator').forEach(indicator => {
                indicator.classList.remove('active');
            });
            document.querySelector(`.indicator[data-slide="${slideNumber}"]`).classList.add('active');
            
            // Reset and start progress bar
            const progressBar = document.getElementById('slideProgress');
            progressBar.style.width = '0%';
            
            // Animate progress bar
            setTimeout(() => {
                progressBar.style.transition = `width ${slideInterval}ms linear`;
                progressBar.style.width = '100%';
            }, 50);
            
            // Update current slide number
            currentSlide = slideNumber;
        }
        
        // Function to advance to next slide
        function nextSlide() {
            let nextSlideNumber = currentSlide + 1;
            if (nextSlideNumber > totalSlides) {
                nextSlideNumber = 1; // Loop back to first slide
                // Force reload the page every full cycle to get fresh data
                window.location.reload();
                return;
            }
            showSlide(nextSlideNumber);
        }
        
        // Initialize slideshow
        function initSlideshow() {
            // Show first slide
            showSlide(1);
            
            // Set up slide interval
            slideTimer = setInterval(nextSlide, slideInterval);
            
            // Set up datetime updater
            updateDateTime();
            setInterval(updateDateTime, 1000);
            
            // Initialize all charts
            initializeCharts();
            
            // Set up indicator clicks
            document.querySelectorAll('.indicator').forEach(indicator => {
                indicator.addEventListener('click', () => {
                    const slideNumber = parseInt(indicator.getAttribute('data-slide'));
                    showSlide(slideNumber);
                    
                    // Reset the timer when manually changing slides
                    clearInterval(slideTimer);
                    slideTimer = setInterval(nextSlide, slideInterval);
                });
            });
            
            // Refresh data every 10 minutes
            setInterval(refreshAllData, 600000); // 10 minutes
        }
        
        // Initialize all charts
        function initializeCharts() {
            try {
                console.log('Initializing charts...');
                // Load data for period 'today'
                const period = 'today';
                
                // Initialize charts
                initOEEChart();
                updateOEEChart(period);
                
                initOEEByLineChart();
                updateOEEByLineChart(period);
                
                initSteamChart();
                updateSteamChart(period);
                
                initSteamUsageChart();
                updateSteamUsageChart(period);
                
                initWeightChart();
                updateWeightChart('shift');
                
                initDowntimeChart();
                updateDowntimeChart(period);
                
                initPowerDonutChart();
                updatePowerDonutChart(period);
                
                initPowerLineChart();
                updatePowerLineChart(period);
                
                updateSteamTable(period);
                
                console.log('Charts initialized successfully');
            } catch (error) {
                console.error('Error initializing charts:', error);
            }
        }
        
        // Refresh all chart data
        function refreshAllData() {
            const period = 'today';
            
            updateOEEChart(period);
            updateOEEByLineChart(period);
            updateSteamChart(period);
            updateSteamUsageChart(period);
            updateWeightChart('shift');
            updateDowntimeChart(period);
            updatePowerDonutChart(period);
            updatePowerLineChart(period);
            updateSteamTable(period);
        }
        
        // Handle window load event
        // window.addEventListener('load', initSlideshow);
        
        // Handle visibility change to pause/resume slideshow
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Page is hidden, clear interval
                clearInterval(slideTimer);
            } else {
                // Page is visible again, restart interval and reload
                window.location.reload();
            }
        });
    </script>
</body>
</html>