<?php
require_once '../config/config.php';
requireRole('doctor');
$u   = me();
$uid = toOid($u['id']);

$patIds   = col('appointments')->distinct('patient_id',['doctor_id'=>$uid]);
$search   = trim($_GET['search']??'');
$filter   = empty($patIds) ? ['_id'=>['$in'=>[]]] : ['_id'=>['$in'=>$patIds]];
if ($search) $filter['$or'] = [['name'=>['$regex'=>$search,'$options'=>'i']],['phone'=>['$regex'=>$search,'$options'=>'i']]];
$patients = col('users')->find($filter,['sort'=>['name'=>1]]);

$pageTitle = 'My Patients';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>My Patients — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_doctor.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>My Patients</h1><p><?= count($patients) ?> patients</p></div></div>
<form method="GET" style="margin-bottom:18px">
    <div class="search-wrap" style="max-width:360px">
        <i class="fas fa-search"></i>
        <input type="text" name="search" class="form-control" placeholder="Search by name or phone..." value="<?= htmlspecialchars($search) ?>">
    </div>
</form>
<?php if(empty($patients)): ?>
<div class="card" style="text-align:center;padding:60px;color:var(--gray)">
    <i class="fas fa-users" style="font-size:40px;opacity:.3;margin-bottom:12px"></i><p>No patients yet</p>
</div>
<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Phone</th><th>Gender</th><th>Appointments</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach($patients as $p):
                $pid = toOid(oid($p['_id']));
                $apptCount = col('appointments')->countDocuments(['patient_id'=>$pid,'doctor_id'=>$uid]);
            ?>
            <tr>
                <td><div style="display:flex;align-items:center;gap:9px"><div class="avatar" style="font-size:12px"><?= initials($p['name']) ?></div><strong><?= htmlspecialchars($p['name']) ?></strong></div></td>
                <td><?= htmlspecialchars($p['phone']??'—') ?></td>
                <td><?= ucfirst($p['gender']??'—') ?></td>
                <td><span class="badge badge-primary"><?= $apptCount ?></span></td>
                <td><a href="prescriptions.php?patient=<?= oid($p['_id']) ?>" class="btn btn-primary btn-sm"><i class="fas fa-file-prescription"></i> Prescribe</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
