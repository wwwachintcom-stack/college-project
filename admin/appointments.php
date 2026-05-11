<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('admin');

$filter = $_GET['filter']??'all';
$match  = [];
if ($filter==='today')    $match['appointment_date'] = ['$gte'=>today(),'$lt'=>tomorrow()];
if ($filter==='upcoming') $match['appointment_date'] = ['$gte'=>today()];
$status = $_GET['status']??'';
if ($status) $match['status'] = $status;

$appts = col('appointments')->aggregate([
    ['$match'  => $match],
    ['$sort'   => ['appointment_date'=>-1,'appointment_time'=>-1]],
    ['$limit'  => 50],
    ['$lookup' => ['from'=>'users','localField'=>'patient_id','foreignField'=>'_id','as'=>'pat']],
    ['$lookup' => ['from'=>'users','localField'=>'doctor_id', 'foreignField'=>'_id','as'=>'doc']],
    ['$unwind' => ['path'=>'$pat','preserveNullAndEmptyArrays'=>true]],
    ['$unwind' => ['path'=>'$doc','preserveNullAndEmptyArrays'=>true]],
]);
$pageTitle='Appointments';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Appointments — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_admin.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>All Appointments</h1></div></div>
<div style="display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap">
    <?php foreach(['all'=>'All','today'=>'Today','upcoming'=>'Upcoming'] as $k=>$v): ?>
    <a href="?filter=<?= $k ?>" class="btn <?= $filter===$k?'btn-primary':'btn-outline-gray' ?> btn-sm"><?= $v ?></a>
    <?php endforeach; ?>
    <select onchange="location='?filter=<?= $filter ?>&status='+this.value" class="form-control" style="width:160px;height:36px;padding:6px 12px;font-size:13px">
        <option value="">All Status</option>
        <?php foreach(['pending','confirmed','in_progress','completed','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= statusLabel($s) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Doctor</th><th>Token</th><th>Type</th><th>Status</th></tr></thead>
            <tbody>
            <?php if(empty($appts)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--gray)">No appointments found</td></tr>
            <?php else: foreach($appts as $a): ?>
            <tr>
                <td><?= fmtDate($a['appointment_date'],'d M Y') ?></td>
                <td><?= date('h:i A',strtotime($a['appointment_time'])) ?></td>
                <td><strong><?= htmlspecialchars($a['pat']['name']??'') ?></strong></td>
                <td>Dr. <?= htmlspecialchars($a['doc']['name']??'') ?></td>
                <td><span class="badge badge-primary">#<?= str_pad($a['token_number']??0,3,'0',STR_PAD_LEFT) ?></span></td>
                <td><span class="badge badge-gray"><?= ucfirst($a['type']??'online') ?></span></td>
                <td><span class="badge <?= badgeClass($a['status']) ?>"><?= statusLabel($a['status']) ?></span></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
