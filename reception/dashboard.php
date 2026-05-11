<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('reception');
$u = me();

$today_appts = col('appointments')->countDocuments(['appointment_date'=>['$gte'=>today(),'$lt'=>tomorrow()],'status'=>['$ne'=>'cancelled']]);
$in_queue    = col('waiting_room')->countDocuments(['status'=>['$in'=>['waiting','in_progress']]]);
$total_pats  = col('users')->countDocuments(['role'=>'patient']);
$todayRevRes = col('bills')->aggregate([['$match'=>['created_at'=>['$gte'=>today(),'$lt'=>tomorrow()]]],['$group'=>['_id'=>null,'t'=>['$sum'=>'$paid_amount']]]]);
$today_rev   = $todayRevRes[0]['t'] ?? 0;

$appts = col('appointments')->aggregate([
    ['$match'  => ['appointment_date'=>['$gte'=>today(),'$lt'=>tomorrow()],'status'=>['$ne'=>'cancelled']]],
    ['$sort'   => ['appointment_time'=>1]],
    ['$limit'  => 15],
    ['$lookup' => ['from'=>'users','localField'=>'patient_id','foreignField'=>'_id','as'=>'pat']],
    ['$lookup' => ['from'=>'users','localField'=>'doctor_id', 'foreignField'=>'_id','as'=>'doc']],
    ['$unwind' => ['path'=>'$pat','preserveNullAndEmptyArrays'=>true]],
    ['$unwind' => ['path'=>'$doc','preserveNullAndEmptyArrays'=>true]],
]);

$queue = col('waiting_room')->aggregate([
    ['$match'  => ['status'=>['$in'=>['waiting','in_progress']]]],
    ['$sort'   => ['token_number'=>1]],
    ['$lookup' => ['from'=>'users','localField'=>'patient_id','foreignField'=>'_id','as'=>'pat']],
    ['$lookup' => ['from'=>'users','localField'=>'doctor_id', 'foreignField'=>'_id','as'=>'doc']],
    ['$unwind' => ['path'=>'$pat','preserveNullAndEmptyArrays'=>true]],
    ['$unwind' => ['path'=>'$doc','preserveNullAndEmptyArrays'=>true]],
]);

$pageTitle = 'Reception Dashboard';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Reception Dashboard — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_reception.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">

<div class="card" style="background:linear-gradient(135deg,#0891b2,#06b6d4);color:#fff;margin-bottom:22px;border:none">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
        <div><h2 style="font-size:21px;margin-bottom:3px">Reception Desk 🏥</h2><p style="opacity:.8;font-size:13px"><?= date('l, d F Y') ?></p></div>
        <div style="display:flex;gap:10px">
            <a href="walkin.php"  class="btn btn-white"><i class="fas fa-walking"></i> Walk-in</a>
            <a href="billing.php" class="btn btn-white"><i class="fas fa-file-invoice-dollar"></i> Billing</a>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon si-blue"><i class="fas fa-calendar-day"></i></div><div><div class="stat-val"><?= $today_appts ?></div><div class="stat-lbl">Today's Appointments</div></div></div>
    <div class="stat-card"><div class="stat-icon si-orange"><i class="fas fa-door-open"></i></div><div><div class="stat-val"><?= $in_queue ?></div><div class="stat-lbl">In Queue</div></div></div>
    <div class="stat-card"><div class="stat-icon si-purple"><i class="fas fa-users"></i></div><div><div class="stat-val"><?= $total_pats ?></div><div class="stat-lbl">Total Patients</div></div></div>
    <div class="stat-card"><div class="stat-icon si-green"><i class="fas fa-rupee-sign"></i></div><div><div class="stat-val">₹<?= number_format($today_rev,0) ?></div><div class="stat-lbl">Today's Collection</div></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 360px;gap:22px">
    <div class="card">
        <div class="page-header" style="margin-bottom:14px">
            <h3 style="font-size:15px;font-weight:700">Today's Appointments</h3>
            <a href="appointments.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <?php if(empty($appts)): ?>
        <div style="text-align:center;padding:40px;color:var(--gray)"><i class="fas fa-calendar-times" style="font-size:38px;opacity:.3;margin-bottom:12px"></i><p>No appointments today</p></div>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Token</th><th>Time</th><th>Patient</th><th>Doctor</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach($appts as $a): ?>
                <tr>
                    <td><span class="badge badge-primary">#<?= str_pad($a['token_number']??0,3,'0',STR_PAD_LEFT) ?></span></td>
                    <td><?= date('h:i A',strtotime($a['appointment_time'])) ?></td>
                    <td><strong><?= htmlspecialchars($a['pat']['name']??'') ?></strong></td>
                    <td>Dr. <?= htmlspecialchars($a['doc']['name']??'') ?></td>
                    <td><span class="badge <?= badgeClass($a['status']) ?>"><?= statusLabel($a['status']) ?></span></td>
                    <td><?php if(in_array($a['status'],['pending','confirmed'])): ?><a href="checkin.php?appt=<?= oid($a['_id']) ?>" class="btn btn-success btn-sm">Check-in</a><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <div class="card" style="background:var(--dark);color:#fff;border:none">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <h3 style="font-size:14px;font-weight:700">Live Queue</h3>
            <span class="live-badge"><span class="live-dot"></span> LIVE</span>
        </div>
        <?php if(empty($queue)): ?>
        <div style="text-align:center;padding:28px;opacity:.5"><i class="fas fa-door-open" style="font-size:28px;margin-bottom:8px"></i><p style="font-size:13px">Queue empty</p></div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:7px">
            <?php foreach($queue as $q): $active=$q['status']==='in_progress'; ?>
            <div style="display:flex;align-items:center;gap:9px;padding:9px 12px;border-radius:var(--radius-sm);background:<?= $active?'rgba(99,102,241,.2)':'rgba(255,255,255,.05)' ?>;border:1px solid rgba(255,255,255,.08)">
                <div style="width:34px;height:34px;border-radius:6px;background:<?= $active?'var(--primary)':'rgba(255,255,255,.15)' ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;flex-shrink:0"><?= str_pad($q['token_number'],3,'0',STR_PAD_LEFT) ?></div>
                <div style="flex:1;min-width:0"><div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($q['pat']['name']??'') ?></div><div style="font-size:11px;opacity:.6">Dr. <?= htmlspecialchars($q['doc']['name']??'') ?></div></div>
                <span><?= $active?'🟢':'⏳' ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <a href="waiting_room.php" class="btn btn-outline" style="margin-top:14px;width:100%;justify-content:center;border-color:rgba(255,255,255,.3);color:#fff">Manage Queue</a>
    </div>
</div>
</div></div></div>
<script>setTimeout(()=>location.reload(),30000);</script>
<script src="/assets/js/app.js"></script>
</body></html>
