<?php
require_once '../config/config.php';
requireRole('admin');
$success='';

if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['add_doctor'])) {
    $name  = trim($_POST['name']??'');
    $email = trim($_POST['email']??'');
    $pass  = $_POST['password']??'';
    $spec  = trim($_POST['specialization']??'');
    $fee   = (float)($_POST['consultation_fee']??0);

    if ($name && $email && $pass) {
        if (!col('users')->findOne(['email'=>$email])) {
            $res = col('users')->insertOne(['name'=>$name,'email'=>$email,'password'=>password_hash($pass,PASSWORD_DEFAULT),'role'=>'doctor','phone'=>trim($_POST['phone']??''),'is_active'=>true,'created_at'=>now()]);
            $docOid = toOid($res->insertedId);
            col('doctors')->insertOne(['user_id'=>$docOid,'specialization'=>$spec,'qualification'=>trim($_POST['qualification']??''),'experience_years'=>(int)($_POST['experience_years']??0),'consultation_fee'=>$fee,'bio'=>trim($_POST['bio']??'')]);
            $success = "Doctor $name added successfully!";
        } else { $success = "Email already exists."; }
    }
}
$pageTitle='Settings';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Settings — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_admin.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>Settings</h1><p>System configuration</p></div></div>
<?php if($success):?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
<div class="card" style="max-width:600px">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:18px"><i class="fas fa-user-md"></i> Add New Doctor</h3>
    <form method="POST">
        <input type="hidden" name="add_doctor" value="1">
        <div class="form-row">
            <div class="form-group"><label class="form-label">Full Name <span style="color:var(--danger)">*</span></label><input type="text" name="name" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Email <span style="color:var(--danger)">*</span></label><input type="email" name="email" class="form-control" required></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Password <span style="color:var(--danger)">*</span></label><input type="password" name="password" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Specialization</label><input type="text" name="specialization" class="form-control" placeholder="e.g. Cardiology"></div>
            <div class="form-group"><label class="form-label">Qualification</label><input type="text" name="qualification" class="form-control" placeholder="e.g. MBBS, MD"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Experience (years)</label><input type="number" name="experience_years" class="form-control" value="0"></div>
            <div class="form-group"><label class="form-label">Consultation Fee (₹)</label><input type="number" name="consultation_fee" class="form-control" value="500"></div>
        </div>
        <div class="form-group"><label class="form-label">Bio</label><textarea name="bio" class="form-control" placeholder="Brief description..."></textarea></div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Doctor</button>
    </form>
</div>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
