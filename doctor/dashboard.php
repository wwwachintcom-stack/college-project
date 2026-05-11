<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('doctor');
$u   = me();
$uid = toOid($u['id']);

$today_count = col('appointments')->countDocuments(['doctor_id'=>$uid,'appointment_date'=>['$gte'=>today(),'$lt'=>tomorrow()],'status'=>['$ne'=>'cancelled']]);
$pending     = col('appointments')->countDocuments(['doctor_id'=>$uid,'status'=>'pending']);
$total_pats  = count(col('appointments')->distinct('patient_id',['doctor_id'=>$uid]));
$total_presc = col('prescriptions')->countDocuments(['doctor_id'=>$uid]);

$appts = col('appointments')->aggregate([
    ['$match'  => ['doctor_id'=>$uid,'appointment_date'=>['$gte'=>today(),'$lt'=>tomorrow()],'status'=>['$ne'=>'cancelled']]],
    ['$sort'   => ['appointment_time'=>1]],
    ['$lookup' => ['from'=>'users','localField'=>'patient_id','foreignField'=>'_id','as'=>'pat']],
    ['$unwind' => ['path'=>'$pat','preserveNullAndEmptyArrays'=>true]],
]);

$queue = col('waiting_room')->aggregate([
    ['$match'  => ['doctor_id'=>$uid,'status'=>['$in'=>['waiting','in_progress']]]],
    ['$sort'   => ['token_number'=>1]],
    ['$lookup' => ['from'=>'users','localField'=>'patient_id','foreignField'=>'_id','as'=>'pat']],
    ['$unwind' => ['path'=>'$pat','preserveNullAndEmptyArrays'=>true]],
]);

$pageTitle = 'Doctor Dashboard';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Doctor Dashboard — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_doctor.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">

<div class="card" style="background:linear-gradient(135deg,#059669,#10b981);color:#fff;margin-bottom:22px;border:none">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
        <div>
            <h2 style="font-size:21px;margin-bottom:3px">Good day, Dr. <?= htmlspecialchars(explode(' ',$u['name'])[0]) ?>! 👨‍⚕️</h2>
            <p style="opacity:.8;font-size:13px">You have <?= $today_count ?> appointment<?= $today_count!==1?'s':'' ?> today.</p>
        </div>
        <a href="appointments.php" class="btn btn-white"><i class="fas fa-calendar-alt"></i> View Schedule</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon si-blue"><i class="fas fa-calendar-day"></i></div><div><div class="stat-val"><?= $today_count ?></div><div class="stat-lbl">Today's Appointments</div></div></div>
    <div class="stat-card"><div class="stat-icon si-orange"><i class="fas fa-clock"></i></div><div><div class="stat-val"><?= $pending ?></div><div class="stat-lbl">Pending</div></div></div>
    <div class="stat-card"><div class="stat-icon si-green"><i class="fas fa-users"></i></div><div><div class="stat-val"><?= $total_pats ?></div><div class="stat-lbl">Total Patients</div></div></div>
    <div class="stat-card"><div class="stat-icon si-purple"><i class="fas fa-file-prescription"></i></div><div><div class="stat-val"><?= $total_presc ?></div><div class="stat-lbl">Prescriptions</div></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:22px">
    <div class="card">
        <div class="page-header" style="margin-bottom:14px">
            <h3 style="font-size:15px;font-weight:700">Today's Schedule</h3>
            <span class="badge badge-primary"><?= date('d M Y') ?></span>
        </div>
        <?php if(empty($appts)): ?>
        <div style="text-align:center;padding:40px;color:var(--gray)">
            <i class="fas fa-calendar-check" style="font-size:38px;opacity:.3;margin-bottom:12px"></i>
            <p>No appointments today</p>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px">
            <?php foreach($appts as $a): ?>
            <div style="display:flex;align-items:center;gap:13px;padding:13px;background:var(--bg);border-radius:var(--radius-sm);border:1px solid var(--border)">
                <div style="width:44px;height:44px;background:var(--success);border-radius:var(--radius-sm);display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;flex-shrink:0;font-size:11px;font-weight:700;text-align:center;line-height:1.3">
                    <?= date('h:i',strtotime($a['appointment_time'])) ?><br><?= date('A',strtotime($a['appointment_time'])) ?>
                </div>
                <div style="flex:1">
                    <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($a['pat']['name']??'') ?></div>
                    <div style="font-size:12px;color:var(--gray)"><?= htmlspecialchars($a['pat']['phone']??'') ?> • Token #<?= str_pad($a['token_number']??0,3,'0',STR_PAD_LEFT) ?></div>
                    <?php if(!empty($a['reason'])): ?><div style="font-size:12px;color:var(--primary);margin-top:2px"><?= htmlspecialchars($a['reason']) ?></div><?php endif; ?>
                </div>
                <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end">
                    <span class="badge <?= badgeClass($a['status']) ?>"><?= statusLabel($a['status']) ?></span>
                    <a href="prescriptions.php?patient=<?= oid($a['patient_id']) ?>&appt=<?= oid($a['_id']) ?>" class="btn btn-primary btn-sm"><i class="fas fa-file-prescription"></i> Prescribe</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="card" style="background:var(--dark);color:#fff;border:none">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <h3 style="font-size:14px;font-weight:700">Waiting Room</h3>
            <span class="live-badge"><span class="live-dot"></span> LIVE</span>
        </div>
        <?php if(empty($queue)): ?>
        <div style="text-align:center;padding:28px;opacity:.5"><i class="fas fa-door-open" style="font-size:28px;margin-bottom:8px"></i><p style="font-size:13px">Queue empty</p></div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:7px">
            <?php foreach($queue as $q): $active=$q['status']==='in_progress'; ?>
            <div style="display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:var(--radius-sm);background:<?= $active?'rgba(99,102,241,.2)':'rgba(255,255,255,.05)' ?>;border:1px solid <?= $active?'rgba(99,102,241,.4)':'rgba(255,255,255,.08)' ?>">
                <div style="width:34px;height:34px;border-radius:6px;background:<?= $active?'var(--primary)':'rgba(255,255,255,.15)' ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;flex-shrink:0"><?= str_pad($q['token_number'],3,'0',STR_PAD_LEFT) ?></div>
                <div style="flex:1;font-size:13px;font-weight:500"><?= htmlspecialchars($q['pat']['name']??'') ?></div>
                <span><?= $active?'🟢':'⏳' ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <a href="waiting_room.php" class="btn btn-outline" style="margin-top:14px;width:100%;justify-content:center;border-color:rgba(255,255,255,.3);color:#fff">Manage Queue</a>
    </div>
</div>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
