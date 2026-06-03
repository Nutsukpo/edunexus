@extends('layouts.header')
@section('title', 'Login')

<body class="bg-gradient-light">
    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5 animate-card">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row g-0">
                            <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-login-image animate-left">
                                <img class="img-fluid w-75 pt-5 animate-float" style="max-width: 250px;" src="{{ asset('img/images.jpeg') }}" alt="Logo">
                            </div>
                            <div class="col-lg-6">
                                <div class="p-4 p-md-5 animate-right">
                                    <div class="text-center mb-4 animate-fade-down">
                                        <h1 class="h3 text-gray-900 mb-2 animate-gradient-text">Kabore Sch Mgt Sys!</h1>
                                        <p class="text-muted small animate-fade-up">Welcome Back! Please sign in to your account</p>
                                    </div>

                                    <form class="user" action="/login" method="POST">
                                        @csrf

                                        <div class="form-group mb-3 animate-input">
                                            <label for="exampleInputEmail" class="form-label small text-muted mb-1">Email Address</label>
                                            <input type="email" class="form-control form-control-user @error('email') is-invalid @enderror"
                                                id="exampleInputEmail" aria-describedby="emailHelp"
                                                placeholder="name@example.com" name="email" value="{{ old('email') }}" required autofocus>
                                            <span class="focus-border"></span>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3 animate-input-delay">
                                            <label for="exampleInputPassword" class="form-label small text-muted mb-1">Password</label>
                                            <div class="password-wrapper">
                                                <input type="password" class="form-control form-control-user @error('password') is-invalid @enderror"
                                                    id="exampleInputPassword" placeholder="Enter your password" name="password" required>
                                                <button type="button" class="password-toggle" id="togglePassword">
                                                    <i class="far fa-eye"></i>
                                                </button>
                                            </div>
                                            <span class="focus-border"></span>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-4 animate-checkbox">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="custom-control custom-checkbox small">
                                                    <input type="checkbox" class="custom-control-input" id="customCheck" name="remember">
                                                    <label class="custom-control-label text-muted" for="customCheck">Remember Me</label>
                                                </div>
                                                <div>
                                                    <a class="small text-primary animate-link" href="{{ url('forgot-password') }}" style="text-decoration: none;">Forgot Password?</a>
                                                </div>
                                            </div>
                                        </div>

                                        <button class="btn btn-block w-100 text-white py-2 mb-3 animate-button" type="submit" style="background-color: darkred; border: none;">
                                            <span class="button-content">
                                                <i class="fas fa-sign-in-alt me-2"></i>
                                                <span>Login</span>
                                            </span>
                                            <span class="button-loader" style="display: none;">
                                                <i class="fas fa-spinner fa-spin me-2"></i>
                                                Logging in...
                                            </span>
                                        </button>

                                        @if(session('errors'))
                                            <div class="alert alert-danger alert-dismissible fade show mb-3 animate-alert" role="alert">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ session('errors')->first('errors') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif

                                        @if(session('success'))
                                            <div class="alert alert-success alert-dismissible fade show mb-3 animate-alert" role="alert">
                                                <i class="fas fa-check-circle me-2"></i>
                                                {{ session('success') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif
                                    </form>

                                    <hr class="my-4 animate-hr">

                                    <!-- <div class="text-center small">
                                        <span class="text-muted">Don't have an account?</span>
                                        <a href="{{ url('register') }}" class="text-primary fw-bold" style="text-decoration: none;">Sign Up</a>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Custom Styles with Enhanced Animations */
        .bg-gradient-light {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Particles */
        .bg-gradient-light::before,
        .bg-gradient-light::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(139,0,0,0.05) 0%, rgba(139,0,0,0) 70%);
            animation: float-particle 20s infinite ease-in-out;
            pointer-events: none;
        }

        .bg-gradient-light::before {
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .bg-gradient-light::after {
            bottom: -100px;
            right: -100px;
            animation-delay: 10s;
            width: 400px;
            height: 400px;
        }

        @keyframes float-particle {
            0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.3; }
            50% { transform: translate(50px, 50px) scale(1.2); opacity: 0.5; }
        }

        /* Card Entrance Animation */
        .animate-card {
            animation: slideUpFade 0.8s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes slideUpFade {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Left Panel Animation */
        .animate-left {
            animation: slideInLeft 0.8s ease-out 0.2s forwards;
            opacity: 0;
            transform: translateX(-30px);
        }

        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Right Panel Animation */
        .animate-right {
            animation: slideInRight 0.8s ease-out 0.3s forwards;
            opacity: 0;
            transform: translateX(30px);
        }

        @keyframes slideInRight {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Floating Animation for Logo */
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Gradient Text Animation */
        .animate-gradient-text {
            background: linear-gradient(135deg, #2c3e50 0%, darkred 50%, #2c3e50 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Fade Animations */
        .animate-fade-down {
            animation: fadeDown 0.6s ease-out 0.4s forwards;
            opacity: 0;
            transform: translateY(-20px);
        }

        .animate-fade-up {
            animation: fadeUp 0.6s ease-out 0.5s forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeDown {
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }

        /* Input Animations */
        .animate-input, .animate-input-delay, .animate-checkbox {
            animation: fadeInScale 0.5s ease-out forwards;
            opacity: 0;
            transform: scale(0.95);
        }

        .animate-input { animation-delay: 0.6s; }
        .animate-input-delay { animation-delay: 0.7s; }
        .animate-checkbox { animation-delay: 0.8s; }

        @keyframes fadeInScale {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Button Animation */
        .animate-button {
            animation: pulseButton 0.8s ease-out 0.9s forwards;
            opacity: 0;
            transform: scale(0.9);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        @keyframes pulseButton {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .animate-button:hover::before {
            width: 300px;
            height: 300px;
        }

        /* Alert Animation */
        .animate-alert {
            animation: shakeAlert 0.5s ease-out;
        }

        @keyframes shakeAlert {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* HR Animation */
        .animate-hr {
            animation: expandWidth 0.6s ease-out 1s forwards;
            width: 0;
            margin-left: auto;
            margin-right: auto;
        }

        @keyframes expandWidth {
            to { width: 100%; }
        }

        /* Link Animation */
        .animate-link {
            position: relative;
            transition: color 0.3s ease;
        }

        .animate-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: darkred;
            transition: width 0.3s ease;
        }

        .animate-link:hover::after {
            width: 100%;
        }

        /* Enhanced Input Styles */
        .form-group {
            position: relative;
        }

        .form-control-user {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control-user:focus {
            border-color: darkred;
            box-shadow: 0 0 0 0.2rem rgba(139, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .focus-border {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: darkred;
            transition: width 0.4s ease;
        }

        .form-control-user:focus ~ .focus-border {
            width: 100%;
        }

        /* Password Toggle Button */
        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: darkred;
        }

        /* Checkbox Animation */
        .custom-control-input {
            cursor: pointer;
        }

        .custom-control-label::before {
            transition: all 0.2s ease;
        }

        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: darkred;
            border-color: darkred;
            animation: checkPop 0.3s ease;
        }

        @keyframes checkPop {
            0% { transform: scale(0.8); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        /* Background Styles */
        .bg-login-image {
            background: linear-gradient(135deg, darkred 0%, #8b0000 100%);
            border-radius: 0.75rem 0 0 0.75rem;
            position: relative;
            overflow: hidden;
        }

        .bg-login-image::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            animation: rotateGlow 20s linear infinite;
        }

        @keyframes rotateGlow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .card {
            border-radius: 0.75rem;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.2) !important;
        }

        /* Alert Styles */
        .alert {
            border-radius: 0.5rem;
            font-size: 0.875rem;
            border: none;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .min-vh-100 {
                min-height: auto !important;
            }
            .p-md-5 {
                padding: 1.5rem !important;
            }
            .animate-card {
                margin: 1rem;
            }
        }

        /* Loading State */
        .btn.loading .button-content {
            display: none;
        }

        .btn.loading .button-loader {
            display: flex !important;
            align-items: center;
            justify-content: center;
        }

        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }
    </style>

    <script>
        // Password visibility toggle
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
                    const passwordInput = document.getElementById('exampleInputPassword');
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }

            // Form submission loading state
            const form = document.querySelector('form.user');
            const submitBtn = document.querySelector('.animate-button');
            
            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                });
            }

            // Add animation to alerts on dismiss
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        alert.style.animation = 'fadeOut 0.3s ease forwards';
                    });
                }
            });
        });

        // Additional fade out animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                to {
                    opacity: 0;
                    transform: translateY(-10px);
                    display: none;
                }
            }
        `;
        document.head.appendChild(style);

        // Parallax effect on mouse move
        document.addEventListener('mousemove', function(e) {
            const card = document.querySelector('.card');
            if (card && window.innerWidth > 768) {
                const mouseX = e.clientX / window.innerWidth;
                const mouseY = e.clientY / window.innerHeight;
                const rotateX = (mouseY - 0.5) * 2;
                const rotateY = (mouseX - 0.5) * 2;
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
            }
        });

        // Reset transform on mouse leave
        document.addEventListener('mouseleave', function() {
            const card = document.querySelector('.card');
            if (card) {
                card.style.transform = '';
            }
        });
    </script>
</body>

</html>