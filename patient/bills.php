<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('patient');
$u   = me();
$uid = toOid($u['id']);

$bills = col('bills')->aggregate([
    ['$match'  => ['patient_id'=>$uid]],
    ['$sort'   => ['created_at'=>-1]],
    ['$lookup' => ['from'=>'appointments','localField'=>'appointment_id','foreignField'=>'_id','as'=>'appt']],
    ['$unwind' => ['path'=>'$appt','preserveNullAndEmptyArrays'=>true]],
    ['$lookup' => ['from'=>'users','localField'=>'appt.doctor_id','foreignField'=>'_id','as'=>'doc']],
    ['$unwind' => ['path'=>'$doc','preserveNullAndEmptyArrays'=>true]],
]);

$pageTitle = 'My Bills';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Bills — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_patient.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">

<div class="page-header">
    <div><h1>My Bills</h1><p>Billing history and invoices</p></div>
</div>

<?php if(empty($bills)): ?>
<div class="card" style="text-align:center;padding:60px">
    <i class="fas fa-file-invoice-dollar" style="font-size:48px;color:var(--gray-light);margin-bottom:16px"></i>
    <h3 style="color:var(--gray)">No bills found</h3>
</div>
<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Invoice</th><th>Date</th><th>Doctor</th><th>Total</th><th>Paid</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach($bills as $b): ?>
            <tr>
                <td><strong><?= htmlspecialchars($b['invoice_number']??'—') ?></strong></td>
                <td><?= fmtDate($b['created_at'],'d M Y') ?></td>
                <td>Dr. <?= htmlspecialchars($b['doc']['name']??'N/A') ?></td>
                <td><strong>₹<?= number_format($b['total_amount']??0,0) ?></strong></td>
                <td>₹<?= number_format($b['paid_amount']??0,0) ?></td>
                <td><span class="badge <?= badgeClass($b['payment_status']??'pending') ?>"><?= statusLabel($b['payment_status']??'pending') ?></span></td>
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
