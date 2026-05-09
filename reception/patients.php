<?php
require_once '../config/config.php';
requireRole('reception');

$search   = trim($_GET['search']??'');
$filter   = ['role'=>'patient'];
if ($search) $filter['$or'] = [['name'=>['$regex'=>$search,'$options'=>'i']],['phone'=>['$regex'=>$search,'$options'=>'i']],['email'=>['$regex'=>$search,'$options'=>'i']]];
$patients = col('users')->find($filter,['sort'=>['name'=>1]]);
$pageTitle='Patients';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Patients — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_reception.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>Patients</h1><p><?= count($patients) ?> registered patients</p></div></div>
<form method="GET" style="margin-bottom:18px">
    <div class="search-wrap" style="max-width:360px"><i class="fas fa-search"></i><input type="text" name="search" class="form-control" placeholder="Search name, phone, email..." value="<?= htmlspecialchars($search) ?>"></div>
</form>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Gender</th><th>Joined</th></tr></thead>
            <tbody>
            <?php if(empty($patients)): ?>
            <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--gray)">No patients found</td></tr>
            <?php else: foreach($patients as $p): ?>
            <tr>
                <td><div style="display:flex;align-items:center;gap:9px"><div class="avatar" style="font-size:12px"><?= initials($p['name']) ?></div><strong><?= htmlspecialchars($p['name']) ?></strong></div></td>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td><?= htmlspecialchars($p['phone']??'—') ?></td>
                <td><?= ucfirst($p['gender']??'—') ?></td>
                <td><?= fmtDate($p['created_at'],'d M Y') ?></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
