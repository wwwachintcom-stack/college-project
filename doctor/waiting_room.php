<?php
require_once '../config/config.php';
requireRole('doctor');
$u   = me();
$uid = toOid($u['id']);

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $wid = toOid($_POST['wr_id']??'');
    $st  = $_POST['status']??'';
    if ($wid && in_array($st,['waiting','in_progress','done','left'])) {
        col('waiting_room')->updateOne(['_id'=>$wid],['$set'=>['status'=>$st]]);
        if ($st==='done') {
            $wr = col('waiting_room')->findOne(['_id'=>$wid]);
            if ($wr) col('appointments')->updateOne(['_id'=>$wr['appointment_id']],['$set'=>['status'=>'completed']]);
        }
    }
    header('Location: waiting_room.php'); exit;
}

$queue = col('waiting_room')->aggregate([
    ['$match'  => ['doctor_id'=>$uid,'status'=>['$in'=>['waiting','in_progress']]]],
    ['$sort'   => ['token_number'=>1]],
    ['$lookup' => ['from'=>'users','localField'=>'patient_id','foreignField'=>'_id','as'=>'pat']],
    ['$unwind' => ['path'=>'$pat','preserveNullAndEmptyArrays'=>true]],
]);

$pageTitle = 'Waiting Room';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Waiting Room — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_doctor.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header">
    <div><h1>Waiting Room</h1><p>Your patient queue</p></div>
    <span class="live-badge"><span class="live-dot"></span> LIVE — <?= count($queue) ?> in queue</span>
</div>
<?php if(empty($queue)): ?>
<div class="card" style="text-align:center;padding:60px;color:var(--gray)">
    <i class="fas fa-door-open" style="font-size:40px;opacity:.3;margin-bottom:12px"></i><p>Queue is empty</p>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:12px">
    <?php foreach($queue as $q): $active=$q['status']==='in_progress'; ?>
    <div class="card" style="padding:16px;border-left:4px solid <?= $active?'var(--primary)':'var(--border)' ?>">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
            <div style="width:50px;height:50px;border-radius:var(--radius);background:<?= $active?'var(--primary)':'var(--bg)' ?>;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:15px;color:<?= $active?'#fff':'var(--dark)' ?>;flex-shrink:0;border:2px solid <?= $active?'var(--primary)':'var(--border)' ?>">
                <?= str_pad($q['token_number'],3,'0',STR_PAD_LEFT) ?>
            </div>
            <div style="flex:1">
                <div style="font-weight:700;font-size:15px"><?= htmlspecialchars($q['pat']['name']??'') ?></div>
                <div style="font-size:13px;color:var(--gray)"><?= htmlspecialchars($q['pat']['phone']??'') ?></div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <?php if(!$active): ?>
                <form method="POST" style="display:inline"><input type="hidden" name="wr_id" value="<?= oid($q['_id']) ?>"><input type="hidden" name="status" value="in_progress"><button class="btn btn-primary btn-sm"><i class="fas fa-play"></i> Call In</button></form>
                <?php else: ?>
                <form method="POST" style="display:inline"><input type="hidden" name="wr_id" value="<?= oid($q['_id']) ?>"><input type="hidden" name="status" value="done"><button class="btn btn-success btn-sm"><i class="fas fa-check"></i> Done</button></form>
                <?php endif; ?>
                <form method="POST" style="display:inline"><input type="hidden" name="wr_id" value="<?= oid($q['_id']) ?>"><input type="hidden" name="status" value="left"><button class="btn btn-outline-gray btn-sm" onclick="return confirm('Mark as left?')"><i class="fas fa-sign-out-alt"></i> Left</button></form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
</div></div></div>
<script>setTimeout(()=>location.reload(),20000);</script>
<script src="/assets/js/app.js"></script>
</body></html>
