<!-- Load charts in correct order -->
<script src="assets/js/FS/Status_line.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/FS/Overview_Cards.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/FS/Oee_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/FS/Steam_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/FS/steamUsageChart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/FS/Downtime_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/FS/downtime_table.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/FS/Power_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/FS/Power_Line_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/FS/servo_temperature_chart.js?v=<?php echo time(); ?>"></script>
<!-- Load main.js last -->
<script src="assets/js/FS/updateSpeedChartData.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/FS/charts.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/FS/Main.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/FS/Speed_Chart.js?v=<?php echo time(); ?>"></script>
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