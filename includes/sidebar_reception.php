<?php $u = me(); $cur = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand"><i class="fas fa-heartbeat"></i> MediCare</div>
    <nav class="sidebar-nav">
        <div class="nav-section">Reception</div>
        <a href="/reception/dashboard.php"    class="nav-link <?= $cur==='dashboard.php'    ?'active':'' ?>"><i class="fas fa-home"></i> Dashboard</a>
        <a href="/reception/appointments.php" class="nav-link <?= $cur==='appointments.php' ?'active':'' ?>"><i class="fas fa-calendar-alt"></i> Appointments</a>
        <a href="/reception/walkin.php"       class="nav-link <?= $cur==='walkin.php'       ?'active':'' ?>"><i class="fas fa-walking"></i> Walk-in</a>
        <a href="/reception/checkin.php"      class="nav-link <?= $cur==='checkin.php'      ?'active':'' ?>"><i class="fas fa-check-circle"></i> Check-in</a>
        <a href="/reception/patients.php"     class="nav-link <?= $cur==='patients.php'     ?'active':'' ?>"><i class="fas fa-users"></i> Patients</a>
        <a href="/reception/billing.php"      class="nav-link <?= $cur==='billing.php'      ?'active':'' ?>"><i class="fas fa-file-invoice-dollar"></i> Billing</a>
        <a href="/reception/waiting_room.php" class="nav-link <?= $cur==='waiting_room.php' ?'active':'' ?>"><i class="fas fa-door-open"></i> Waiting Room</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar"><?= initials($u['name']) ?></div>
            <div><div class="user-name"><?= htmlspecialchars($u['name']) ?></div><div class="user-role"><?= $u['role'] ?></div></div>
        </div>
        <a href="/auth/logout.php" class="btn btn-danger btn-sm btn-block"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>
