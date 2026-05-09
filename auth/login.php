<?php
require_once '../config/config.php';
if (isLoggedIn()) { header('Location: /' . me()['role'] . '/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $user = col('users')->findOne(['email' => $email, 'is_active' => true]);
        if ($user && password_verify($password, $user['password'])) {
            // Data API returns _id as {'$oid': '...'} — extract string
            if (is_array($user['_id'])) $user['_id'] = $user['_id']['$oid'] ?? '';
            loginUser($user);
            header('Location: /' . $user['role'] . '/dashboard.php'); exit;
        }
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — MediCare</title>
    <link rel="stylesheet" href="../assets/css/app.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-left">
        <div class="auth-left-logo"><i class="fas fa-heartbeat"></i> MediCare</div>
        <h1>Welcome back to MediCare</h1>
        <p>Sign in to access your dashboard, appointments, prescriptions, and more.</p>
        <div class="auth-features">
            <div class="auth-feature"><i class="fas fa-shield-alt"></i> Secure role-based access</div>
            <div class="auth-feature"><i class="fas fa-calendar-alt"></i> Manage appointments easily</div>
            <div class="auth-feature"><i class="fas fa-chart-line"></i> Real-time clinic analytics</div>
            <div class="auth-feature"><i class="fas fa-mobile-alt"></i> Works on any device</div>
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-box">
            <div class="auth-box-logo"><i class="fas fa-heartbeat"></i> MediCare</div>
            <h2>Sign in to your account</h2>
            <p class="sub">Enter your credentials to continue</p>

            <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> Account created! You can now sign in.</div>
            <?php endif; ?>
            <?php if (isset($_GET['err']) && $_GET['err'] === 'unauthorized'): ?>
            <div class="alert alert-warning"><i class="fas fa-lock"></i> You don't have permission to access that page.</div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control"
                        placeholder="you@example.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="pass-wrap">
                        <input type="password" name="password" id="pw" class="form-control" placeholder="••••••••" required>
                        <button type="button" class="toggle-pass" onclick="togglePw('pw','ei')"><i class="fas fa-eye" id="ei"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="auth-footer">Don't have an account? <a href="/auth/register.php">Create one free</a></div>
            <div class="auth-footer" style="margin-top:8px"><a href="/"><i class="fas fa-arrow-left"></i> Back to Home</a></div>
        </div>
    </div>
</div>
<script>
function togglePw(id, iconId) {
    const i = document.getElementById(id), ic = document.getElementById(iconId);
    i.type = i.type === 'password' ? 'text' : 'password';
    ic.className = i.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
</body>
</html>
