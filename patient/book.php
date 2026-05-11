<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('patient');
$u   = me();
$uid = toOid($u['id']);

$error = ''; $success = '';

$doctors = toArr(col('users')->aggregate([
    ['$match'  => ['role'=>'doctor','is_active'=>true]],
    ['$lookup' => ['from'=>'doctors','localField'=>'_id','foreignField'=>'user_id','as'=>'info']],
    ['$unwind' => ['path'=>'$info','preserveNullAndEmptyArrays'=>true]],
    ['$sort'   => ['name'=>1]],
]));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $did    = trim($_POST['doctor_id']        ?? '');
    $date   = trim($_POST['appointment_date'] ?? '');
    $time   = trim($_POST['appointment_time'] ?? '');
    $reason = trim($_POST['reason']           ?? '');

    if (!$did || !$date || !$time)          $error = 'Please fill all required fields.';
    elseif (strtotime($date) < strtotime('today')) $error = 'Date cannot be in the past.';
    else {
        $docOid   = toOid($did);
        $apptDate = mDate($date);

        $exists = col('appointments')->findOne(['doctor_id'=>$docOid,'appointment_date'=>$apptDate,'appointment_time'=>$time,'status'=>['$nin'=>['cancelled']]]);
        if ($exists) {
            $error = 'This time slot is already booked. Please choose another.';
        } else {
            $tokenRes = toArr(col('appointments')->aggregate([
                ['$match' => ['doctor_id'=>$docOid,'appointment_date'=>$apptDate]],
                ['$group' => ['_id'=>null,'max'=>['$max'=>'$token_number']]],
            ]));
            $token = ($tokenRes[0]['max'] ?? 0) + 1;

            col('appointments')->insertOne([
                'patient_id'       => $uid,
                'doctor_id'        => $docOid,
                'appointment_date' => $apptDate,
                'appointment_time' => $time,
                'token_number'     => $token,
                'status'           => 'pending',
                'type'             => 'online',
                'reason'           => $reason,
                'notes'            => '',
                'created_at'       => now(),
            ]);
            col('notifications')->insertOne(['user_id'=>$uid,'title'=>'Appointment Booked','message'=>"Appointment booked for $date at $time.",'type'=>'appointment','is_read'=>false,'created_at'=>now()]);
            $success = "Appointment booked! Token #" . str_pad($token,3,'0',STR_PAD_LEFT) . " — $date at " . date('h:i A',strtotime($time));
        }
    }
}

$pageTitle = 'Book Appointment';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Book Appointment — MediCare</title>
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar_patient.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">

    <div class="page-header">
        <div><h1>Book Appointment</h1><p>Schedule a visit with your preferred doctor</p></div>
    </div>

    <?php if ($error):   ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:22px">
        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Select Doctor <span style="color:var(--danger)">*</span></label>
                    <select name="doctor_id" class="form-control" required onchange="showDoctorInfo(this)">
                        <option value="">— Choose a doctor —</option>
                        <?php foreach ($doctors as $d):
                            $did  = oid($d['_id']);
                            $spec = $d['info']['specialization'] ?? 'General';
                            $fee  = $d['info']['consultation_fee'] ?? 0;
                        ?>
                        <option value="<?= $did ?>"
                            data-spec="<?= htmlspecialchars($spec) ?>"
                            data-fee="<?= $fee ?>"
                            data-qual="<?= htmlspecialchars($d['info']['qualification']??'') ?>"
                            data-exp="<?= $d['info']['experience_years']??0 ?>"
                            <?= ($_POST['doctor_id']??'')===$did?'selected':'' ?>>
                            Dr. <?= htmlspecialchars($d['name']) ?> — <?= htmlspecialchars($spec) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date <span style="color:var(--danger)">*</span></label>
                        <input type="date" name="appointment_date" class="form-control"
                            min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['appointment_date']??'') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time Slot <span style="color:var(--danger)">*</span></label>
                        <select name="appointment_time" class="form-control" required>
                            <option value="">— Select time —</option>
                            <?php foreach (['09:00','09:30','10:00','10:30','11:00','11:30','12:00','14:00','14:30','15:00','15:30','16:00','16:30','17:00'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($_POST['appointment_time']??'')===$s?'selected':'' ?>>
                                <?= date('h:i A',strtotime($s)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Reason for Visit</label>
                    <textarea name="reason" class="form-control" placeholder="Describe your symptoms or reason for visit..."><?= htmlspecialchars($_POST['reason']??'') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-calendar-check"></i> Confirm Booking
                </button>
            </form>
        </div>

        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="card" id="docCard" style="display:none">
                <h4 style="font-size:14px;font-weight:700;margin-bottom:13px"><i class="fas fa-user-md"></i> Doctor Info</h4>
                <div id="docInfo"></div>
            </div>
            <div class="card">
                <h4 style="font-size:14px;font-weight:700;margin-bottom:13px">Available Doctors</h4>
                <?php foreach ($doctors as $d): ?>
                <div style="padding:11px;background:var(--bg);border-radius:var(--radius-sm);border:1px solid var(--border);margin-bottom:8px">
                    <div style="font-weight:600;font-size:13px">Dr. <?= htmlspecialchars($d['name']) ?></div>
                    <div style="font-size:12px;color:var(--gray)"><?= htmlspecialchars($d['info']['specialization']??'General') ?></div>
                    <div style="font-size:13px;color:var(--primary);font-weight:600;margin-top:3px">₹<?= number_format($d['info']['consultation_fee']??0,0) ?> / visit</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div></div></div>
<script src="/assets/js/app.js"></script>
<script>
function showDoctorInfo(sel) {
    const o = sel.options[sel.selectedIndex];
    const card = document.getElementById('docCard');
    if (!o.value) { card.style.display='none'; return; }
    card.style.display='block';
    document.getElementById('docInfo').innerHTML = `
        <div style="display:flex;flex-direction:column;gap:8px;font-size:13px">
            <div><span style="color:var(--gray)">Specialization:</span> <strong>${o.dataset.spec}</strong></div>
            <div><span style="color:var(--gray)">Qualification:</span> <strong>${o.dataset.qual||'—'}</strong></div>
            <div><span style="color:var(--gray)">Experience:</span> <strong>${o.dataset.exp} years</strong></div>
            <div style="font-size:16px;font-weight:800;color:var(--primary);margin-top:4px">₹${Number(o.dataset.fee).toLocaleString()} / visit</div>
        </div>`;
}
</script>
</body></html>
