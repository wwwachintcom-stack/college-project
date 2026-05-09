<?php $u = me(); $cur = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand"><i class="fas fa-heartbeat"></i> MediCare</div>
    <nav class="sidebar-nav">
        <div class="nav-section">Patient</div>
        <a href="/patient/dashboard.php"     class="nav-link <?= $cur==='dashboard.php'     ?'active':'' ?>"><i class="fas fa-home"></i> Dashboard</a>
        <a href="/patient/book.php"          class="nav-link <?= $cur==='book.php'          ?'active':'' ?>"><i class="fas fa-plus-circle"></i> Book Appointment</a>
        <a href="/patient/appointments.php"  class="nav-link <?= $cur==='appointments.php'  ?'active':'' ?>"><i class="fas fa-calendar-alt"></i> My Appointments</a>
        <a href="/patient/prescriptions.php" class="nav-link <?= $cur==='prescriptions.php' ?'active':'' ?>"><i class="fas fa-file-prescription"></i> Prescriptions</a>
        <a href="/patient/bills.php"         class="nav-link <?= $cur==='bills.php'         ?'active':'' ?>"><i class="fas fa-file-invoice-dollar"></i> Bills</a>
        <a href="/patient/waiting_room.php"  class="nav-link <?= $cur==='waiting_room.php'  ?'active':'' ?>"><i class="fas fa-door-open"></i> Waiting Room</a>
        <div class="nav-section">Account</div>
        <a href="/patient/profile.php"       class="nav-link <?= $cur==='profile.php'       ?'active':'' ?>"><i class="fas fa-user"></i> My Profile</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar"><?= initials($u['name']) ?></div>
            <div><div class="user-name"><?= htmlspecialchars($u['name']) ?></div><div class="user-role"><?= $u['role'] ?></div></div>
        </div>
        <a href="/auth/logout.php" class="btn btn-danger btn-sm btn-block"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>
