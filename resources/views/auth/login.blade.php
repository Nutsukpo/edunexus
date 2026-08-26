<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | TALHA PREMIER INTERNATIONAL ACADEMY</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('img/Talha.jpeg') }}">
    <style>
        /* ----- Reset & Global ----- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f7ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Particles - Blue tones */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(26, 75, 140, 0.10) 0%, rgba(26, 75, 140, 0) 70%);
            animation: float-particle 20s infinite ease-in-out;
            pointer-events: none;
            z-index: 0;
        }
        body::before {
            top: -100px;
            left: -100px;
            animation-delay: 0s;
            width: 300px;
            height: 300px;
        }
        body::after {
            bottom: -100px;
            right: -100px;
            animation-delay: 10s;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(43, 107, 176, 0.08) 0%, rgba(43, 107, 176, 0) 70%);
        }

        @keyframes float-particle {
            0%, 100% { transform: translate(0,0) scale(1); opacity: 0.3; }
            50% { transform: translate(50px,50px) scale(1.2); opacity: 0.5; }
        }

        /* Glowing orb effect - Blue */
        .glow-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }
        .glow-orb-1 {
            top: -200px;
            right: -200px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(26, 75, 140, 0.25) 0%, rgba(26, 75, 140, 0) 70%);
            animation: pulse 8s ease-in-out infinite;
        }
        .glow-orb-2 {
            bottom: -200px;
            left: -200px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(43, 107, 176, 0.15) 0%, rgba(43, 107, 176, 0) 70%);
            animation: pulse 10s ease-in-out infinite reverse;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 0.8; }
        }

        /* ----- Main Card ----- */
        .login-card {
            max-width: 480px;
            width: 100%;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            box-shadow: 0 30px 60px -15px rgba(0, 40, 100, 0.4), 0 0 40px rgba(26, 75, 140, 0.08);
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: slideUp 0.8s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
            opacity: 0;
            transform: translateY(30px);
            border: 1px solid rgba(26, 75, 140, 0.08);
        }

        @keyframes slideUp {
            to { opacity: 1; transform: translateY(0); }
        }

        /* ----- Header with Blue Gradient ----- */
        .card-header-custom {
            background: linear-gradient(135deg, #0a2f66 0%, #1a4b8c 40%, #2b6bb0 70%, #1a4b8c 100%);
            padding: 2.5rem 2rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 3px solid #3f87d0;
        }

        .card-header-custom::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.06) 0%, rgba(255, 255, 255, 0) 70%);
            animation: rotateGlow 25s linear infinite;
        }

        @keyframes rotateGlow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Light blue accent line */
        .card-header-custom::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #3f87d0, #6ba3e0, #3f87d0, transparent);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        .header-icon {
            width: 90px;
            height: 90px;
            background: rgba(255, 255, 255, 0.10);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            border: 3px solid rgba(63, 135, 208, 0.3);
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
            font-size: 2.8rem;
            color: #6ba3e0;
            animation: float 3s ease-in-out infinite;
            box-shadow: 0 0 30px rgba(43, 107, 176, 0.15);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        .card-header-custom h3 {
            color: white;
            font-weight: 800;
            font-size: 1.75rem;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            letter-spacing: -0.5px;
            margin-bottom: 0.25rem;
        }

        .card-header-custom h3 span {
            color: #6ba3e0;
        }

        .card-header-custom p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
            margin-bottom: 0;
            font-weight: 300;
        }

        .header-divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #6ba3e0, transparent);
            margin: 0.75rem auto;
            border-radius: 10px;
            position: relative;
            z-index: 1;
        }

        .school-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.08);
            padding: 0.3rem 1rem;
            border-radius: 30px;
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.7rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(63, 135, 208, 0.15);
            position: relative;
            z-index: 1;
            margin-top: 0.5rem;
        }

        .school-badge i {
            color: #6ba3e0;
        }

        /* ----- Body ----- */
        .card-body-custom {
            padding: 2rem 2rem 1.5rem;
        }

        /* ----- Alerts - Blue theme ----- */
        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-custom .alert-icon {
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .alert-custom .alert-text {
            flex: 1;
        }

        .alert-custom .btn-close {
            font-size: 0.7rem;
        }

        .alert-danger-custom {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border-left: 4px solid #dc3545;
        }

        .alert-success-custom {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }

        .alert-info-custom {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }

        /* ----- Form Elements - Blue theme ----- */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            font-weight: 700;
            font-size: 0.9rem;
            color: #1a3a6a;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label .label-icon {
            color: #2b6bb0;
            width: 18px;
            text-align: center;
        }

        .input-group-custom {
            position: relative;
        }

        .input-group-custom .form-control {
            border-radius: 12px;
            padding: 0.85rem 1rem;
            border: 2px solid #d4e2f5;
            transition: all 0.3s ease;
            background: #f8fafc;
            font-size: 0.95rem;
            height: auto;
            padding-right: 3.5rem;
        }

        .input-group-custom .form-control:focus {
            border-color: #2b6bb0;
            box-shadow: 0 0 0 0.2rem rgba(43, 107, 176, 0.15);
            transform: translateY(-2px);
            background: white;
        }

        .input-group-custom .form-control:hover {
            border-color: #2b6bb0;
        }

        .input-group-custom .form-control.is-invalid {
            border-color: #dc3545;
            background-image: none;
        }

        .input-group-custom .form-control.is-valid {
            border-color: #28a745;
            background-image: none;
        }

        .input-group-custom .form-control::placeholder {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .input-group-custom .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: color 0.3s ease;
            z-index: 5;
        }

        .input-group-custom .form-control:focus ~ .input-icon {
            color: #2b6bb0;
        }

        .input-group-custom .form-control {
            padding-left: 45px;
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
            font-size: 0.9rem;
        }

        .toggle-password-btn:hover {
            color: #2b6bb0;
            background: rgba(43, 107, 176, 0.08);
        }

        .toggle-password-btn:focus {
            outline: none;
        }

        /* ----- Submit Button - Blue Gradient ----- */
        .btn-login {
            background: linear-gradient(135deg, #0a2f66 0%, #1a4b8c 40%, #2b6bb0 70%, #1a4b8c 100%);
            border: none;
            color: white;
            font-weight: 700;
            padding: 0.9rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 1rem;
            width: 100%;
            position: relative;
            overflow: hidden;
            margin-top: 0.25rem;
            letter-spacing: 0.5px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.10), transparent);
            transition: left 0.6s ease;
        }

        .btn-login:hover:not(:disabled)::before {
            left: 100%;
        }

        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(26, 75, 140, 0.4);
            color: white;
        }

        .btn-login:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn-login .button-loader {
            display: none;
        }

        .btn-login.loading .button-content {
            display: none;
        }

        .btn-login.loading .button-loader {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        /* Light blue accent on button */
        .btn-login::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 20%;
            right: 20%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #6ba3e0, transparent);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .btn-login:hover:not(:disabled)::after {
            opacity: 1;
            left: 10%;
            right: 10%;
        }

        /* ----- Footer ----- */
        .card-footer-custom {
            background: transparent;
            border-top: 1px solid rgba(26, 75, 140, 0.08);
            padding: 1rem 2rem;
            text-align: center;
        }

        .card-footer-custom small {
            color: #94a3b8;
            font-size: 0.7rem;
        }

        .card-footer-custom small i {
            color: #2b6bb0;
        }

        .card-footer-custom .footer-divider {
            color: #d1d5db;
            margin: 0 0.5rem;
        }

        /* ----- Student Info Help - Blue theme ----- */
        .help-text {
            font-size: 0.8rem;
            color: #4a6f9c;
            padding: 0.5rem 0.75rem;
            background: rgba(43, 107, 176, 0.05);
            border-radius: 8px;
            border-left: 3px solid #2b6bb0;
            margin-top: 0.5rem;
        }

        .help-text i {
            color: #2b6bb0;
            margin-right: 0.5rem;
        }

        /* Remember me checkbox styling - Blue */
        .form-check-input:checked {
            background-color: #1a4b8c;
            border-color: #1a4b8c;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(26, 75, 140, 0.25);
        }

        /* Forgot password link - Blue */
        .forgot-link {
            color: #1a4b8c;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: #0a2f66;
            text-decoration: underline !important;
        }

        /* ----- Responsive ----- */
        @media (max-width: 576px) {
            body { padding: 0.75rem; }
            .card-body-custom { padding: 1.5rem; }
            .card-header-custom { padding: 1.5rem; }
            .card-header-custom h3 { font-size: 1.4rem; }
            .header-icon { width: 65px; height: 65px; font-size: 2rem; }
            .glow-orb-1 { width: 300px; height: 300px; top: -100px; right: -100px; }
            .glow-orb-2 { width: 250px; height: 250px; bottom: -100px; left: -100px; }
        }
    </style>
</head>
<body>

<!-- Glowing Orbs - Blue -->
<div class="glow-orb glow-orb-1"></div>
<div class="glow-orb glow-orb-2"></div>

<div class="login-card">
    <!-- Header -->
    <div class="card-header-custom">
        <div class="header-icon">
            <i class="fas fa-user-graduate"></i>
        </div>
        <h3>Student <span>Portal</span></h3>
        <div class="header-divider"></div>
        <p>Sign in to access your academic dashboard</p>
        <span class="school-badge">
            <i class="fas fa-school me-1"></i>
            Talha Prem. School Management System
        </span>
    </div>

    <!-- Body -->
    <div class="card-body-custom">
        <!-- Alerts -->
        @if(session('errors'))
            <div class="alert-custom alert-danger-custom alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                <span class="alert-text">{{ session('errors')->first('errors') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert-custom alert-success-custom alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                <span class="alert-text">{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert-custom alert-info-custom alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="fas fa-info-circle"></i></span>
                <span class="alert-text">{{ session('info') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Login Form -->
        <form action="{{ route('login.submit') }}" method="POST" id="studentLoginForm">
            @csrf
            <input type="hidden" name="role" value="student">

            <!-- Student ID -->
            <div class="form-group">
                <label for="login_field" class="form-label">
                    <span class="label-icon"><i class="fas fa-id-card"></i></span>
                    Student ID 
                </label>
                <div class="input-group-custom">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" class="form-control" id="login_field"
                           name="login_field" required
                           placeholder="Enter your Student ID or Email"
                           value="{{ old('login_field') }}"
                           autofocus>
                    <button type="button" class="toggle-password-btn" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">
                    <span class="label-icon"><i class="fas fa-key"></i></span>
                    Password
                </label>
                <div class="input-group-custom">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" class="form-control" id="password"
                           name="password" required
                           placeholder="Enter your password">
                    <button type="button" class="toggle-password-btn" onclick="togglePassword(this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label small text-muted" for="remember">
                        <i class="fas fa-check-circle me-1" style="color: #2b6bb0; font-size: 0.7rem;"></i>
                        Remember me
                    </label>
                </div>
                <a href="/student/change-password" class="forgot-link text-decoration-none small fw-semibold">
                    <i class="fas fa-question-circle me-1"></i>
                    Forgot Password?
                </a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-login" id="submitBtn">
                <span class="button-content">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Sign In to Dashboard
                </span>
                <span class="button-loader">
                    <i class="fas fa-spinner fa-spin me-2"></i>
                    Signing in...
                </span>
            </button>
        </form>

        <!-- Additional Info -->
        <div class="text-center mt-3">
            <p class="small text-muted">
                <i class="fas fa-shield-alt me-1" style="color: #2b6bb0;"></i>
                Secure &bull; Encrypted &bull; Protected
            </p>
        </div>
    </div>

    <!-- Footer -->
    <div class="card-footer-custom">
        <small>
            <i class="fas fa-code-branch me-1"></i> v2.0
            <span class="footer-divider">|</span>
            <i class="fas fa-graduation-cap me-1"></i>
            Kabore School Management System
        </small>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ---- Toggle Password Visibility ----
        window.togglePassword = function(button) {
            const wrapper = button.closest('.input-group-custom');
            const input = wrapper.querySelector('input[type="password"], input[type="text"]');
            if (!input) return;

            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);

            const icon = button.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        };

        // ---- Form Loading State ----
        const form = document.getElementById('studentLoginForm');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', function(e) {
            // Basic validation
            const studentId = document.getElementById('login_field').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!studentId || !password) {
                e.preventDefault();
                
                // Show validation message
                let alertDiv = document.querySelector('.alert-danger-custom');
                if (!alertDiv) {
                    alertDiv = document.createElement('div');
                    alertDiv.className = 'alert-custom alert-danger-custom alert-dismissible fade show';
                    alertDiv.role = 'alert';
                    alertDiv.innerHTML = `
                        <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                        <span class="alert-text">Please fill in all required fields.</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    form.insertBefore(alertDiv, form.firstChild);
                } else {
                    alertDiv.querySelector('.alert-text').textContent = 'Please fill in all required fields.';
                    alertDiv.style.display = 'flex';
                }
                return;
            }

            // Show loading state
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        // ---- Auto-focus on page load ----
        document.getElementById('login_field').focus();

        // ---- Keyboard shortcut: Enter to submit ----
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const activeElement = document.activeElement;
                if (activeElement && (activeElement.id === 'login_field' || activeElement.id === 'password')) {
                    if (!submitBtn.disabled) {
                        form.submit();
                    }
                }
            }
        });

        // ---- Dismiss Alerts with Animation ----
        document.querySelectorAll('.alert-custom .btn-close').forEach(btn => {
            btn.addEventListener('click', function() {
                const alert = this.closest('.alert-custom');
                if (alert) {
                    alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                }
            });
        });

        // ---- Auto-dismiss success alerts after 5 seconds ----
        setTimeout(() => {
            document.querySelectorAll('.alert-success-custom').forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        console.log('Student login page initialized');
    });
</script>
</body>
</html>