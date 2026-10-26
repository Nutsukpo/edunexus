@extends('layouts.master')

@section('content')

<style>
    .password-page {
        min-height: 75vh;
        padding: 45px 15px;
        background: #f4f7fb;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .password-wrapper {
        width: 100%;
        max-width: 1000px;
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 45px rgba(15, 23, 42, 0.08);
        border: 1px solid #e8edf5;
    }

    .password-sidebar {
        height: 100%;
        min-height: 540px;
        padding: 45px 32px;
        color: #fff;
        background: linear-gradient(145deg, #123c80, #1768b5);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .password-icon {
        width: 68px;
        height: 68px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.2);
        font-size: 28px;
        margin-bottom: 28px;
    }

    .password-sidebar h3 {
        font-weight: 750;
        line-height: 1.35;
        margin-bottom: 16px;
    }

    .password-sidebar p {
        color: rgba(255, 255, 255, 0.82);
        line-height: 1.8;
        font-size: 14px;
    }

    .security-tip {
        padding: 16px;
        background: rgba(255, 255, 255, 0.10);
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 12px;
        font-size: 13px;
        line-height: 1.7;
    }

    .password-form-panel {
        padding: 45px 42px;
    }

    .password-form-panel h4 {
        color: #172b4d;
        font-size: 25px;
        font-weight: 750;
        margin-bottom: 8px;
    }

    .password-subtitle {
        color: #748198;
        font-size: 14px;
        line-height: 1.7;
        margin-bottom: 30px;
    }

    .password-page .form-label {
        color: #344563;
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 8px;
    }

    .password-input-group {
        position: relative;
    }

    .password-input-group .form-control {
        height: 49px;
        border: 1px solid #dce3ed;
        border-radius: 10px;
        padding: 12px 48px 12px 15px;
        font-size: 14px;
        color: #172b4d;
        background-color: #fbfcfe;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .password-input-group .form-control:focus {
        border-color: #2674d9;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(38, 116, 217, 0.10);
    }

    .password-input-group .form-control.is-invalid {
        border-color: #dc3545;
    }

    .toggle-password {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        padding: 5px;
        color: #718096;
        background: transparent;
        cursor: pointer;
        z-index: 3;
    }

    .toggle-password:hover {
        color: #1768b5;
    }

    .password-help {
        color: #8390a5;
        font-size: 12px;
        margin-top: 7px;
    }

    .password-submit {
        min-height: 49px;
        border-radius: 10px;
        background: #1768b5;
        border: 1px solid #1768b5;
        font-size: 14px;
        font-weight: 650;
        transition: all 0.2s ease;
    }

    .password-submit:hover {
        background: #105493;
        border-color: #105493;
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(23, 104, 181, 0.18);
    }

    .password-security-note {
        background: #f3f8ff;
        border: 1px solid #e0ecfc;
        border-radius: 10px;
        padding: 13px 15px;
        color: #45617f;
        font-size: 12px;
        line-height: 1.7;
    }

    .password-security-note i {
        color: #1768b5;
    }

    @media (max-width: 767px) {
        .password-page {
            padding: 20px 12px;
        }

        .password-sidebar {
            min-height: auto;
            padding: 28px 24px;
            gap: 20px;
        }

        .password-sidebar h3 {
            font-size: 23px;
        }

        .password-form-panel {
            padding: 30px 23px;
        }

        .password-form-panel h4 {
            font-size: 22px;
        }
    }
</style>

<div class="password-page">
    <div class="password-wrapper">
        <div class="row g-0">

            {{-- LEFT INFORMATION PANEL --}}
            <div class="col-md-5">
                <div class="password-sidebar">

                    <div>
                        <div class="password-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>

                        <h3>Protect Your Account</h3>

                        <p>
                            Keep your EDUNEXUS account secure by updating
                            your password regularly. Choose a strong,
                            unique password that is difficult to guess.
                        </p>
                    </div>

                    <div class="security-tip">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-lock me-2"></i>
                            <strong>Security tip</strong>
                        </div>

                        Use at least 8 characters and avoid reusing
                        passwords from other websites or accounts.
                    </div>

                </div>
            </div>

            {{-- RIGHT PASSWORD FORM --}}
            <div class="col-md-7">
                <div class="password-form-panel">

                    <div class="mb-4">
                        <span class="text-primary small fw-semibold">
                            ACCOUNT SETTINGS
                        </span>

                        <h4 class="mt-2">Change Password</h4>

                        <p class="password-subtitle">
                            Enter your current password, then create
                            and confirm your new password below.
                        </p>
                    </div>

                    {{-- SUCCESS MESSAGE --}}
                    @if (session('success'))
                        <div class="alert alert-success border-0 rounded-3"
                             role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- VALIDATION ERRORS --}}
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3"
                             role="alert">
                            <div class="fw-semibold mb-2">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Please correct the following:
                            </div>

                            <ul class="mb-0 ps-4 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('password.change.update') }}">
                        @csrf
                        @method('PUT')

                        {{-- CURRENT PASSWORD --}}
                        <div class="mb-4">
                            <label for="current_password"
                                   class="form-label">
                                Current Password
                            </label>

                            <div class="password-input-group">
                                <input
                                    type="password"
                                    name="current_password"
                                    id="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    placeholder="Enter your current password"
                                    autocomplete="current-password"
                                    required>

                                <button type="button"
                                        class="toggle-password"
                                        data-target="current_password"
                                        aria-label="Show current password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                            @error('current_password')
                                <div class="text-danger small mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- NEW PASSWORD --}}
                        <div class="mb-4">
                            <label for="password" class="form-label">
                                New Password
                            </label>

                            <div class="password-input-group">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Create a new password"
                                    minlength="8"
                                    autocomplete="new-password"
                                    required>

                                <button type="button"
                                        class="toggle-password"
                                        data-target="password"
                                        aria-label="Show new password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                            <div class="password-help">
                                <i class="fas fa-info-circle me-1"></i>
                                Use at least 8 characters.
                            </div>

                            @error('password')
                                <div class="text-danger small mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- CONFIRM PASSWORD --}}
                        <div class="mb-4">
                            <label for="password_confirmation"
                                   class="form-label">
                                Confirm New Password
                            </label>

                            <div class="password-input-group">
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    id="password_confirmation"
                                    class="form-control"
                                    placeholder="Re-enter your new password"
                                    minlength="8"
                                    autocomplete="new-password"
                                    required>

                                <button type="button"
                                        class="toggle-password"
                                        data-target="password_confirmation"
                                        aria-label="Show password confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        {{-- SECURITY NOTICE --}}
                        <div class="password-security-note mb-4">
                            <i class="fas fa-shield-alt me-2"></i>
                            Your new password will be securely hashed
                            before it is saved.
                        </div>

                        {{-- SUBMIT --}}
                        <button type="submit"
                                class="btn btn-primary password-submit w-100">
                            <i class="fas fa-save me-2"></i>
                            Update Password
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.toggle-password').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const icon = this.querySelector('i');

            if (!input || !icon) {
                return;
            }

            const showPassword = input.type === 'password';

            input.type = showPassword ? 'text' : 'password';

            icon.classList.toggle('fa-eye', !showPassword);
            icon.classList.toggle('fa-eye-slash', showPassword);

            this.setAttribute(
                'aria-label',
                showPassword ? 'Hide password' : 'Show password'
            );
        });
    });
</script>

@endsection
