<?php
require_once '../config/config.php';
requireRole('doctor');
$u   = me();
$uid = toOid($u['id']);

$error = ''; $success = '';
$prePatient = $_GET['patient'] ?? '';
$preAppt    = $_GET['appt']    ?? '';

$patIds  = col('appointments')->distinct('patient_id',['doctor_id'=>$uid]);
$patients = col('users')->find(['_id'=>['$in'=>$patIds]],['sort'=>['name'=>1]]);

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $patId   = trim($_POST['patient_id']??'');
    $apptId  = trim($_POST['appt_id']??'');
    $diag    = trim($_POST['diagnosis']??'');
    $notes   = trim($_POST['notes']??'');
    $followup= trim($_POST['follow_up']??'');
    $meds    = $_POST['medicines']??[];

    if (empty($patId)) { $error='Please select a patient.'; }
    else {
        $patOid  = toOid($patId);
        $apptOid = $apptId ? toOid($apptId) : null;

        $prescData = ['patient_id'=>$patOid,'doctor_id'=>$uid,'appointment_id'=>$apptOid,'diagnosis'=>$diag,'notes'=>$notes,'follow_up_date'=>$followup?mDate($followup):null,'created_at'=>now()];
        $res = col('prescriptions')->insertOne($prescData);
        $prescId = toOid($res->insertedId);

        foreach($meds as $m) {
            if (!empty($m['name'])) {
                col('prescription_medicines')->insertOne(['prescription_id'=>$prescId,'medicine_name'=>$m['name'],'dosage'=>$m['dosage']??'','frequency'=>$m['frequency']??'','duration'=>$m['duration']??'','instructions'=>$m['instructions']??'']);
            }
        }
        if ($apptOid) col('appointments')->updateOne(['_id'=>$apptOid],['$set'=>['status'=>'completed']]);
        col('notifications')->insertOne(['user_id'=>$patOid,'title'=>'New Prescription','message'=>'Dr. '.htmlspecialchars($u['name']).' has written a prescription for you.','type'=>'prescription','is_read'=>false,'created_at'=>now()]);
        $success = 'Prescription saved!';
    }
}

$recent = col('prescriptions')->aggregate([
    ['$match'  => ['doctor_id'=>$uid]],
    ['$sort'   => ['created_at'=>-1]],
    ['$limit'  => 8],
    ['$lookup' => ['from'=>'users','localField'=>'patient_id','foreignField'=>'_id','as'=>'pat']],
    ['$lookup' => ['from'=>'prescription_medicines','localField'=>'_id','foreignField'=>'prescription_id','as'=>'meds']],
    ['$unwind' => ['path'=>'$pat','preserveNullAndEmptyArrays'=>true]],
]);

$pageTitle = 'Prescriptions';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Prescriptions — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_doctor.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>Write Prescription</h1><p>Create digital prescriptions</p></div></div>
<?php if($error):?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if($success):?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:22px">
    <div class="card">
        <form method="POST">
            <input type="hidden" name="appt_id" value="<?= htmlspecialchars($preAppt) ?>">
            <div class="form-group"><label class="form-label">Patient <span style="color:var(--danger)">*</span></label>
                <select name="patient_id" class="form-control" required>
                    <option value="">— Select patient —</option>
                    <?php foreach($patients as $p): $pid=oid($p['_id']); ?>
                    <option value="<?= $pid ?>" <?= $prePatient===$pid?'selected':'' ?>><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['phone']??'') ?>)</option>
                    <?php endforeach; ?>
                </select></div>
            <div class="form-group"><label class="form-label">Diagnosis</label>
                <input type="text" name="diagnosis" class="form-control" placeholder="e.g. Viral fever"></div>
            <div class="form-group">
                <label class="form-label">Medicines</label>
                <div id="medList" style="display:flex;flex-direction:column;gap:10px">
                    <div class="med-row" style="background:var(--bg);padding:12px;border-radius:var(--radius-sm);border:1px solid var(--border)">
                        <div class="form-row" style="margin-bottom:8px">
                            <input type="text" name="medicines[0][name]" class="form-control" placeholder="Medicine name" required>
                            <input type="text" name="medicines[0][dosage]" class="form-control" placeholder="Dosage">
                        </div>
                        <div class="form-row-3">
                            <input type="text" name="medicines[0][frequency]" class="form-control" placeholder="Frequency">
                            <input type="text" name="medicines[0][duration]" class="form-control" placeholder="Duration">
                            <input type="text" name="medicines[0][instructions]" class="form-control" placeholder="Instructions">
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addMed()" class="btn btn-outline-gray btn-sm" style="margin-top:8px"><i class="fas fa-plus"></i> Add Medicine</button>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Clinical Notes</label>
                    <textarea name="notes" class="form-control" placeholder="Additional notes..."></textarea></div>
                <div class="form-group"><label class="form-label">Follow-up Date</label>
                    <input type="date" name="follow_up" class="form-control" min="<?= date('Y-m-d') ?>"></div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-file-prescription"></i> Save Prescription</button>
        </form>
    </div>
    <div class="card">
        <h3 style="font-size:14px;font-weight:700;margin-bottom:14px">Recent Prescriptions</h3>
        <?php if(empty($recent)): ?>
        <div style="text-align:center;padding:30px;color:var(--gray);opacity:.5"><i class="fas fa-file-prescription" style="font-size:28px;margin-bottom:8px"></i><p style="font-size:13px">None yet</p></div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:9px">
            <?php foreach($recent as $p): ?>
            <div style="padding:11px;background:var(--bg);border-radius:var(--radius-sm);border:1px solid var(--border)">
                <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($p['pat']['name']??'') ?></div>
                <div style="font-size:12px;color:var(--gray)"><?= fmtDate($p['created_at'],'d M Y') ?></div>
                <?php if(!empty($p['diagnosis'])): ?><div style="font-size:12px;color:var(--primary)"><?= htmlspecialchars($p['diagnosis']) ?></div><?php endif; ?>
                <span class="badge badge-primary" style="margin-top:4px"><?= count($p['meds']??[]) ?> med(s)</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</div></div></div>
<script>
let mc=1;
function addMed(){
    const l=document.getElementById('medList');
    const d=document.createElement('div');
    d.className='med-row';
    d.style.cssText='background:var(--bg);padding:12px;border-radius:8px;border:1px solid var(--border);position:relative';
    d.innerHTML=`<button type="button" onclick="this.parentElement.remove()" style="position:absolute;top:8px;right:8px;background:none;border:none;cursor:pointer;color:var(--danger)"><i class="fas fa-times"></i></button>
    <div class="form-row" style="margin-bottom:8px">
        <input type="text" name="medicines[${mc}][name]" class="form-control" placeholder="Medicine name" required>
        <input type="text" name="medicines[${mc}][dosage]" class="form-control" placeholder="Dosage">
    </div>
    <div class="form-row-3">
        <input type="text" name="medicines[${mc}][frequency]" class="form-control" placeholder="Frequency">
        <input type="text" name="medicines[${mc}][duration]" class="form-control" placeholder="Duration">
        <input type="text" name="medicines[${mc}][instructions]" class="form-control" placeholder="Instructions">
    </div>`;
    l.appendChild(d); mc++;
}
</script>
<script src="/assets/js/app.js"></script>
</body></html>
