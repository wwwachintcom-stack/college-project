<?php
require_once __DIR__ . '/config/config.php';
if (isLoggedIn()) {
    header('Location: /' . me()['role'] . '/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare — Smart Clinic Management</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-inner">
            <a href="/" class="nav-brand"><i class="fas fa-heartbeat"></i> MediCare</a>
            <div class="nav-links" id="navLinks">
                <a href="#how" class="nav-link-item">How it works</a>
                <a href="#features" class="nav-link-item">Features</a>
                <a href="/auth/login.php" class="btn btn-outline-gray btn-sm">Sign In</a>
                <a href="/auth/register.php" class="btn btn-primary btn-sm">Get Started Free</a>
            </div>
            <button class="nav-toggle" onclick="toggleNav()"><i class="fas fa-bars"></i></button>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-blob blob1"></div>
        <div class="hero-blob blob2"></div>
        <div class="hero-inner">
            <div>
                <div class="hero-badge"><i class="fas fa-shield-alt"></i> Trusted Clinic Platform</div>
                <h1 class="hero-title">Manage your clinic<br><span>smarter & faster</span></h1>
                <p class="hero-sub">Book appointments, manage prescriptions, track billing, and monitor live waiting
                    queues — all in one secure platform.</p>
                <div class="hero-btns">
                    <a href="/auth/register.php" class="btn btn-primary btn-lg"><i class="fas fa-user-plus"></i> Create
                        Free Account</a>
                    <a href="/auth/login.php" class="btn btn-outline btn-lg"><i class="fas fa-sign-in-alt"></i> Sign
                        In</a>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:16px">
                <div
                    style="background:rgba(255,255,255,0.7);border-radius:16px;padding:28px;border:1px solid rgba(255,255,255,0.9);backdrop-filter:blur(10px)">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
                        <div
                            style="width:44px;height:44px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px">
                            <i class="fas fa-calendar-check"></i></div>
                        <div>
                            <div style="font-weight:700;font-size:15px">Easy Appointment Booking</div>
                            <div style="font-size:13px;color:var(--gray)">Choose doctor, date & time</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
                        <div
                            style="width:44px;height:44px;background:linear-gradient(135deg,#16a34a,#0891b2);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px">
                            <i class="fas fa-file-prescription"></i></div>
                        <div>
                            <div style="font-weight:700;font-size:15px">Digital Prescriptions</div>
                            <div style="font-size:13px;color:var(--gray)">Access anytime, anywhere</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px">
                        <div
                            style="width:44px;height:44px;background:linear-gradient(135deg,#d97706,#dc2626);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px">
                            <i class="fas fa-door-open"></i></div>
                        <div>
                            <div style="font-weight:700;font-size:15px">Live Waiting Room</div>
                            <div style="font-size:13px;color:var(--gray)">Real-time queue tracking</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section id="how" style="background:var(--white)">
        <div class="container">
            <div class="section-head">
                <h2>How it works</h2>
                <p>Get started in minutes — no setup required</p>
            </div>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-num">1</div>
                    <h3>Create Account</h3>
                    <p>Register as a patient in seconds. Doctors and staff are added by admin.</p>
                </div>
                <div class="step-card">
                    <div class="step-num">2</div>
                    <h3>Book Appointment</h3>
                    <p>Choose your doctor, pick a date and time slot, and confirm your booking instantly.</p>
                </div>
                <div class="step-card">
                    <div class="step-num">3</div>
                    <h3>Visit & Get Care</h3>
                    <p>Check in at reception, track your queue live, receive digital prescriptions and bills.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section id="features" style="background:var(--bg)">
        <div class="container">
            <div class="section-head">
                <h2>Everything your clinic needs</h2>
                <p>Powerful features built for modern healthcare</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
                    <h4>Smart Scheduling</h4>
                    <p>Intelligent appointment booking with real-time slot availability and conflict detection.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-file-prescription"></i></div>
                    <h4>Digital Prescriptions</h4>
                    <p>Doctors write and share prescriptions digitally. Patients can view and print anytime.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-door-open"></i></div>
                    <h4>Live Waiting Room</h4>
                    <p>Real-time queue display so patients always know exactly when it's their turn.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    <h4>Billing & Invoicing</h4>
                    <p>Automated invoice generation, payment tracking, and billing history in one place.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h4>Analytics Dashboard</h4>
                    <p>Real-time insights on appointments, revenue, and patient trends for admins.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-lock"></i></div>
                    <h4>Role-Based Access</h4>
                    <p>Secure, tailored dashboards for patients, doctors, reception staff, and admins.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section style="background:var(--white)">
        <div class="container">
            <div class="cta-box">
                <h2>Ready to get started?</h2>
                <p>Join MediCare today — it's completely free for patients.</p>
                <a href="/auth/register.php" class="btn btn-white btn-lg"><i class="fas fa-user-plus"></i> Create Your
                    Account</a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-brand"><i class="fas fa-heartbeat"></i> MediCare</div>
            <p style="font-size:13px">© <?= date('Y') ?> MediCare. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function toggleNav() {
            const n = document.getElementById('navLinks');
            const open = n.style.display === 'flex';
            n.style.cssText = open ? '' : 'display:flex;flex-direction:column;position:absolute;top:66px;right:24px;background:#fff;padding:16px;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.12);border:1px solid #e2e8f0;z-index:300;gap:8px';
        }
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const t = document.querySelector(a.getAttribute('href'));
                if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
            });
        });
    </script>
</body>

</html>