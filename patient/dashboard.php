<?php
require_once '../config/config.php';
requireRole('patient');
$u   = me();
$uid = toOid($u['id']);

$total   = col('appointments')->countDocuments(['patient_id'=>$uid]);
$upcoming= col('appointments')->countDocuments(['patient_id'=>$uid,'appointment_date'=>['$gte'=>today()],'status'=>['$nin'=>['cancelled','completed']]]);
$prescs  = col('prescriptions')->countDocuments(['patient_id'=>$uid]);

$appts = toArr(col('appointments')->aggregate([
    ['$match'  => ['patient_id'=>$uid,'appointment_date'=>['$gte'=>today()],'status'=>['$nin'=>['cancelled','completed']]]],
    ['$sort'   => ['appointment_date'=>1,'appointment_time'=>1]],
    ['$limit'  => 5],
    ['$lookup' => ['from'=>'users','localField'=>'doctor_id','foreignField'=>'_id','as'=>'doc']],
    ['$lookup' => ['from'=>'doctors','localField'=>'doctor_id','foreignField'=>'user_id','as'=>'dinfo']],
    ['$unwind' => ['path'=>'$doc',  'preserveNullAndEmptyArrays'=>true]],
    ['$unwind' => ['path'=>'$dinfo','preserveNullAndEmptyArrays'=>true]],
]));

$wrList = toArr(col('waiting_room')->aggregate([
    ['$match'  => ['patient_id'=>$uid,'status'=>['$in'=>['waiting','in_progress']]]],
    ['$limit'  => 1],
    ['$lookup' => ['from'=>'users','localField'=>'doctor_id','foreignField'=>'_id','as'=>'doc']],
    ['$unwind' => ['path'=>'$doc','preserveNullAndEmptyArrays'=>true]],
]));
$wr = $wrList[0] ?? null;

$pageTitle = 'Dashboard';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Dashboard — MediCare</title>
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar_patient.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">

    <!-- Banner -->
    <div class="card" style="background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;margin-bottom:22px;border:none">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
            <div>
                <h2 style="font-size:21px;margin-bottom:3px">Welcome back, <?= htmlspecialchars(explode(' ',$u['name'])[0]) ?>! 👋</h2>
                <p style="opacity:.8;font-size:13px">Here's your health summary for today — <?= date('l, d F Y') ?></p>
            </div>
            <a href="/patient/book.php" class="btn btn-white"><i class="fas fa-plus"></i> Book Appointment</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon si-purple"><i class="fas fa-calendar-alt"></i></div><div><div class="stat-val"><?= $total ?></div><div class="stat-lbl">Total Appointments</div></div></div>
        <div class="stat-card"><div class="stat-icon si-blue"><i class="fas fa-clock"></i></div><div><div class="stat-val"><?= $upcoming ?></div><div class="stat-lbl">Upcoming</div></div></div>
        <div class="stat-card"><div class="stat-icon si-green"><i class="fas fa-file-prescription"></i></div><div><div class="stat-val"><?= $prescs ?></div><div class="stat-lbl">Prescriptions</div></div></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:22px">
        <!-- Upcoming Appointments -->
        <div class="card">
            <div class="page-header" style="margin-bottom:16px">
                <h3 style="font-size:15px;font-weight:700">Upcoming Appointments</h3>
                <a href="/patient/appointments.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            <?php if (empty($appts)): ?>
            <div style="text-align:center;padding:40px;color:var(--gray)">
                <i class="fas fa-calendar-times" style="font-size:38px;opacity:.3;margin-bottom:12px"></i>
                <p style="margin-bottom:14px">No upcoming appointments</p>
                <a href="/patient/book.php" class="btn btn-primary btn-sm">Book Now</a>
            </div>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:10px">
                <?php foreach ($appts as $a): ?>
                <div style="display:flex;align-items:center;gap:13px;padding:13px;background:var(--bg);border-radius:var(--radius-sm);border:1px solid var(--border)">
                    <div style="width:46px;height:46px;background:var(--primary);border-radius:var(--radius-sm);display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;flex-shrink:0">
                        <span style="font-size:15px;font-weight:800;line-height:1"><?= fmtDate($a['appointment_date'],'d') ?></span>
                        <span style="font-size:10px;opacity:.8"><?= fmtDate($a['appointment_date'],'M') ?></span>
                    </div>
                    <div style="flex:1">
                        <div style="font-weight:600;font-size:14px">Dr. <?= htmlspecialchars($a['doc']['name']??'') ?></div>
                        <div style="font-size:12px;color:var(--gray)"><?= htmlspecialchars($a['dinfo']['specialization']??'General') ?> • <?= date('h:i A',strtotime($a['appointment_time'])) ?></div>
                    </div>
                    <span class="badge <?= badgeClass($a['status']) ?>"><?= statusLabel($a['status']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right -->
        <div style="display:flex;flex-direction:column;gap:16px">
            <?php if ($wr): ?>
            <div class="card" style="background:var(--dark);color:#fff;border:none;text-align:center">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;opacity:.6;margin-bottom:8px">
                    <span class="live-dot" style="display:inline-block;margin-right:5px"></span>Live Queue
                </div>
                <div style="font-size:60px;font-weight:900;color:#93c5fd;line-height:1"><?= str_pad($wr['token_number'],3,'0',STR_PAD_LEFT) ?></div>
                <div style="font-size:12px;opacity:.6;margin:6px 0">Your Token</div>
                <span class="badge <?= badgeClass($wr['status']) ?>" style="font-size:13px;padding:5px 14px">
                    <?= $wr['status']==='in_progress' ? '🟢 In Progress' : '⏳ Waiting' ?>
                </span>
                <div style="font-size:12px;opacity:.6;margin-top:10px"><i class="fas fa-user-md"></i> Dr. <?= htmlspecialchars($wr['doc']['name']??'') ?></div>
            </div>
            <?php endif; ?>
            <div class="card">
                <h3 style="font-size:14px;font-weight:700;margin-bottom:13px">Quick Actions</h3>
                <div style="display:flex;flex-direction:column;gap:9px">
                    <a href="/patient/book.php"          class="btn btn-primary btn-sm"><i class="fas fa-plus-circle"></i> Book Appointment</a>
                    <a href="/patient/prescriptions.php" class="btn btn-outline btn-sm"><i class="fas fa-file-prescription"></i> Prescriptions</a>
                    <a href="/patient/waiting_room.php"  class="btn btn-outline btn-sm"><i class="fas fa-door-open"></i> Waiting Room</a>
                </div>
            </div>
        </div>
    </div>

</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
