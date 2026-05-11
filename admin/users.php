<?php
require_once dirname(__DIR__) . '/config/config.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!empty($_POST['toggle_id'])) {
        $oid = toOid($_POST['toggle_id']);
        $u   = col('users')->findOne(['_id'=>$oid]);
        if ($u) col('users')->updateOne(['_id'=>$oid],['$set'=>['is_active'=>!($u['is_active']??true)]]);
        header('Location: users.php'); exit;
    }
    if (!empty($_POST['delete_id'])) {
        col('users')->deleteOne(['_id'=>toOid($_POST['delete_id'])]);
        header('Location: users.php?msg=deleted'); exit;
    }
}

$role   = $_GET['role']??'';
$search = trim($_GET['search']??'');
$filter = [];
if ($role)   $filter['role'] = $role;
if ($search) $filter['$or']  = [['name'=>['$regex'=>$search,'$options'=>'i']],['email'=>['$regex'=>$search,'$options'=>'i']]];
$users = col('users')->find($filter,['sort'=>['created_at'=>-1]]);
$pageTitle='User Management';
?>
<!DOCTYPE html><html lang="en"><head>
<?php include '../includes/head.php'; ?>
<title>Users — MediCare</title>
</head><body>
<div class="layout">
<?php include '../includes/sidebar_admin.php'; ?>
<div class="main">
<?php include '../includes/topbar.php'; ?>
<div class="page-body">
<div class="page-header"><div><h1>User Management</h1><p>Manage all system users</p></div></div>
<?php if(isset($_GET['msg'])&&$_GET['msg']==='deleted'):?><div class="alert alert-success">User deleted.</div><?php endif; ?>
<form method="GET" style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap">
    <div class="search-wrap" style="flex:1;min-width:200px"><i class="fas fa-search"></i><input type="text" name="search" class="form-control" placeholder="Search name or email..." value="<?= htmlspecialchars($search) ?>"></div>
    <select name="role" class="form-control" style="width:160px">
        <option value="">All Roles</option>
        <?php foreach(['patient','doctor','reception','admin'] as $r): ?><option value="<?= $r ?>" <?= $role===$r?'selected':'' ?>><?= ucfirst($r) ?></option><?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
    <a href="users.php" class="btn btn-outline-gray">Reset</a>
</form>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if(empty($users)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--gray)">No users found</td></tr>
            <?php else: foreach($users as $u):
                $uid = oid($u['_id']);
                $rc  = ['patient'=>'badge-primary','doctor'=>'badge-success','reception'=>'badge-info','admin'=>'badge-warning'];
            ?>
            <tr>
                <td><div style="display:flex;align-items:center;gap:9px"><div class="avatar" style="font-size:12px"><?= initials($u['name']) ?></div><strong><?= htmlspecialchars($u['name']) ?></strong></div></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge <?= $rc[$u['role']]??'badge-gray' ?>"><?= ucfirst($u['role']) ?></span></td>
                <td><?= htmlspecialchars($u['phone']??'—') ?></td>
                <td><span class="badge <?= ($u['is_active']??true)?'badge-success':'badge-danger' ?>"><?= ($u['is_active']??true)?'Active':'Inactive' ?></span></td>
                <td><?= fmtDate($u['created_at'],'d M Y') ?></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <form method="POST" style="display:inline"><input type="hidden" name="toggle_id" value="<?= $uid ?>"><button class="btn <?= ($u['is_active']??true)?'btn-warning':'btn-success' ?> btn-sm" title="<?= ($u['is_active']??true)?'Deactivate':'Activate' ?>"><i class="fas fa-<?= ($u['is_active']??true)?'ban':'check' ?>"></i></button></form>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete permanently?')"><input type="hidden" name="delete_id" value="<?= $uid ?>"><button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div></div></div>
<script src="/assets/js/app.js"></script>
</body></html>
