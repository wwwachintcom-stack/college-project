<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('patient');
$u   = me();
$uid = toOid($u['id']);

// Cancel appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cancel_id'])) {
    $oid = toOid($_POST['cancel_id']);
    if ($oid) col('appointments')->updateOne(['_id'=>$oid,'patient_id'=>$uid],['$set'=>['status'=>'cancelled']]);
    header('Location: appointments.php?msg=cancelled'); exit;
}

$filter = $_GET['filter'] ?? 'all';
$match  = ['patient_id' => $uid];
if ($filter === 'upcoming')  $match['appointment_date'] = ['$gte' => today()];
if ($filter === 'past')      $match['appointment_date'] = ['$lt'  => today()];
if ($filter === 'cancelled') $match['status'] = 'cancelled';

$appts = col('appointments')->aggregate([
    ['$match'  => $match],
    ['$sort'   => ['appointment_date' => -1]],
    ['$lookup' => ['from'=>'users','localField'=>'doctor_id','foreignField'=>'_id','as'=>'doc']],
    ['$lookup' => ['from'=>'doctors','localField'=>'doctor_id','foreignField'=>'user_id','as'=>'dinfo']],
    ['$unwind' => ['path'=>'$doc',  'preserveNullAndEmptyArrays'=>true]],
    ['$unwind' => ['path'=>'$dinfo','preserveNullAndEmptyArrays'=>true]],
]);

$pageTitle = 'My Appointments';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>My Appointments — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_patient.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">

<div class="page-header">
    <div><h1>My Appointments</h1><p>View and manage your appointments</p></div>
    <a href="book.php" class="btn btn-primary"><i class="fas fa-plus"></i> Book New</a>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i>
    <?= $_GET['msg']==='cancelled' ? 'Appointment cancelled.' : 'Done.' ?>
</div>
<?php endif; ?>

<!-- Filter tabs -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php foreach(['all'=>'All','upcoming'=>'Upcoming','past'=>'Past','cancelled'=>'Cancelled'] as $k=>$v): ?>
    <a href="?filter=<?= $k ?>" class="btn <?= $filter===$k?'btn-primary':'btn-outline-gray' ?> btn-sm"><?= $v ?></a>
    <?php endforeach; ?>
</div>

<?php if (empty($appts)): ?>
<div class="card" style="text-align:center;padding:60px">
    <i class="fas fa-calendar-times" style="font-size:48px;color:var(--gray-light);margin-bottom:16px"></i>
    <h3 style="color:var(--gray)">No appointments found</h3>
    <a href="book.php" class="btn btn-primary" style="margin-top:16px">Book Appointment</a>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:12px">
    <?php foreach($appts as $a):
        $canCancel = in_array($a['status'],['pending','confirmed']);
    ?>
    <div class="card" style="padding:18px">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
            <div style="width:50px;height:50px;background:var(--primary);border-radius:var(--radius);display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;flex-shrink:0">
                <span style="font-size:15px;font-weight:800;line-height:1"><?= fmtDate($a['appointment_date'],'d') ?></span>
                <span style="font-size:10px;opacity:.8"><?= fmtDate($a['appointment_date'],'M') ?></span>
            </div>
            <div style="flex:1;min-width:180px">
                <div style="font-weight:700;font-size:15px">Dr. <?= htmlspecialchars($a['doc']['name']??'') ?></div>
                <div style="font-size:13px;color:var(--gray)"><?= htmlspecialchars($a['dinfo']['specialization']??'General') ?> • <?= date('h:i A',strtotime($a['appointment_time'])) ?></div>
                <div style="font-size:12px;color:var(--gray-light);margin-top:3px">Token #<?= str_pad($a['token_number']??0,3,'0',STR_PAD_LEFT) ?><?= !empty($a['reason']) ? ' • '.$a['reason'] : '' ?></div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
                <span class="badge <?= badgeClass($a['status']) ?>"><?= statusLabel($a['status']) ?></span>
                <?php if($canCancel): ?>
                <form method="POST" onsubmit="return confirm('Cancel this appointment?')">
                    <input type="hidden" name="cancel_id" value="<?= oid($a['_id']) ?>">
                    <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Cancel</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
