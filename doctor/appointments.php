<?php
require_once '../config/config.php';
requireRole('doctor');
$u   = me();
$uid = toOid($u['id']);

$filter = $_GET['filter'] ?? 'today';
$match  = ['doctor_id' => $uid];
if ($filter==='today')    { $match['appointment_date'] = ['$gte'=>today(),'$lt'=>tomorrow()]; }
if ($filter==='upcoming') { $match['appointment_date'] = ['$gte'=>today()]; }
if ($filter==='all')      { /* no date filter */ }

$appts = col('appointments')->aggregate([
    ['$match'  => $match],
    ['$sort'   => ['appointment_date'=>1,'appointment_time'=>1]],
    ['$lookup' => ['from'=>'users','localField'=>'patient_id','foreignField'=>'_id','as'=>'pat']],
    ['$unwind' => ['path'=>'$pat','preserveNullAndEmptyArrays'=>true]],
]);

$pageTitle = 'Appointments';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Appointments — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_doctor.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>Appointments</h1><p>Your patient schedule</p></div></div>
<div style="display:flex;gap:8px;margin-bottom:20px">
    <?php foreach(['today'=>'Today','upcoming'=>'Upcoming','all'=>'All'] as $k=>$v): ?>
    <a href="?filter=<?= $k ?>" class="btn <?= $filter===$k?'btn-primary':'btn-outline-gray' ?> btn-sm"><?= $v ?></a>
    <?php endforeach; ?>
</div>
<?php if(empty($appts)): ?>
<div class="card" style="text-align:center;padding:60px;color:var(--gray)">
    <i class="fas fa-calendar-times" style="font-size:40px;opacity:.3;margin-bottom:12px"></i><p>No appointments found</p>
</div>
<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Phone</th><th>Token</th><th>Reason</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach($appts as $a): ?>
            <tr>
                <td><?= fmtDate($a['appointment_date'],'d M Y') ?></td>
                <td><?= date('h:i A',strtotime($a['appointment_time'])) ?></td>
                <td><strong><?= htmlspecialchars($a['pat']['name']??'') ?></strong></td>
                <td><?= htmlspecialchars($a['pat']['phone']??'—') ?></td>
                <td><span class="badge badge-primary">#<?= str_pad($a['token_number']??0,3,'0',STR_PAD_LEFT) ?></span></td>
                <td><?= htmlspecialchars($a['reason']??'—') ?></td>
                <td><span class="badge <?= badgeClass($a['status']) ?>"><?= statusLabel($a['status']) ?></span></td>
                <td><a href="prescriptions.php?patient=<?= oid($a['patient_id']) ?>&appt=<?= oid($a['_id']) ?>" class="btn btn-primary btn-sm"><i class="fas fa-file-prescription"></i></a></td>
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
