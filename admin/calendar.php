<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle       = 'Calendar';
$activeAdminPage = 'calendar';

$month = (int)($_GET['month'] ?? date('n'));
$year  = (int)($_GET['year']  ?? date('Y'));
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$firstDay   = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = (int)date('t', $firstDay);
$startDow   = (int)date('w', $firstDay); // 0=Sun

$apptResult = $conn->query("SELECT scheduled_at, customer_name, type FROM appointments WHERE MONTH(scheduled_at)=$month AND YEAR(scheduled_at)=$year AND status != 'cancelled'");
$appts = [];
while ($a = $apptResult->fetch_assoc()) {
    $day = (int)date('j', strtotime($a['scheduled_at']));
    $appts[$day][] = $a;
}

$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

include 'includes/admin-header.php';
?>
<div class="panel">
    <div class="panel-head">
        <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="btn btn-ghost btn-sm">‹ Prev</a>
        <h2><?php echo date('F Y', $firstDay); ?></h2>
        <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="btn btn-ghost btn-sm">Next ›</a>
    </div>
    <div class="panel-body">
        <div class="cal-grid">
            <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
                <div class="cal-head"><?php echo $d; ?></div>
            <?php endforeach; ?>
            <?php for ($e = 0; $e < $startDow; $e++): ?><div class="cal-empty"></div><?php endfor; ?>
            <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                <div class="cal-day <?php echo $day === (int)date('j') && $month === (int)date('n') && $year === (int)date('Y') ? 'today' : ''; ?>">
                    <span class="cal-day-num"><?php echo $day; ?></span>
                    <?php if (!empty($appts[$day])): foreach ($appts[$day] as $ap): ?>
                        <div class="cal-event"><?php echo clean($ap['customer_name']); ?> <em>(<?php echo clean($ap['type']); ?>)</em></div>
                    <?php endforeach; endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>
<style>
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;}
.cal-head{font-weight:700;text-align:center;padding:6px;font-size:.8rem;color:var(--text-muted);}
.cal-empty{background:transparent;}
.cal-day{background:var(--card-bg);border:1px solid var(--border);border-radius:8px;padding:8px;min-height:80px;position:relative;}
.cal-day.today{border-color:var(--primary);background:rgba(var(--primary-rgb),.08);}
.cal-day-num{font-weight:700;font-size:.9rem;display:block;margin-bottom:4px;}
.cal-event{background:var(--primary);color:#fff;border-radius:4px;padding:2px 5px;font-size:.7rem;margin-top:2px;line-height:1.3;}
</style>
<?php include 'includes/admin-footer.php'; ?>
