<?php
require_once '../config/config.php';
requireRole('doctor');
$u    = me();
$uid  = toOid($u['id']);
$user = col('users')->findOne(['_id'=>$uid]);
$info = col('doctors')->findOne(['user_id'=>$uid]);
$error=''; $success='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name  = trim($_POST['name']??'');
    $phone = trim($_POST['phone']??'');
    $spec  = trim($_POST['specialization']??'');
    $qual  = trim($_POST['qualification']??'');
    $exp   = (int)($_POST['experience_years']??0);
    $fee   = (float)($_POST['consultation_fee']??0);
    $bio   = trim($_POST['bio']??'');
    $newpw = $_POST['new_password']??'';
    $curpw = $_POST['current_password']??'';

    $set = ['name'=>$name,'phone'=>$phone];
    if (!empty($newpw)) {
        if (!password_verify($curpw,$user['password']??'')) $error='Current password incorrect.';
        elseif (strlen($newpw)<6) $error='Min 6 chars.';
        else $set['password']=password_hash($newpw,PASSWORD_DEFAULT);
    }
    if (!$error) {
        col('users')->updateOne(['_id'=>$uid],['$set'=>$set]);
        $docSet = ['specialization'=>$spec,'qualification'=>$qual,'experience_years'=>$exp,'consultation_fee'=>$fee,'bio'=>$bio];
        if ($info) col('doctors')->updateOne(['user_id'=>$uid],['$set'=>$docSet]);
        else col('doctors')->insertOne(array_merge(['user_id'=>$uid],$docSet));
        $_SESSION['name']=$name;
        $user=col('users')->findOne(['_id'=>$uid]);
        $info=col('doctors')->findOne(['user_id'=>$uid]);
        $success='Profile updated!';
    }
}
$pageTitle='Doctor Profile';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Profile — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_doctor.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>My Profile</h1></div></div>
<?php if($error):?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if($success):?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<form method="POST">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:22px">
    <div class="card">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:16px">Personal Info</h3>
        <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']??'') ?>" required></div>
        <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']??'') ?>"></div>
        <div class="form-group"><label class="form-label">Specialization</label><input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($info['specialization']??'') ?>"></div>
        <div class="form-group"><label class="form-label">Qualification</label><input type="text" name="qualification" class="form-control" value="<?= htmlspecialchars($info['qualification']??'') ?>"></div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Experience (years)</label><input type="number" name="experience_years" class="form-control" value="<?= $info['experience_years']??0 ?>"></div>
            <div class="form-group"><label class="form-label">Consultation Fee (₹)</label><input type="number" name="consultation_fee" class="form-control" value="<?= $info['consultation_fee']??0 ?>"></div>
        </div>
        <div class="form-group"><label class="form-label">Bio</label><textarea name="bio" class="form-control"><?= htmlspecialchars($info['bio']??'') ?></textarea></div>
    </div>
    <div class="card">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:16px">Change Password</h3>
        <div class="form-group"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" placeholder="••••••••"></div>
        <div class="form-group"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" placeholder="Min 6 characters"></div>
    </div>
</div>
<div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Changes</button></div>
</form>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
