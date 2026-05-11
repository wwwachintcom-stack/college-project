<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('admin');
$u = me();

$total_users   = col('users')->countDocuments([]);
$total_doctors = col('users')->countDocuments(['role'=>'doctor']);
$total_pats    = col('users')->countDocuments(['role'=>'patient']);
$today_appts   = col('appointments')->countDocuments(['appointment_date'=>['$gte'=>today(),'$lt'=>tomorrow()]]);
$pending_bills = col('bills')->countDocuments(['payment_status'=>'pending']);
$in_queue      = col('waiting_room')->countDocuments(['status'=>['$in'=>['waiting','in_progress']]]);

$revRes = col('bills')->aggregate([['$group'=>['_id'=>null,'t'=>['$sum'=>'$paid_amount']]]]);
$total_rev = $revRes[0]['t']??0;
$todRevRes = col('bills')->aggregate([['$match'=>['created_at'=>['$gte'=>today(),'$lt'=>tomorrow()]]],['$group'=>['_id'=>null,'t'=>['$sum'=>'$paid_amount']]]]);
$today_rev = $todRevRes[0]['t']??0;

$recentAppts = col('appointments')->aggregate([
    ['$sort'   => ['created_at'=>-1]],
    ['$limit'  => 8],
    ['$lookup' => ['from'=>'users','localField'=>'patient_id','foreignField'=>'_id','as'=>'pat']],
    ['$lookup' => ['from'=>'users','localField'=>'doctor_id', 'foreignField'=>'_id','as'=>'doc']],
    ['$unwind' => ['path'=>'$pat','preserveNullAndEmptyArrays'=>true]],
    ['$unwind' => ['path'=>'$doc','preserveNullAndEmptyArrays'=>true]],
]);

$monthlyRev = col('bills')->aggregate([
    ['$match' => ['created_at'=>['$gte'=>new MongoDB\BSON\UTCDateTime(strtotime('-6 months')*1000)]]],
    ['$group' => ['_id'=>['y'=>['$year'=>'$created_at'],'m'=>['$month'=>'$created_at']],'rev'=>['$sum'=>'$paid_amount']]],
    ['$sort'  => ['_id.y'=>1,'_id.m'=>1]],
]);
$mn=['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$cMonths=[]; $cRevs=[];
foreach($monthlyRev as $r){ $cMonths[]=$mn[$r['_id']['m']]; $cRevs[]=(float)$r['rev']; }

$statusBd = col('appointments')->aggregate([['$group'=>['_id'=>'$status','c'=>['$sum'=>1]]]]);
$pLabels=[]; $pData=[]; $pColors=[];
$cm=['pending'=>'#f59e0b','confirmed'=>'#10b981','in_progress'=>'#6366f1','completed'=>'#06b6d4','cancelled'=>'#ef4444'];
foreach($statusBd as $s){ $pLabels[]=statusLabel($s['_id']); $pData[]=(int)$s['c']; $pColors[]=$cm[$s['_id']]??'#94a3b8'; }

$pageTitle='Admin Dashboard';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Admin Dashboard — MediCare</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_admin.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">

<div class="card" style="background:linear-gradient(135deg,#7c3aed,#6366f1);color:#fff;margin-bottom:22px;border:none">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
        <div><h2 style="font-size:21px;margin-bottom:3px">Admin Dashboard 🛡️</h2><p style="opacity:.8;font-size:13px">Real-time clinic analytics</p></div>
        <div style="text-align:right"><div style="font-size:26px;font-weight:800">₹<?= number_format($today_rev,0) ?></div><div style="font-size:12px;opacity:.8">Today's Revenue</div></div>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
    <div class="stat-card"><div class="stat-icon si-purple"><i class="fas fa-users"></i></div><div><div class="stat-val"><?= $total_users ?></div><div class="stat-lbl">Total Users</div></div></div>
    <div class="stat-card"><div class="stat-icon si-green"><i class="fas fa-user-md"></i></div><div><div class="stat-val"><?= $total_doctors ?></div><div class="stat-lbl">Doctors</div></div></div>
    <div class="stat-card"><div class="stat-icon si-blue"><i class="fas fa-user-injured"></i></div><div><div class="stat-val"><?= $total_pats ?></div><div class="stat-lbl">Patients</div></div></div>
    <div class="stat-card"><div class="stat-icon si-cyan"><i class="fas fa-calendar-day"></i></div><div><div class="stat-val"><?= $today_appts ?></div><div class="stat-lbl">Today's Appts</div></div></div>
    <div class="stat-card"><div class="stat-icon si-green"><i class="fas fa-rupee-sign"></i></div><div><div class="stat-val">₹<?= number_format($total_rev,0) ?></div><div class="stat-lbl">Total Revenue</div></div></div>
    <div class="stat-card"><div class="stat-icon si-orange"><i class="fas fa-file-invoice-dollar"></i></div><div><div class="stat-val"><?= $pending_bills ?></div><div class="stat-lbl">Pending Bills</div></div></div>
    <div class="stat-card"><div class="stat-icon si-red"><i class="fas fa-door-open"></i></div><div><div class="stat-val"><?= $in_queue ?></div><div class="stat-lbl">In Queue</div></div></div>
    <div class="stat-card"><div class="stat-icon si-purple"><i class="fas fa-chart-line"></i></div><div><div class="stat-val">₹<?= number_format($today_rev,0) ?></div><div class="stat-lbl">Today Revenue</div></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:22px;margin-bottom:22px">
    <div class="card"><h3 style="font-size:15px;font-weight:700;margin-bottom:14px">Revenue (Last 6 Months)</h3><canvas id="revChart" height="200"></canvas></div>
    <div class="card"><h3 style="font-size:15px;font-weight:700;margin-bottom:14px">Appointment Status</h3><canvas id="statusChart" height="200"></canvas></div>
</div>

<div class="card">
    <div class="page-header" style="margin-bottom:14px">
        <h3 style="font-size:15px;font-weight:700">Recent Appointments</h3>
        <a href="appointments.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach($recentAppts as $a): ?>
            <tr>
                <td><strong><?= htmlspecialchars($a['pat']['name']??'') ?></strong></td>
                <td>Dr. <?= htmlspecialchars($a['doc']['name']??'') ?></td>
                <td><?= fmtDate($a['appointment_date'],'d M Y') ?></td>
                <td><?= date('h:i A',strtotime($a['appointment_time'])) ?></td>
                <td><span class="badge <?= badgeClass($a['status']) ?>"><?= statusLabel($a['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div></div></div>
<script>
new Chart(document.getElementById('revChart'),{type:'bar',data:{labels:<?= json_encode($cMonths?:['Jan','Feb','Mar','Apr','May','Jun']) ?>,datasets:[{label:'Revenue (₹)',data:<?= json_encode($cRevs?:[0,0,0,0,0,0]) ?>,backgroundColor:'rgba(99,102,241,.8)',borderRadius:6}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
new Chart(document.getElementById('statusChart'),{type:'doughnut',data:{labels:<?= json_encode($pLabels?:['No Data']) ?>,datasets:[{data:<?= json_encode($pData?:[1]) ?>,backgroundColor:<?= json_encode($pColors?:['#e2e8f0']) ?>,borderWidth:0}]},options:{responsive:true,plugins:{legend:{position:'bottom'}},cutout:'65%'}});
</script>
<script src="/assets/js/app.js"></script>
</body></html>
