<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('patient');
$u   = me();
$uid = toOid($u['id']);

$prescs = col('prescriptions')->aggregate([
    ['$match'  => ['patient_id'=>$uid]],
    ['$sort'   => ['created_at'=>-1]],
    ['$lookup' => ['from'=>'users','localField'=>'doctor_id','foreignField'=>'_id','as'=>'doc']],
    ['$lookup' => ['from'=>'prescription_medicines','localField'=>'_id','foreignField'=>'prescription_id','as'=>'meds']],
    ['$unwind' => ['path'=>'$doc','preserveNullAndEmptyArrays'=>true]],
]);

$pageTitle = 'My Prescriptions';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Prescriptions — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_patient.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">

<div class="page-header">
    <div><h1>My Prescriptions</h1><p>Your digital prescriptions</p></div>
</div>

<?php if(empty($prescs)): ?>
<div class="card" style="text-align:center;padding:60px">
    <i class="fas fa-file-prescription" style="font-size:48px;color:var(--gray-light);margin-bottom:16px"></i>
    <h3 style="color:var(--gray)">No prescriptions yet</h3>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:16px">
    <?php foreach($prescs as $p): ?>
    <div class="card">
        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px">
            <div>
                <div style="font-weight:700;font-size:15px">Dr. <?= htmlspecialchars($p['doc']['name']??'') ?></div>
                <div style="font-size:13px;color:var(--gray)"><?= fmtDate($p['created_at'],'d M Y') ?></div>
                <?php if(!empty($p['diagnosis'])): ?>
                <div style="font-size:13px;color:var(--primary);margin-top:4px"><strong>Diagnosis:</strong> <?= htmlspecialchars($p['diagnosis']) ?></div>
                <?php endif; ?>
            </div>
            <span class="badge badge-primary"><?= count($p['meds']??[]) ?> medicine(s)</span>
        </div>
        <?php if(!empty($p['meds'])): ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Instructions</th></tr></thead>
                <tbody>
                <?php foreach($p['meds'] as $m): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($m['medicine_name']??'') ?></strong></td>
                    <td><?= htmlspecialchars($m['dosage']??'') ?></td>
                    <td><?= htmlspecialchars($m['frequency']??'') ?></td>
                    <td><?= htmlspecialchars($m['duration']??'') ?></td>
                    <td><?= htmlspecialchars($m['instructions']??'') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php if(!empty($p['notes'])): ?>
        <div style="margin-top:10px;padding:10px;background:var(--bg);border-radius:var(--radius-sm);font-size:13px"><strong>Notes:</strong> <?= htmlspecialchars($p['notes']) ?></div>
        <?php endif; ?>
        <?php if(!empty($p['follow_up_date'])): ?>
        <div style="margin-top:8px;font-size:13px;color:var(--warning)"><i class="fas fa-calendar-check"></i> Follow-up: <?= fmtDate($p['follow_up_date'],'d M Y') ?></div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
