<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('patient');
$u   = me();
$uid = toOid($u['id']);

$wrList = col('waiting_room')->aggregate([
    ['$match'  => ['patient_id'=>$uid,'status'=>['$in'=>['waiting','in_progress']]]],
    ['$limit'  => 1],
    ['$lookup' => ['from'=>'users','localField'=>'doctor_id','foreignField'=>'_id','as'=>'doc']],
    ['$unwind' => ['path'=>'$doc','preserveNullAndEmptyArrays'=>true]],
]);
$wr = $wrList[0] ?? null;

$allQueue = [];
if ($wr) {
    $allQueue = col('waiting_room')->aggregate([
        ['$match'  => ['doctor_id'=>$wr['doctor_id'],'status'=>['$in'=>['waiting','in_progress']]]],
        ['$sort'   => ['token_number'=>1]],
        ['$lookup' => ['from'=>'users','localField'=>'patient_id','foreignField'=>'_id','as'=>'pat']],
        ['$unwind' => ['path'=>'$pat','preserveNullAndEmptyArrays'=>true]],
    ]);
    $pos = 0;
    foreach($allQueue as $i=>$q) {
        if(oid($q['patient_id'])===oid($uid)) { $pos=$i+1; break; }
    }
}

$pageTitle = 'Waiting Room';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Waiting Room — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_patient.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">

<div class="page-header">
    <div><h1>Waiting Room</h1><p>Live queue status</p></div>
</div>

<?php if(!$wr): ?>
<div class="card" style="text-align:center;padding:60px">
    <i class="fas fa-door-open" style="font-size:48px;color:var(--gray-light);margin-bottom:16px"></i>
    <h3 style="color:var(--gray)">You're not in any queue</h3>
    <p style="color:var(--gray-light);margin-top:8px">Book an appointment and check in at reception</p>
    <a href="book.php" class="btn btn-primary" style="margin-top:16px">Book Appointment</a>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:1fr 280px;gap:22px">
    <div class="card" style="background:var(--dark);color:#fff;border:none">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
            <h3 style="font-size:15px;font-weight:700">Current Queue</h3>
            <span class="live-badge"><span class="live-dot"></span> LIVE</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
            <?php foreach($allQueue as $q):
                $isMe     = oid($q['patient_id'])===oid($uid);
                $isActive = $q['status']==='in_progress';
            ?>
            <div style="display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:var(--radius-sm);background:<?= $isMe?'rgba(37,99,235,.25)':($isActive?'rgba(99,102,241,.15)':'rgba(255,255,255,.05)') ?>;border:1px solid <?= $isMe?'rgba(37,99,235,.5)':'rgba(255,255,255,.08)' ?>">
                <div style="width:38px;height:38px;border-radius:6px;background:<?= $isActive?'var(--primary)':'rgba(255,255,255,.15)' ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;flex-shrink:0">
                    <?= str_pad($q['token_number'],3,'0',STR_PAD_LEFT) ?>
                </div>
                <div style="flex:1;font-size:13px;font-weight:<?= $isMe?'700':'500' ?>">
                    <?= $isMe?'You ('.htmlspecialchars($q['pat']['name']??'').')':htmlspecialchars($q['pat']['name']??'') ?>
                </div>
                <span style="font-size:11px;opacity:.7"><?= $isActive?'🟢':'⏳' ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:14px">
        <div class="card" style="background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;border:none;text-align:center">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;opacity:.7;margin-bottom:8px">Your Token</div>
            <div style="font-size:64px;font-weight:900;line-height:1"><?= str_pad($wr['token_number'],3,'0',STR_PAD_LEFT) ?></div>
            <div style="margin-top:10px">
                <span class="badge <?= badgeClass($wr['status']) ?>" style="font-size:13px;padding:5px 14px">
                    <?= $wr['status']==='in_progress'?'🟢 It\'s your turn!':'⏳ Position #'.($pos??'?') ?>
                </span>
            </div>
            <div style="font-size:12px;opacity:.7;margin-top:10px"><i class="fas fa-user-md"></i> Dr. <?= htmlspecialchars($wr['doc']['name']??'') ?></div>
        </div>
        <div class="card">
            <h4 style="font-size:13px;font-weight:700;margin-bottom:10px">Queue Info</h4>
            <div style="font-size:13px;display:flex;flex-direction:column;gap:7px">
                <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">People ahead</span><strong><?= max(0,($pos??1)-1) ?></strong></div>
                <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Total in queue</span><strong><?= count($allQueue) ?></strong></div>
                <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Est. wait</span><strong>~<?= max(0,(($pos??1)-1)*15) ?> min</strong></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
</div></div></div>
<script>setTimeout(()=>location.reload(),20000);</script>
<script src="/assets/js/app.js"></script>
</body></html>
