<?php $u = me(); $cur = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand"><i class="fas fa-heartbeat"></i> MediCare</div>
    <nav class="sidebar-nav">
        <div class="nav-section">Admin</div>
        <a href="/admin/dashboard.php"    class="nav-link <?= $cur==='dashboard.php'    ?'active':'' ?>"><i class="fas fa-chart-pie"></i> Dashboard</a>
        <a href="/admin/users.php"        class="nav-link <?= $cur==='users.php'        ?'active':'' ?>"><i class="fas fa-users-cog"></i> Users</a>
        <a href="/admin/doctors.php"      class="nav-link <?= $cur==='doctors.php'      ?'active':'' ?>"><i class="fas fa-user-md"></i> Doctors</a>
        <a href="/admin/appointments.php" class="nav-link <?= $cur==='appointments.php' ?'active':'' ?>"><i class="fas fa-calendar-alt"></i> Appointments</a>
        <a href="/admin/billing.php"      class="nav-link <?= $cur==='billing.php'      ?'active':'' ?>"><i class="fas fa-file-invoice-dollar"></i> Billing</a>
        <a href="/admin/reports.php"      class="nav-link <?= $cur==='reports.php'      ?'active':'' ?>"><i class="fas fa-chart-bar"></i> Reports</a>
        <div class="nav-section">System</div>
        <a href="/admin/settings.php"     class="nav-link <?= $cur==='settings.php'     ?'active':'' ?>"><i class="fas fa-cog"></i> Settings</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar"><?= initials($u['name']) ?></div>
            <div><div class="user-name"><?= htmlspecialchars($u['name']) ?></div><div class="user-role"><?= $u['role'] ?></div></div>
        </div>
        <a href="/auth/logout.php" class="btn btn-danger btn-sm btn-block"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>
