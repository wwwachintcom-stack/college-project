<?php
require_once '../config/config.php';
requireRole('reception');
$error=''; $success='';

$patients = col('users')->find(['role'=>'patient'],['sort'=>['name'=>1]]);

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $patId  = trim($_POST['patient_id']??'');
    $cf     = (float)($_POST['consultation_fee']??0);
    $mf     = (float)($_POST['medicine_fee']??0);
    $lf     = (float)($_POST['lab_fee']??0);
    $of     = (float)($_POST['other_fee']??0);
    $disc   = (float)($_POST['discount']??0);
    $paid   = (float)($_POST['paid_amount']??0);
    $method = $_POST['payment_method']??'cash';
    $notes  = trim($_POST['notes']??'');

    if (!$patId) { $error='Please select a patient.'; }
    else {
        $total  = $cf+$mf+$lf+$of-$disc;
        $status = $paid>=$total?'paid':($paid>0?'partial':'pending');
        $inv    = 'INV-'.date('Y').'-'.str_pad(col('bills')->countDocuments()+1,4,'0',STR_PAD_LEFT);
        col('bills')->insertOne(['patient_id'=>toOid($patId),'appointment_id'=>null,'invoice_number'=>$inv,'consultation_fee'=>$cf,'medicine_fee'=>$mf,'lab_fee'=>$lf,'other_fee'=>$of,'discount'=>$disc,'total_amount'=>$total,'paid_amount'=>$paid,'payment_status'=>$status,'payment_method'=>$method,'notes'=>$notes,'created_at'=>now()]);
        $success = "Invoice $inv created!";
    }
}

$recent = col('bills')->aggregate([
    ['$sort'   => ['created_at'=>-1]],
    ['$limit'  => 10],
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
<?php include '../includes/sidebar_reception.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>Billing</h1><p>Create patient invoices</p></div></div>
<?php if($error):?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if($success):?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:22px">
    <div class="card">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:18px">Create Invoice</h3>
        <form method="POST" id="billForm">
            <div class="form-group"><label class="form-label">Patient <span style="color:var(--danger)">*</span></label>
                <select name="patient_id" class="form-control" required>
                    <option value="">— Select patient —</option>
                    <?php foreach($patients as $p): ?><option value="<?= oid($p['_id']) ?>"><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['phone']??'') ?>)</option><?php endforeach; ?>
                </select></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group"><label class="form-label">Consultation (₹)</label><input type="number" name="consultation_fee" class="form-control" value="0" min="0" oninput="calc()"></div>
                <div class="form-group"><label class="form-label">Medicine (₹)</label><input type="number" name="medicine_fee" class="form-control" value="0" min="0" oninput="calc()"></div>
                <div class="form-group"><label class="form-label">Lab (₹)</label><input type="number" name="lab_fee" class="form-control" value="0" min="0" oninput="calc()"></div>
                <div class="form-group"><label class="form-label">Other (₹)</label><input type="number" name="other_fee" class="form-control" value="0" min="0" oninput="calc()"></div>
                <div class="form-group"><label class="form-label">Discount (₹)</label><input type="number" name="discount" class="form-control" value="0" min="0" oninput="calc()"></div>
                <div class="form-group"><label class="form-label">Total (₹)</label><input type="number" id="totalDisp" class="form-control" value="0" readonly style="background:var(--bg);font-weight:700"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group"><label class="form-label">Paid Amount (₹)</label><input type="number" name="paid_amount" class="form-control" value="0" min="0"></div>
                <div class="form-group"><label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-control"><option value="cash">Cash</option><option value="card">Card</option><option value="upi">UPI</option><option value="insurance">Insurance</option></select></div>
            </div>
            <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-control" placeholder="Additional notes..."></textarea></div>
            <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fas fa-file-invoice-dollar"></i> Generate Invoice</button>
        </form>
    </div>
    <div class="card">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:14px">Recent Bills</h3>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Invoice</th><th>Patient</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach($recent as $b): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($b['invoice_number']??'') ?></strong></td>
                    <td><?= htmlspecialchars($b['pat']['name']??'') ?></td>
                    <td>₹<?= number_format($b['total_amount']??0,0) ?></td>
                    <td><span class="badge <?= badgeClass($b['payment_status']??'pending') ?>"><?= statusLabel($b['payment_status']??'pending') ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div></div></div>
<script>
function calc(){
    const f=n=>parseFloat(document.querySelector('[name="'+n+'"]').value)||0;
    document.getElementById('totalDisp').value=Math.max(0,f('consultation_fee')+f('medicine_fee')+f('lab_fee')+f('other_fee')-f('discount'));
}
</script>
<script src="/assets/js/app.js"></script>
</body></html>
