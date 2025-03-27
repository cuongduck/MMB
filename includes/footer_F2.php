<!-- Load charts in correct order -->
<script src="assets/js/F2/Status_line.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/Overview_Cards.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/oeeByLine.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/realtime.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/Oee_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/Steam_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/steamUsageChart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/Weight_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/Downtime_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/downtime_table.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/Power_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/Power_Line_Chart.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/Power_Table.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/Steam_table.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/Weight_table.js?v=<?php echo time(); ?>"></script>



<!-- Load main.js last -->
<script src="assets/js/F2/charts.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/F2/Main.js?v=<?php echo time(); ?>"></script>
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