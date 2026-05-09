<?php
require_once '../config/config.php';
requireRole('admin');

$bills = col('bills')->aggregate([
    ['$sort'   => ['created_at'=>-1]],
    ['$limit'  => 50],
    ['$lookup' => ['from'=>'users','localField'=>'patient_id','foreignField'=>'_id','as'=>'pat']],
    ['$unwind' => ['path'=>'$pat','preserveNullAndEmptyArrays'=>true]],
]);
$pageTitle='Billing';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Billing — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_admin.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>Billing</h1><p>All invoices</p></div></div>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Invoice</th><th>Date</th><th>Patient</th><th>Total</th><th>Paid</th><th>Method</th><th>Status</th></tr></thead>
            <tbody>
            <?php if(empty($bills)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--gray)">No bills found</td></tr>
            <?php else: foreach($bills as $b): ?>
            <tr>
                <td><strong><?= htmlspecialchars($b['invoice_number']??'') ?></strong></td>
                <td><?= fmtDate($b['created_at'],'d M Y') ?></td>
                <td><?= htmlspecialchars($b['pat']['name']??'') ?></td>
                <td>₹<?= number_format($b['total_amount']??0,0) ?></td>
                <td>₹<?= number_format($b['paid_amount']??0,0) ?></td>
                <td><?= ucfirst($b['payment_method']??'cash') ?></td>
                <td><span class="badge <?= badgeClass($b['payment_status']??'pending') ?>"><?= statusLabel($b['payment_status']??'pending') ?></span></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
