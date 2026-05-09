<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare API Docs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Inter,sans-serif;background:#0f172a;color:#e2e8f0;line-height:1.6}
        .sidebar{width:260px;background:#1e293b;position:fixed;top:0;left:0;height:100vh;overflow-y:auto;border-right:1px solid #334155;padding:24px 0}
        .sidebar-brand{padding:0 20px 20px;font-size:18px;font-weight:800;color:#fff;border-bottom:1px solid #334155;margin-bottom:16px;display:flex;align-items:center;gap:8px}
        .sidebar-brand i{color:#60a5fa}
        .nav-group{padding:8px 20px 4px;font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:1px;margin-top:8px}
        .nav-item{display:block;padding:8px 20px;font-size:13px;color:#94a3b8;cursor:pointer;transition:all .15s;border-left:3px solid transparent;text-decoration:none}
        .nav-item:hover,.nav-item.active{color:#fff;background:rgba(96,165,250,.1);border-left-color:#60a5fa}
        .main{margin-left:260px;padding:40px;max-width:900px}
        h1{font-size:28px;font-weight:800;margin-bottom:8px}
        .sub{color:#94a3b8;font-size:15px;margin-bottom:32px}
        .endpoint{background:#1e293b;border-radius:12px;margin-bottom:16px;border:1px solid #334155;overflow:hidden}
        .ep-header{display:flex;align-items:center;gap:12px;padding:14px 18px;cursor:pointer;user-select:none}
        .ep-header:hover{background:rgba(255,255,255,.03)}
        .method{padding:4px 10px;border-radius:6px;font-size:12px;font-weight:700;min-width:64px;text-align:center}
        .GET{background:rgba(16,185,129,.15);color:#34d399}
        .POST{background:rgba(96,165,250,.15);color:#60a5fa}
        .PUT{background:rgba(245,158,11,.15);color:#fbbf24}
        .PATCH{background:rgba(168,85,247,.15);color:#c084fc}
        .DELETE{background:rgba(239,68,68,.15);color:#f87171}
        .ep-path{font-family:monospace;font-size:14px;color:#e2e8f0}
        .ep-desc{font-size:13px;color:#64748b;margin-left:auto}
        .ep-body{padding:18px;border-top:1px solid #334155;display:none}
        .ep-body.open{display:block}
        .section-title{font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;margin-top:16px}
        .section-title:first-child{margin-top:0}
        pre{background:#020617;border-radius:8px;padding:14px;font-size:12px;color:#86efac;overflow-x:auto;border:1px solid #1e3a5f;line-height:1.8}
        .param-table{width:100%;border-collapse:collapse;font-size:13px}
        .param-table th{text-align:left;padding:8px 12px;background:#0f172a;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.5px}
        .param-table td{padding:8px 12px;border-top:1px solid #1e293b;color:#cbd5e1}
        .param-table td:first-child{font-family:monospace;color:#60a5fa}
        .required{color:#f87171;font-size:11px;font-weight:600}
        .optional{color:#64748b;font-size:11px}
        .try-btn{background:#2563eb;color:#fff;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;margin-top:12px;font-family:inherit}
        .try-btn:hover{background:#1d4ed8}
        .response-box{margin-top:12px;display:none}
        .response-box.show{display:block}
        .status-badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:700;margin-bottom:8px}
        .s200{background:rgba(16,185,129,.2);color:#34d399}
        .s400{background:rgba(239,68,68,.2);color:#f87171}
        .base-url{background:#020617;border-radius:8px;padding:12px 16px;font-family:monospace;font-size:14px;color:#60a5fa;margin-bottom:24px;border:1px solid #1e3a5f}
        .tag{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;background:rgba(99,102,241,.15);color:#a5b4fc;margin-left:8px}
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand"><i class="fas fa-heartbeat"></i> MediCare API</div>
    <div class="nav-group">Overview</div>
    <a class="nav-item active" onclick="scrollTo('overview')">Introduction</a>
    <a class="nav-item" onclick="scrollTo('auth')">Authentication</a>
    <div class="nav-group">Resources</div>
    <a class="nav-item" onclick="scrollTo('users')">Users</a>
    <a class="nav-item" onclick="scrollTo('appointments')">Appointments</a>
    <a class="nav-item" onclick="scrollTo('doctors')">Doctors</a>
    <a class="nav-item" onclick="scrollTo('prescriptions')">Prescriptions</a>
    <a class="nav-item" onclick="scrollTo('bills')">Bills</a>
    <a class="nav-item" onclick="scrollTo('waiting_room')">Waiting Room</a>
    <a class="nav-item" onclick="scrollTo('notifications')">Notifications</a>
</div>

<div class="main">
    <div id="overview">
        <h1>MediCare REST API</h1>
        <p class="sub">Complete REST API for clinic management — GET, POST, PUT, PATCH, DELETE</p>
        <div class="base-url">Base URL: <strong>http://localhost:8000/api</strong></div>
    </div>

    <div id="auth" style="margin-bottom:32px">
        <h2 style="font-size:18px;font-weight:700;margin-bottom:12px">Authentication</h2>
        <p style="font-size:14px;color:#94a3b8;margin-bottom:12px">Pass your API key in the request header:</p>
        <pre>X-API-Key: your_api_key_here</pre>
        <p style="font-size:13px;color:#64748b;margin-top:8px">Or use session (browser login). Public endpoints don't require auth.</p>
    </div>

    <?php
    $resources = [
        'users' => [
            'desc' => 'Manage patients, doctors, reception staff and admins',
            'fields' => [
                ['name','string','required','Full name'],
                ['email','string','required','Unique email address'],
                ['password','string','required (create)','Min 6 characters'],
                ['role','string','optional','patient | doctor | reception | admin'],
                ['phone','string','optional','Phone number'],
                ['gender','string','optional','male | female | other'],
            ],
            'example_post' => '{"name":"John Doe","email":"john@example.com","password":"secret123","role":"patient","phone":"9876543210"}',
            'example_patch' => '{"phone":"9999999999","gender":"male"}',
        ],
        'appointments' => [
            'desc' => 'Book and manage patient appointments',
            'fields' => [
                ['patient_id','string','required','Patient user ID'],
                ['doctor_id','string','required','Doctor user ID'],
                ['appointment_date','string','required','Date (YYYY-MM-DD)'],
                ['appointment_time','string','required','Time (HH:MM)'],
                ['reason','string','optional','Reason for visit'],
                ['status','string','optional','pending | confirmed | in_progress | completed | cancelled'],
                ['type','string','optional','online | walk_in'],
            ],
            'example_post' => '{"patient_id":"abc123","doctor_id":"def456","appointment_date":"2026-05-10","appointment_time":"10:00","reason":"Fever"}',
            'example_patch' => '{"status":"confirmed"}',
        ],
        'doctors' => [
            'desc' => 'Doctor profiles with specialization and availability',
            'fields' => [
                ['user_id','string','required','Linked user ID'],
                ['specialization','string','optional','e.g. General Medicine'],
                ['qualification','string','optional','e.g. MBBS, MD'],
                ['experience_years','number','optional','Years of experience'],
                ['consultation_fee','number','optional','Fee in ₹'],
            ],
            'example_post' => '{"user_id":"abc123","specialization":"Cardiology","consultation_fee":800}',
            'example_patch' => '{"consultation_fee":1000}',
        ],
        'prescriptions' => [
            'desc' => 'Digital prescriptions written by doctors',
            'fields' => [
                ['patient_id','string','required','Patient user ID'],
                ['doctor_id','string','required','Doctor user ID'],
                ['diagnosis','string','optional','Diagnosis text'],
                ['notes','string','optional','Clinical notes'],
                ['follow_up_date','string','optional','Follow-up date'],
            ],
            'example_post' => '{"patient_id":"abc123","doctor_id":"def456","diagnosis":"Viral fever","notes":"Rest for 3 days"}',
            'example_patch' => '{"follow_up_date":"2026-05-20"}',
        ],
        'bills' => [
            'desc' => 'Patient billing and invoice management',
            'fields' => [
                ['patient_id','string','required','Patient user ID'],
                ['consultation_fee','number','optional','Consultation fee'],
                ['medicine_fee','number','optional','Medicine charges'],
                ['total_amount','number','optional','Total bill amount'],
                ['paid_amount','number','optional','Amount paid'],
                ['payment_status','string','optional','pending | partial | paid'],
                ['payment_method','string','optional','cash | card | upi | insurance'],
            ],
            'example_post' => '{"patient_id":"abc123","consultation_fee":500,"medicine_fee":150,"total_amount":650,"paid_amount":650,"payment_status":"paid","payment_method":"upi"}',
            'example_patch' => '{"payment_status":"paid","paid_amount":650}',
        ],
        'waiting_room' => [
            'desc' => 'Live waiting room queue management',
            'fields' => [
                ['appointment_id','string','required','Appointment ID'],
                ['patient_id','string','required','Patient user ID'],
                ['doctor_id','string','required','Doctor user ID'],
                ['token_number','number','required','Queue token number'],
                ['status','string','optional','waiting | in_progress | done | left'],
            ],
            'example_post' => '{"appointment_id":"abc123","patient_id":"def456","doctor_id":"ghi789","token_number":5}',
            'example_patch' => '{"status":"in_progress"}',
        ],
        'notifications' => [
            'desc' => 'User notifications and alerts',
            'fields' => [
                ['user_id','string','required','Target user ID'],
                ['title','string','required','Notification title'],
                ['message','string','required','Notification message'],
                ['type','string','optional','appointment | prescription | billing | system'],
                ['is_read','boolean','optional','Read status'],
            ],
            'example_post' => '{"user_id":"abc123","title":"Appointment Confirmed","message":"Your appointment is confirmed.","type":"appointment"}',
            'example_patch' => '{"is_read":true}',
        ],
    ];

    foreach ($resources as $res => $info):
    ?>
    <div id="<?= $res ?>" style="margin-bottom:40px">
        <h2 style="font-size:18px;font-weight:700;margin-bottom:4px"><?= ucfirst(str_replace('_',' ',$res)) ?></h2>
        <p style="font-size:13px;color:#64748b;margin-bottom:16px"><?= $info['desc'] ?></p>

        <?php
        $endpoints = [
            ['GET',    "/api/$res",      "List all $res",          null,                    null],
            ['GET',    "/api/$res/{id}", "Get single $res by ID",  null,                    null],
            ['POST',   "/api/$res",      "Create new $res",        $info['example_post'],   $info['fields']],
            ['PUT',    "/api/$res/{id}", "Full replace $res",      $info['example_post'],   $info['fields']],
            ['PATCH',  "/api/$res/{id}", "Partial update $res",    $info['example_patch'],  null],
            ['DELETE', "/api/$res/{id}", "Delete $res",            null,                    null],
        ];
        foreach ($endpoints as $i => [$m, $path, $desc, $body, $fields]):
        ?>
        <div class="endpoint">
            <div class="ep-header" onclick="toggle('<?= $res.$i ?>')">
                <span class="method <?= $m ?>"><?= $m ?></span>
                <span class="ep-path"><?= $path ?></span>
                <span class="ep-desc"><?= $desc ?></span>
            </div>
            <div class="ep-body" id="<?= $res.$i ?>">
                <?php if ($fields): ?>
                <div class="section-title">Request Body Fields</div>
                <table class="param-table">
                    <tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr>
                    <?php foreach ($fields as [$n,$t,$r,$d]): ?>
                    <tr>
                        <td><?= $n ?></td>
                        <td style="color:#94a3b8"><?= $t ?></td>
                        <td><?php if(str_contains($r,'required')): ?><span class="required">required</span><?php else: ?><span class="optional">optional</span><?php endif; ?></td>
                        <td style="color:#94a3b8"><?= $d ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>

                <?php if ($body): ?>
                <div class="section-title" style="margin-top:16px">Example Request</div>
                <pre><?= htmlspecialchars(json_encode(json_decode($body), JSON_PRETTY_PRINT)) ?></pre>
                <?php endif; ?>

                <div class="section-title" style="margin-top:16px">Example Response</div>
                <pre>{
  "success": true,
  "message": "<?= ucfirst($res) ?> <?= strtolower($m === 'POST' ? 'created' : ($m === 'DELETE' ? 'deleted' : 'updated')) ?> successfully.",
  "data": { ... }
}</pre>

                <button class="try-btn" onclick="tryApi('<?= $m ?>','<?= $path ?>','<?= $res.$i ?>',<?= $body ? "'" . addslashes($body) . "'" : 'null' ?>)">
                    <i class="fas fa-play"></i> Try it
                </button>
                <div class="response-box" id="resp_<?= $res.$i ?>">
                    <div class="section-title" style="margin-top:12px">Response</div>
                    <pre id="respBody_<?= $res.$i ?>" style="color:#e2e8f0"></pre>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>

<script>
function toggle(id) {
    const el = document.getElementById(id);
    el.classList.toggle('open');
}
function scrollTo(id) {
    document.getElementById(id)?.scrollIntoView({behavior:'smooth'});
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    event.target.classList.add('active');
}
async function tryApi(method, path, id, body) {
    const realPath = path.replace('{id}', prompt('Enter ID (for routes with {id}):') || '');
    const url = 'http://localhost:8000' + realPath;
    const opts = { method, headers: {'Content-Type':'application/json','X-API-Key': prompt('Enter API Key (or leave blank for session):') || ''} };
    if (['POST','PUT','PATCH'].includes(method) && body) opts.body = body;

    const respEl = document.getElementById('resp_' + id);
    const bodyEl = document.getElementById('respBody_' + id);
    respEl.classList.add('show');
    bodyEl.textContent = 'Loading...';

    try {
        const res = await fetch(url, opts);
        const data = await res.json();
        bodyEl.textContent = JSON.stringify(data, null, 2);
        bodyEl.style.color = res.ok ? '#86efac' : '#f87171';
    } catch(e) {
        bodyEl.textContent = 'Error: ' + e.message;
        bodyEl.style.color = '#f87171';
    }
}
</script>
</body>
</html>
