@extends('layouts.master')

@section('title', 'Change Password')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Kabore School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Only minimal custom CSS - everything else uses Bootstrap */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
            font-family: 'Nunito', sans-serif;
        }

        /* Hide sidebar */
        #wrapper { display: block !important; }
        #content-wrapper { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
        .container-fluid { padding: 0 !important; }
        .sidebar, #sidebar-wrapper, .navbar, .navbar-expand, .topbar { display: none !important; }
        #content { padding: 0 !important; margin: 0 !important; }

        /* Card animations */
        .password-card {
            animation: slideUp 0.8s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes slideUp {
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        @keyframes rotateGlow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .header-icon {
            animation: float 3s ease-in-out infinite;
        }

        .header-gradient {
            background: linear-gradient(135deg, #1a1a2e 0%, #7B0000 50%, #B8860B 100%);
        }

        .header-gradient::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0) 70%);
            animation: rotateGlow 20s linear infinite;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #7B0000 0%, #B8860B 100%);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(123, 0, 0, 0.35);
            color: white;
        }

        .btn-gradient:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .form-control-custom {
            border-radius: 12px;
            padding: 0.85rem 1rem;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control-custom:focus {
            border-color: #7B0000;
            box-shadow: 0 0 0 0.2rem rgba(123, 0, 0, 0.15);
            transform: translateY(-2px);
            background: white;
        }

        .form-control-custom.is-invalid {
            border-color: #dc3545;
        }

        .form-control-custom.is-valid {
            border-color: #28a745;
        }

        .strength-bar {
            height: 4px;
            border-radius: 4px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .strength-bar .progress-fill {
            height: 100%;
            width: 0%;
            border-radius: 4px;
            transition: width 0.5s ease, background 0.5s ease;
        }

        .requirement-item.met {
            color: #28a745;
        }

        .requirement-item.met i {
            color: #28a745;
        }

        .password-match-indicator {
            display: none;
        }

        .password-match-indicator.show {
            display: flex;
        }

        .password-match-indicator.match {
            color: #28a745;
        }

        .password-match-indicator.no-match {
            color: #dc3545;
        }

        .toggle-password-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 5px;
            z-index: 5;
            border-radius: 8px;
        }

        .toggle-password-btn:hover {
            color: #7B0000;
            background: rgba(123, 0, 0, 0.05);
        }

        .btn-loading .button-content { display: none; }
        .btn-loading .button-loader { display: inline-flex !important; }

        .button-loader { display: none; }

        .back-link {
            transition: all 0.3s ease;
        }

        .back-link:hover {
            transform: translateX(-4px);
            color: #a00000 !important;
        }

        .student-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #7B0000, #B8860B);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .student-info-card {
                flex-direction: column;
                align-items: stretch !important;
            }
            .student-details {
                text-align: center;
            }
            .student-avatar {
                margin: 0 auto;
            }
            .status-badge {
                text-align: center;
            }
        }

        @media (max-width: 768px) {
            .container-fluid { padding: 0 !important; }
            #content { padding: 0 !important; }
        }
    </style>
</head>
<body>

<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center p-3 p-md-4">
    <div class="password-card card border-0 shadow-lg w-100" style="max-width: 540px; border-radius: 24px;">

        <!-- Header -->
        <!-- <div class="card-header header-gradient border-0 position-relative text-white text-center p-4 p-md-3" style="border-radius: 50px 24px 0 0;">
            <div class="position-relative" style="z-index: 1;">
                <div class="header-icon bg-white bg-opacity-15 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px; border: 3px solid rgba(255,255,255,0.2); backdrop-filter: blur(10px); font-size: 2.5rem;">
                    <i class="fas fa-key text-dark"></i>
                </div>
                <h3 class="fw-800 mb-1" style="letter-spacing: -0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">Change Password</h3>
                <div class="bg-white bg-opacity-25 mx-auto" style="width: 60px; height: 3px; border-radius: 10px;"></div>
                <p class="text-white-50 mb-2 mt-2">Secure your account with a new password</p>
                <span class="badge bg-white bg-opacity-10 px-3 py-2 rounded-pill border border-white border-opacity-10">
                    <i class="fas fa-shield-alt me-1"></i>
                    Secure &bull; Encrypted
                </span>
            </div>
        </div> -->

        <!-- Body -->
        <div class="card-body p-4 p-md-5">

            <!-- Alerts -->
            @if(session('warning'))
                <div class="alert alert-warning border-0 border-start border-4 border-warning rounded-3 d-flex align-items-center alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span>{{ session('warning') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger border-0 border-start border-4 border-danger rounded-3 d-flex align-items-center alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span>{{ $errors->first() }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success border-0 border-start border-4 border-success rounded-3 d-flex align-items-center alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Student Info -->
            <div class="student-info-card bg-light rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap" style="border-left: 4px solid #7B0000;">
                <div class="d-flex align-items-center gap-3">
                    <div class="student-avatar rounded-circle text-white">
                        {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}
                    </div>
                    <div class="student-details">
                        <div class="fw-bold text-dark">
                            {{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-id-card text-danger me-1"></i>
                            {{ $student->student_id ?? 'N/A' }}
                        </div>
                    </div>
                </div>
                <div>
                    <span class="badge bg-warning bg-opacity-25 text-dark px-3 py-2">
                        <i class="fas fa-clock me-1"></i>
                        Password Change Required
                    </span>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('student.password.change') }}" method="POST" id="passwordChangeForm" novalidate>
                @csrf

                <!-- Current Password -->
                <div class="mb-3">
                    <label for="current_password" class="form-label fw-bold">
                        <i class="fas fa-lock text-danger me-1" style="width: 18px;"></i>
                        Current Password
                    </label>
                    <div class="position-relative">
                        <input type="password" class="form-control form-control-custom" id="current_password"
                               name="current_password" required
                               placeholder="Enter your current password"
                               autocomplete="current-password">
                        <button type="button" class="toggle-password-btn" onclick="togglePassword('current_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- New Password -->
                <div class="mb-3">
                    <label for="new_password" class="form-label fw-bold">
                        <i class="fas fa-key text-danger me-1" style="width: 18px;"></i>
                        New Password
                    </label>
                    <div class="position-relative">
                        <input type="password" class="form-control form-control-custom" id="new_password"
                               name="new_password" required
                               placeholder="Create a strong password (min 8 characters)"
                               autocomplete="new-password"
                               minlength="8">
                        <button type="button" class="toggle-password-btn" onclick="togglePassword('new_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <!-- Password Strength -->
                    <div class="mt-2">
                        <div class="strength-bar">
                            <div class="progress-fill" id="strengthProgress"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted">Password strength</small>
                            <small class="fw-bold" id="strengthLabel">Weak</small>
                        </div>
                    </div>

                    <!-- Requirements -->
                    <div class="bg-light rounded-3 p-2 mt-2 d-flex flex-wrap gap-2">
                        <span class="requirement-item small text-muted d-flex align-items-center gap-1" id="req-length">
                            <i class="fas fa-circle fa-xs"></i> 8+ characters
                        </span>
                        <span class="requirement-item small text-muted d-flex align-items-center gap-1" id="req-uppercase">
                            <i class="fas fa-circle fa-xs"></i> Uppercase
                        </span>
                        <span class="requirement-item small text-muted d-flex align-items-center gap-1" id="req-lowercase">
                            <i class="fas fa-circle fa-xs"></i> Lowercase
                        </span>
                        <span class="requirement-item small text-muted d-flex align-items-center gap-1" id="req-number">
                            <i class="fas fa-circle fa-xs"></i> Number
                        </span>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="new_password_confirmation" class="form-label fw-bold">
                        <i class="fas fa-check-circle text-danger me-1" style="width: 18px;"></i>
                        Confirm New Password
                    </label>
                    <div class="position-relative">
                        <input type="password" class="form-control form-control-custom" id="new_password_confirmation"
                               name="new_password_confirmation" required
                               placeholder="Re-enter your new password"
                               autocomplete="new-password">
                        <button type="button" class="toggle-password-btn" onclick="togglePassword('new_password_confirmation', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-match-indicator mt-1 align-items-center gap-1" id="matchIndicator">
                        <i class="fas"></i>
                        <span id="matchText"></span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-gradient w-100 text-white fw-bold py-3 rounded-3" id="submitBtn">
                    <span class="button-content">
                        <i class="fas fa-save me-2"></i>
                        Change Password &amp; Continue
                    </span>
                    <span class="button-loader">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        Processing...
                    </span>
                </button>
            </form>

            <!-- Back to Login -->
            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="back-link text-decoration-none fw-semibold" style="color: #7B0000;">
                    <i class="fas fa-arrow-left me-1"></i>
                    Back to Login
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="card-footer bg-transparent border-top text-center py-4">
            <small class="text-muted">
                <i class="fas fa-shield-alt text-danger me-1"></i>
                Secure &bull; Encrypted &bull; Protected
                <span class="text-secondary mx-1">|</span>
                <i class="fas fa-code-branch me-1"></i> v2.0
            </small>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ---- Toggle Password Visibility ----
        window.togglePassword = function(fieldId, button) {
            const field = document.getElementById(fieldId);
            if (!field) return;

            const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
            field.setAttribute('type', type);

            const icon = button.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        };

        // ---- DOM Elements ----
        const passwordInput = document.getElementById('new_password');
        const confirmInput = document.getElementById('new_password_confirmation');
        const currentPasswordInput = document.getElementById('current_password');
        const strengthProgress = document.getElementById('strengthProgress');
        const strengthLabel = document.getElementById('strengthLabel');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('passwordChangeForm');

        const requirements = {
            length: document.getElementById('req-length'),
            uppercase: document.getElementById('req-uppercase'),
            lowercase: document.getElementById('req-lowercase'),
            number: document.getElementById('req-number')
        };

        // ---- Password Strength & Requirements ----
        function checkRequirements(password) {
            const checks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password)
            };

            Object.keys(checks).forEach(key => {
                const el = requirements[key];
                if (el) {
                    el.classList.toggle('met', checks[key]);
                    const icon = el.querySelector('i');
                    if (checks[key]) {
                        icon.className = 'fas fa-check-circle fa-xs';
                        el.style.color = '#28a745';
                    } else {
                        icon.className = 'fas fa-circle fa-xs';
                        el.style.color = '';
                    }
                }
            });

            return checks;
        }

        function calculateStrength(password) {
            const checks = checkRequirements(password);
            let score = 0;

            if (checks.length) score += 25;
            if (checks.uppercase) score += 25;
            if (checks.lowercase) score += 25;
            if (checks.number) score += 25;

            return score;
        }

        function updateStrength(password) {
            const score = calculateStrength(password);

            strengthProgress.style.width = score + '%';

            let label = 'Weak';
            let color = '#dc3545';

            if (score < 25) {
                label = 'Weak';
                color = '#dc3545';
            } else if (score < 50) {
                label = 'Fair';
                color = '#ffc107';
            } else if (score < 75) {
                label = 'Good';
                color = '#17a2b8';
            } else {
                label = 'Strong';
                color = '#28a745';
            }

            strengthProgress.style.background = color;
            strengthLabel.textContent = label;
            strengthLabel.style.color = color;
        }

        // ---- Password Match Check ----
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            const indicator = document.getElementById('matchIndicator');
            const matchText = document.getElementById('matchText');
            const icon = indicator.querySelector('i');

            if (confirm.length === 0) {
                indicator.classList.remove('show', 'match', 'no-match');
                confirmInput.classList.remove('is-valid', 'is-invalid');
                return;
            }

            indicator.classList.add('show');

            if (password === confirm) {
                indicator.classList.remove('no-match');
                indicator.classList.add('match');
                matchText.textContent = 'Passwords match ✓';
                icon.className = 'fas fa-check-circle';
                confirmInput.classList.remove('is-invalid');
                confirmInput.classList.add('is-valid');
            } else {
                indicator.classList.remove('match');
                indicator.classList.add('no-match');
                matchText.textContent = 'Passwords do not match ✗';
                icon.className = 'fas fa-times-circle';
                confirmInput.classList.remove('is-valid');
                confirmInput.classList.add('is-invalid');
            }
        }

        // ---- Validate Field ----
        function validateField(field) {
            const value = field.value.trim();

            if (field.id === 'current_password') {
                if (value.length === 0) {
                    field.classList.remove('is-valid', 'is-invalid');
                    return;
                }
                field.classList.add(value.length >= 6 ? 'is-valid' : 'is-invalid');
                return;
            }

            if (field.id === 'new_password') {
                const score = calculateStrength(value);
                if (value.length === 0) {
                    field.classList.remove('is-valid', 'is-invalid');
                    return;
                }
                field.classList.add(score >= 50 ? 'is-valid' : 'is-invalid');
                return;
            }

            if (field.id === 'new_password_confirmation') {
                const password = passwordInput.value;
                if (value.length === 0) {
                    field.classList.remove('is-valid', 'is-invalid');
                    return;
                }
                field.classList.add(value === password ? 'is-valid' : 'is-invalid');
                return;
            }
        }

        // ---- Update Submit Button ----
        function updateSubmitButton() {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            const currentPassword = currentPasswordInput.value;

            const isPasswordValid = calculateStrength(password) >= 50;
            const isConfirmValid = password === confirm && confirm.length > 0;
            const isCurrentValid = currentPassword.length >= 6;

            submitBtn.disabled = !(isCurrentValid && isPasswordValid && isConfirmValid);
        }

        // ---- Event Listeners ----
        passwordInput.addEventListener('input', function() {
            updateStrength(this.value);
            validateField(this);
            checkPasswordMatch();
            updateSubmitButton();
        });

        confirmInput.addEventListener('input', function() {
            checkPasswordMatch();
            validateField(this);
            updateSubmitButton();
        });

        currentPasswordInput.addEventListener('input', function() {
            validateField(this);
            updateSubmitButton();
        });

        // ---- Form Submission ----
        form.addEventListener('submit', function(e) {
            if (submitBtn.disabled) {
                e.preventDefault();
                return;
            }

            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
        });

        // ---- Auto-focus ----
        currentPasswordInput.focus();

        // ---- Keyboard Shortcut: Ctrl+Enter ----
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                if (!submitBtn.disabled) {
                    form.submit();
                }
            }
        });

        // ---- Dismiss Alerts ----
        document.querySelectorAll('.alert .btn-close').forEach(btn => {
            btn.addEventListener('click', function() {
                const alert = this.closest('.alert');
                if (alert) {
                    alert.style.transition = 'opacity 0.3s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            });
        });

        console.log('Password change page initialized');
    });
</script>
</body>
</html>
@endsection