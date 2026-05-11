<?php
require_once dirname(__DIR__) . '/config/config.php';
if (isLoggedIn()) { header('Location: /'); exit; }

$errors = []; $old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;
    $name     = trim($_POST['name']             ?? '');
    $email    = trim($_POST['email']            ?? '');
    $phone    = trim($_POST['phone']            ?? '');
    $gender   =      $_POST['gender']           ?? '';
    $dob      =      $_POST['dob']              ?? '';
    $password =      $_POST['password']         ?? '';
    $confirm  =      $_POST['confirm_password'] ?? '';

    if (empty($name))                                    $errors['name']     = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required.';
    if (strlen($password) < 8)                           $errors['password'] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)                          $errors['confirm']  = 'Passwords do not match.';

    if (empty($errors)) {
        if (col('users')->findOne(['email' => $email])) {
            $errors['email'] = 'This email is already registered.';
        } else {
            col('users')->insertOne([
                'name'       => $name,
                'email'      => $email,
                'password'   => password_hash($password, PASSWORD_DEFAULT),
                'role'       => 'patient',
                'phone'      => $phone,
                'gender'     => $gender,
                'dob'        => $dob ?: null,
                'address'    => '',
                'is_active'  => true,
                'created_at' => now(),
            ]);
            header('Location: /auth/login.php?registered=1'); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — MediCare</title>
    <link rel="stylesheet" href="../assets/css/app.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="auth-wrap">
    <!-- Left Panel -->
    <div class="auth-left">
        <div class="auth-left-logo"><i class="fas fa-heartbeat"></i> MediCare</div>
        <h1>Your health,<br>our priority</h1>
        <p>Join thousands of patients managing their healthcare digitally — appointments, prescriptions, and bills in one place.</p>
        <div class="auth-features">
            <div class="auth-feature"><i class="fas fa-calendar-check"></i> Book appointments online instantly</div>
            <div class="auth-feature"><i class="fas fa-file-prescription"></i> Access digital prescriptions anytime</div>
            <div class="auth-feature"><i class="fas fa-clock"></i> Track live waiting room queue</div>
            <div class="auth-feature"><i class="fas fa-file-invoice-dollar"></i> View and download your bills</div>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="auth-right">
        <div class="auth-box">
            <div class="auth-box-logo"><i class="fas fa-heartbeat"></i> MediCare</div>
            <h2>Create your account</h2>
            <p class="sub">Start managing your health today — it's free</p>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Please fix the errors below.</div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="form-group">
                    <label class="form-label">Full Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                        placeholder="Your full name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
                    <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= $errors['name'] ?></div><?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address <span style="color:var(--danger)">*</span></label>
                        <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                            placeholder="you@example.com" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                        <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="9876543210"
                            value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">Select gender</option>
                            <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= ($old['gender']??'') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($old['dob'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password <span style="color:var(--danger)">*</span></label>
                        <div class="pass-wrap">
                            <input type="password" name="password" id="pw1" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                placeholder="Min 8 characters" required>
                            <button type="button" class="toggle-pass" onclick="togglePw('pw1','ei1')"><i class="fas fa-eye" id="ei1"></i></button>
                        </div>
                        <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= $errors['password'] ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password <span style="color:var(--danger)">*</span></label>
                        <div class="pass-wrap">
                            <input type="password" name="confirm_password" id="pw2" class="form-control <?= isset($errors['confirm']) ? 'is-invalid' : '' ?>"
                                placeholder="Repeat password" required>
                            <button type="button" class="toggle-pass" onclick="togglePw('pw2','ei2')"><i class="fas fa-eye" id="ei2"></i></button>
                        </div>
                        <?php if (isset($errors['confirm'])): ?><div class="invalid-feedback"><?= $errors['confirm'] ?></div><?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:4px">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-footer">Already have an account? <a href="/auth/login.php">Sign in</a></div>
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
