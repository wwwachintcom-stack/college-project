<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('admin');

$doctors = col('users')->aggregate([
    ['$match'  => ['role'=>'doctor']],
    ['$lookup' => ['from'=>'doctors','localField'=>'_id','foreignField'=>'user_id','as'=>'info']],
    ['$unwind' => ['path'=>'$info','preserveNullAndEmptyArrays'=>true]],
    ['$sort'   => ['name'=>1]],
]);
$pageTitle='Doctors';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Doctors — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_admin.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>Doctors</h1><p>All registered doctors</p></div></div>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Specialization</th><th>Qualification</th><th>Experience</th><th>Fee</th><th>Status</th></tr></thead>
            <tbody>
            <?php if(empty($doctors)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--gray)">No doctors found</td></tr>
            <?php else: foreach($doctors as $d): ?>
            <tr>
                <td><div style="display:flex;align-items:center;gap:9px"><div class="avatar" style="font-size:12px"><?= initials($d['name']) ?></div><strong><?= htmlspecialchars($d['name']) ?></strong></div></td>
                <td><?= htmlspecialchars($d['email']) ?></td>
                <td><?= htmlspecialchars($d['info']['specialization']??'—') ?></td>
                <td><?= htmlspecialchars($d['info']['qualification']??'—') ?></td>
                <td><?= ($d['info']['experience_years']??0) ?> yrs</td>
                <td>₹<?= number_format($d['info']['consultation_fee']??0,0) ?></td>
                <td><span class="badge <?= ($d['is_active']??true)?'badge-success':'badge-danger' ?>"><?= ($d['is_active']??true)?'Active':'Inactive' ?></span></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
