<?php
require_once '../config/config.php';
requireRole('admin');

$totalRev  = (col('bills')->aggregate([['$group'=>['_id'=>null,'t'=>['$sum'=>'$paid_amount']]]])[0]['t']??0);
$totalAppts= col('appointments')->countDocuments([]);
$completed = col('appointments')->countDocuments(['status'=>'completed']);
$cancelled = col('appointments')->countDocuments(['status'=>'cancelled']);
$rate      = $totalAppts>0 ? round($completed/$totalAppts*100,1) : 0;

$monthly = col('bills')->aggregate([
    ['$group' => ['_id'=>['y'=>['$year'=>'$created_at'],'m'=>['$month'=>'$created_at']],'rev'=>['$sum'=>'$paid_amount'],'count'=>['$sum'=>1]]],
    ['$sort'  => ['_id.y'=>-1,'_id.m'=>-1]],
    ['$limit' => 12],
]);
$mn=['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$pageTitle='Reports';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Reports — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_admin.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>Reports</h1><p>Clinic analytics overview</p></div></div>
<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon si-green"><i class="fas fa-rupee-sign"></i></div><div><div class="stat-val">₹<?= number_format($totalRev,0) ?></div><div class="stat-lbl">Total Revenue</div></div></div>
    <div class="stat-card"><div class="stat-icon si-blue"><i class="fas fa-calendar-check"></i></div><div><div class="stat-val"><?= $totalAppts ?></div><div class="stat-lbl">Total Appointments</div></div></div>
    <div class="stat-card"><div class="stat-icon si-green"><i class="fas fa-check-circle"></i></div><div><div class="stat-val"><?= $completed ?></div><div class="stat-lbl">Completed</div></div></div>
    <div class="stat-card"><div class="stat-icon si-red"><i class="fas fa-times-circle"></i></div><div><div class="stat-val"><?= $cancelled ?></div><div class="stat-lbl">Cancelled</div></div></div>
</div>
<div class="card">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:14px">Monthly Revenue Report</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Month</th><th>Year</th><th>Invoices</th><th>Revenue</th></tr></thead>
            <tbody>
            <?php if(empty($monthly)): ?>
            <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--gray)">No data yet</td></tr>
            <?php else: foreach($monthly as $r): ?>
            <tr>
                <td><strong><?= $mn[$r['_id']['m']] ?></strong></td>
                <td><?= $r['_id']['y'] ?></td>
                <td><?= $r['count'] ?></td>
                <td><strong>₹<?= number_format($r['rev'],0) ?></strong></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
