<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('reception');
$error=''; $success='';

$doctors = col('users')->aggregate([
    ['$match'  => ['role'=>'doctor','is_active'=>true]],
    ['$lookup' => ['from'=>'doctors','localField'=>'_id','foreignField'=>'user_id','as'=>'info']],
    ['$unwind' => ['path'=>'$info','preserveNullAndEmptyArrays'=>true]],
    ['$sort'   => ['name'=>1]],
]);

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name   = trim($_POST['patient_name']??'');
    $phone  = trim($_POST['phone']??'');
    $did    = trim($_POST['doctor_id']??'');
    $reason = trim($_POST['reason']??'');

    if (!$name || !$did) { $error='Patient name and doctor are required.'; }
    else {
        $docOid = toOid($did);
        $pat = col('users')->findOne(['phone'=>$phone,'role'=>'patient']);
        if (!$pat) {
            $res = col('users')->insertOne(['name'=>$name,'email'=>'walkin_'.time().'@medicare.local','password'=>password_hash(uniqid(),PASSWORD_DEFAULT),'role'=>'patient','phone'=>$phone,'is_active'=>true,'created_at'=>now()]);
            $patOid = toOid($res->insertedId);
        } else { $patOid = toOid(oid($pat['_id'])); }

        $tokenRes = col('appointments')->aggregate([['$match'=>['doctor_id'=>$docOid,'appointment_date'=>['$gte'=>today(),'$lt'=>tomorrow()]]],['$group'=>['_id'=>null,'max'=>['$max'=>'$token_number']]]]);
        $token = ($tokenRes[0]['max']??0)+1;

        $apptRes = col('appointments')->insertOne(['patient_id'=>$patOid,'doctor_id'=>$docOid,'appointment_date'=>today(),'appointment_time'=>date('H:i'),'token_number'=>$token,'status'=>'confirmed','type'=>'walk_in','reason'=>$reason,'notes'=>'','created_at'=>now()]);
        $apptOid = toOid($apptRes->insertedId);

        col('waiting_room')->insertOne(['appointment_id'=>$apptOid,'patient_id'=>$patOid,'doctor_id'=>$docOid,'token_number'=>$token,'check_in_time'=>now(),'status'=>'waiting']);
        $success = "Walk-in registered! Token #".str_pad($token,3,'0',STR_PAD_LEFT)." for $name";
    }
}
$pageTitle='Walk-in Registration';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Walk-in — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_reception.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>Walk-in Registration</h1><p>Register a walk-in patient</p></div><a href="dashboard.php" class="btn btn-outline-gray"><i class="fas fa-arrow-left"></i> Back</a></div>
<?php if($error):?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if($success):?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
<div class="card" style="max-width:560px">
    <form method="POST">
        <div class="form-group"><label class="form-label">Patient Name <span style="color:var(--danger)">*</span></label><input type="text" name="patient_name" class="form-control" placeholder="Full name" value="<?= htmlspecialchars($_POST['patient_name']??'') ?>" required></div>
        <div class="form-group"><label class="form-label">Phone Number</label><input type="tel" name="phone" class="form-control" placeholder="9876543210" value="<?= htmlspecialchars($_POST['phone']??'') ?>"></div>
        <div class="form-group"><label class="form-label">Select Doctor <span style="color:var(--danger)">*</span></label>
            <select name="doctor_id" class="form-control" required>
                <option value="">— Choose doctor —</option>
                <?php foreach($doctors as $d): ?>
                <option value="<?= oid($d['_id']) ?>" <?= ($_POST['doctor_id']??'')===oid($d['_id'])?'selected':'' ?>>Dr. <?= htmlspecialchars($d['name']) ?> — <?= htmlspecialchars($d['info']['specialization']??'General') ?></option>
                <?php endforeach; ?>
            </select></div>
        <div class="form-group"><label class="form-label">Reason / Chief Complaint</label><textarea name="reason" class="form-control" placeholder="Describe symptoms..."><?= htmlspecialchars($_POST['reason']??'') ?></textarea></div>
        <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fas fa-walking"></i> Register Walk-in</button>
    </form>
</div>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
