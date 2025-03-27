<!-- Load charts in correct order -->
<script src="assets/js/CSD/Status_line.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/CSD/Overview_Cards.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/CSD/Oee_Chart.js?v=<?php echo time(); ?>"></script>

<script src="assets/js/CSD/Steam_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/CSD/steamUsageChart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/CSD/Downtime_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/CSD/downtime_table.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/CSD/Power_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/CSD/Power_Line_Chart.js?v=<?php echo time(); ?>"></script>
<!-- Thêm tham chiếu đến file JavaScript CO2 và BRIX Chart -->
<script src="assets/js/CSD/co2_ir_chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/CSD/brix_ir_chart.js?v=<?php echo time(); ?>"></script>




<!-- Load main.js last -->
<script src="assets/js/CSD/updateSpeedChartData.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/CSD/charts.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/CSD/Main.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/CSD/Speed_Chart.js?v=<?php echo time(); ?>"></script>
<?php
require_once 'includes/visitor_counter.php';
$counter = new VisitorCounter();
$active_visitors = $counter->updateVisitor();
?>

<!-- Visitor Counter -->
<?php if (isAdmin()): ?> <!-- Chỉ admin mới thấy được link -->
    <a href="login_statistics.php" class="fixed bottom-2 right-2 bg-blue-600 text-white px-2 py-1 rounded-full shadow-lg text-sm hover:bg-blue-700 transition-colors cursor-pointer">
        <div class="flex items-center gap-1">
            <div class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-xs"><?php echo $active_visitors; ?></span>
        </div>
    </a>
<?php else: ?>
    <div class="fixed bottom-2 right-2 bg-blue-600 text-white px-2 py-1 rounded-full shadow-lg text-sm">
        <div class="flex items-center gap-1">
            <div class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-xs"><?php echo $active_visitors; ?></span>
        </div>
    </div>
<?php endif; ?>
</body>
</html>