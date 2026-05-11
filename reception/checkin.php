<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('reception');
$error=''; $success=''; $appt=null;

$apptId = $_GET['appt'] ?? ($_POST['appt_id'] ?? '');
if ($apptId) {
    $apptOid = toOid($apptId);
    if ($apptOid) {
        $apptList = col('appointments')->aggregate([
            ['$match'  => ['_id'=>$apptOid]],
            ['$lookup' => ['from'=>'users','localField'=>'patient_id','foreignField'=>'_id','as'=>'pat']],
            ['$lookup' => ['from'=>'users','localField'=>'doctor_id', 'foreignField'=>'_id','as'=>'doc']],
            ['$unwind' => ['path'=>'$pat','preserveNullAndEmptyArrays'=>true]],
            ['$unwind' => ['path'=>'$doc','preserveNullAndEmptyArrays'=>true]],
        ]);
        $appt = $apptList[0] ?? null;
    }
}

if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['confirm_checkin'])) {
    $aOid = toOid($_POST['appt_id']??'');
    $aDoc = col('appointments')->findOne(['_id'=>$aOid]);
    if ($aDoc) {
        col('appointments')->updateOne(['_id'=>$aOid],['$set'=>['status'=>'confirmed']]);
        $exists = col('waiting_room')->findOne(['appointment_id'=>$aOid]);
        if (!$exists) {
            col('waiting_room')->insertOne(['appointment_id'=>$aOid,'patient_id'=>$aDoc['patient_id'],'doctor_id'=>$aDoc['doctor_id'],'token_number'=>$aDoc['token_number']??1,'check_in_time'=>now(),'status'=>'waiting']);
        }
        $success = 'Patient checked in! Token #'.str_pad($aDoc['token_number']??1,3,'0',STR_PAD_LEFT);
    }
}
$pageTitle='Patient Check-in';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Check-in — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_reception.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>Patient Check-in</h1></div><a href="dashboard.php" class="btn btn-outline-gray"><i class="fas fa-arrow-left"></i> Back</a></div>
<?php if($error):?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if($success):?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if($appt && !$success): ?>
<div class="card" style="max-width:480px">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:18px">Confirm Check-in</h3>
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:22px;font-size:14px">
        <?php foreach(['Patient'=>$appt['pat']['name']??'','Doctor'=>'Dr. '.($appt['doc']['name']??''),'Date & Time'=>fmtDate($appt['appointment_date'],'d M Y').' at '.date('h:i A',strtotime($appt['appointment_time'])),'Token'=>'#'.str_pad($appt['token_number']??0,3,'0',STR_PAD_LEFT)] as $l=>$v): ?>
        <div style="display:flex;justify-content:space-between;padding:10px;background:var(--bg);border-radius:var(--radius-sm)"><span style="color:var(--gray)"><?= $l ?></span><strong><?= htmlspecialchars($v) ?></strong></div>
        <?php endforeach; ?>
    </div>
    <form method="POST">
        <input type="hidden" name="appt_id" value="<?= oid($appt['_id']) ?>">
        <input type="hidden" name="confirm_checkin" value="1">
        <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fas fa-check-circle"></i> Confirm Check-in</button>
    </form>
</div>
<?php elseif(!$success): ?>
<div class="card" style="max-width:400px">
    <form method="GET">
        <div class="form-group"><label class="form-label">Appointment ID</label><input type="text" name="appt" class="form-control" placeholder="Paste appointment ID" required></div>
        <button type="submit" class="btn btn-primary btn-block">Find Appointment</button>
    </form>
</div>
<?php endif; ?>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
