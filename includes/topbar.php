<?php
$u = me();
$notifs = 0;
$oid = toOid($u['id']);
if ($oid) $notifs = col('notifications')->countDocuments(['user_id'=>$oid,'is_read'=>false]);
?>
<header class="topbar">
    <div style="display:flex;align-items:center;gap:12px">
        <button class="icon-btn" onclick="toggleSidebar()" title="Menu"><i class="fas fa-bars"></i></button>
        <span class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></span>
    </div>
    <div class="topbar-right">
        <div class="icon-btn" title="Notifications">
            <i class="fas fa-bell"></i>
            <?php if ($notifs > 0): ?><span class="notif-dot"><?= $notifs ?></span><?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:9px">
            <div class="avatar" style="font-size:13px"><?= initials($u['name']) ?></div>
            <div>
                <div style="font-size:13px;font-weight:600;line-height:1.2"><?= htmlspecialchars($u['name']) ?></div>
                <div style="font-size:11px;color:var(--gray);text-transform:capitalize"><?= $u['role'] ?></div>
            </div>
        </div>
    </div>
</header>
