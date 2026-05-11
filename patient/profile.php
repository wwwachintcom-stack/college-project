<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('patient');
$u    = me();
$uid  = toOid($u['id']);
$user = col('users')->findOne(['_id'=>$uid]);
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name   = trim($_POST['name']??'');
    $phone  = trim($_POST['phone']??'');
    $gender = $_POST['gender']??'';
    $dob    = $_POST['dob']??'';
    $addr   = trim($_POST['address']??'');
    $newpw  = $_POST['new_password']??'';
    $curpw  = $_POST['current_password']??'';

    $set = ['name'=>$name,'phone'=>$phone,'gender'=>$gender,'address'=>$addr];
    if ($dob) $set['dob'] = mDate($dob);

    if (!empty($newpw)) {
        if (!password_verify($curpw, $user['password']??'')) { $error='Current password incorrect.'; }
        elseif (strlen($newpw)<6) { $error='New password min 6 chars.'; }
        else { $set['password'] = password_hash($newpw, PASSWORD_DEFAULT); }
    }
    if (!$error) {
        col('users')->updateOne(['_id'=>$uid],['$set'=>$set]);
        $_SESSION['name'] = $name;
        $user = col('users')->findOne(['_id'=>$uid]);
        $success = 'Profile updated!';
    }
}
$pageTitle = 'My Profile';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Profile — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_patient.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>My Profile</h1><p>Update your information</p></div></div>
<?php if($error):?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if($success):?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:22px">
    <div class="card">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:18px">Personal Information</h3>
        <form method="POST">
            <div class="form-group"><label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']??'') ?>" required></div>
            <div class="form-group"><label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']??'') ?>" disabled></div>
            <div class="form-group"><label class="form-label">Phone</label>
                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']??'') ?>"></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select</option>
                        <?php foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $v=>$l): ?>
                        <option value="<?= $v ?>" <?= ($user['gender']??'')===$v?'selected':'' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="form-group"><label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="<?= isset($user['dob'])?fmtDate($user['dob'],'Y-m-d'):'' ?>"></div>
            </div>
            <div class="form-group"><label class="form-label">Address</label>
                <textarea name="address" class="form-control"><?= htmlspecialchars($user['address']??'') ?></textarea></div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        </form>
    </div>
    <div class="card">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:18px">Change Password</h3>
        <form method="POST">
            <input type="hidden" name="name" value="<?= htmlspecialchars($user['name']??'') ?>">
            <input type="hidden" name="phone" value="<?= htmlspecialchars($user['phone']??'') ?>">
            <input type="hidden" name="gender" value="<?= htmlspecialchars($user['gender']??'') ?>">
            <div class="form-group"><label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" placeholder="••••••••"></div>
            <div class="form-group"><label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Min 6 characters"></div>
            <button type="submit" class="btn btn-warning"><i class="fas fa-key"></i> Update Password</button>
        </form>
    </div>
</div>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
