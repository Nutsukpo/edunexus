@extends('layouts.master')

@section('content')

@php
    $displayName = $user->name ?? 'User';
    $initials = collect(explode(' ', trim($displayName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');

    $roleNames = method_exists($user, 'getRoleNames')
        ? $user->getRoleNames()->implode(', ')
        : '';

    $role = $roleNames ?: ($user->role ?? 'User');

    $hasPhoto = !empty($user->profile_photo);
    $photoUrl = $hasPhoto
        ? \Illuminate\Support\Facades\Storage::disk('public')
            ->url($user->profile_photo)
        : null;
@endphp

<style>
    .profile-page {
        background: #f4f7fb;
        min-height: 75vh;
        padding: 35px 15px 50px;
        color: #26364b;
    }

    .profile-container {
        max-width: 1100px;
        margin: auto;
    }

    .profile-heading {
        margin-bottom: 25px;
    }

    .profile-heading h3 {
        font-size: 26px;
        font-weight: 750;
        color: #172b4d;
        margin-bottom: 7px;
    }

    .profile-heading p {
        color: #7b8798;
        margin: 0;
        font-size: 14px;
    }

    .profile-card {
        background: #fff;
        border: 1px solid #e7edf5;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(26, 45, 75, .045);
        margin-bottom: 22px;
    }

    .profile-cover {
        height: 145px;
        background: linear-gradient(120deg, #123c80, #2679c9);
        position: relative;
    }

    .profile-cover::after {
        content: "";
        position: absolute;
        width: 230px;
        height: 230px;
        border: 35px solid rgba(255, 255, 255, .06);
        border-radius: 50%;
        right: 8%;
        top: -105px;
    }

    .profile-identity {
        padding: 0 32px 27px;
        position: relative;
    }

    .profile-avatar {
        width: 108px;
        height: 108px;
        border-radius: 50%;
        border: 5px solid #fff;
        background: #e6efff;
        color: #1768b5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
        font-weight: 750;
        overflow: hidden;
        margin-top: -54px;
        position: relative;
        z-index: 1;
        box-shadow: 0 4px 15px rgba(20, 50, 90, .12);
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-name {
        color: #172b4d;
        font-weight: 750;
        font-size: 23px;
        margin: 16px 0 6px;
        overflow-wrap: anywhere;
    }

    .profile-email {
        color: #7b8798;
        font-size: 14px;
        overflow-wrap: anywhere;
    }

    .profile-role {
        display: inline-block;
        margin-top: 13px;
        padding: 7px 13px;
        border-radius: 20px;
        color: #1556a0;
        background: #eaf3ff;
        font-size: 12px;
        font-weight: 700;
    }

    .profile-section-title {
        font-size: 16px;
        font-weight: 750;
        color: #20334f;
        padding: 22px 25px;
        border-bottom: 1px solid #edf1f6;
        margin: 0;
    }

    .profile-details {
        padding: 5px 25px 20px;
    }

    .profile-detail {
        padding: 19px 0;
        border-bottom: 1px solid #edf1f6;
        display: flex;
        gap: 15px;
        align-items: flex-start;
    }

    .profile-detail:last-child {
        border-bottom: none;
    }

    .profile-detail-icon {
        width: 40px;
        height: 40px;
        min-width: 40px;
        border-radius: 10px;
        background: #f0f5fc;
        color: #2868ad;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-detail-label {
        color: #8793a5;
        font-size: 12px;
        margin-bottom: 5px;
    }

    .profile-detail-value {
        color: #26364b;
        font-size: 14px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .profile-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 11px;
        border-radius: 20px;
        background: #e8f8ef;
        color: #198754;
        font-size: 12px;
        font-weight: 700;
    }

    .profile-status.inactive {
        background: #fff0f0;
        color: #c0392b;
    }

    .profile-help {
        background: #eef5ff;
        border: 1px solid #dceaff;
        border-radius: 12px;
        padding: 18px;
        color: #426080;
        font-size: 13px;
        line-height: 1.7;
    }

    .profile-help i {
        color: #1768b5;
    }

    @media (max-width: 576px) {
        .profile-page {
            padding: 22px 10px 35px;
        }

        .profile-identity {
            padding: 0 22px 23px;
        }

        .profile-section-title {
            padding: 20px;
        }

        .profile-details {
            padding: 5px 20px 15px;
        }

        .profile-name {
            font-size: 21px;
        }
    }
</style>

<div class="profile-page">
    <div class="profile-container">

        {{-- PAGE HEADING --}}
        <div class="profile-heading">
            <h3>My Profile</h3>
            <p>View your personal details and account information.</p>
        </div>

        {{-- PROFILE OVERVIEW --}}
        <div class="profile-card">
            <div class="profile-cover"></div>

            <div class="profile-identity">
                <div class="profile-avatar">
                    @if ($photoUrl)
                        <img src="{{ $photoUrl }}"
                             alt="{{ $displayName }}'s profile photo">
                    @else
                        {{ $initials ?: 'U' }}
                    @endif
                </div>

                <h4 class="profile-name">
                    {{ $displayName }}
                </h4>

                <div class="profile-email">
                    <i class="fas fa-envelope me-2"></i>
                    {{ $user->email ?: 'No email provided' }}
                </div>

                <span class="profile-role">
                    <i class="fas fa-user-shield me-1"></i>
                    {{ \Illuminate\Support\Str::headline($role) }}
                </span>
            </div>
        </div>

        <div class="row">

            {{-- PERSONAL INFORMATION --}}
            <div class="col-lg-7">
                <div class="profile-card">
                    <h5 class="profile-section-title">
                        <i class="fas fa-user me-2 text-primary"></i>
                        Personal Information
                    </h5>

                    <div class="profile-details">

                        <div class="profile-detail">
                            <div class="profile-detail-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="profile-detail-label">
                                    Full Name
                                </div>
                                <div class="profile-detail-value">
                                    {{ $user->name ?: 'Not provided' }}
                                </div>
                            </div>
                        </div>

                        <div class="profile-detail">
                            <div class="profile-detail-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <div class="profile-detail-label">
                                    Email Address
                                </div>
                                <div class="profile-detail-value">
                                    {{ $user->email ?: 'Not provided' }}
                                </div>
                            </div>
                        </div>

                        <div class="profile-detail">
                            <div class="profile-detail-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <div class="profile-detail-label">
                                    Phone Number
                                </div>
                                <div class="profile-detail-value">
                                    {{ $user->phone ?: 'Not provided' }}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ACCOUNT INFORMATION --}}
            <div class="col-lg-5">
                <div class="profile-card">
                    <h5 class="profile-section-title">
                        <i class="fas fa-id-card me-2 text-primary"></i>
                        Account Information
                    </h5>

                    <div class="profile-details">

                        <div class="profile-detail">
                            <div class="profile-detail-icon">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <div>
                                <div class="profile-detail-label">
                                    Account Role
                                </div>
                                <div class="profile-detail-value">
                                    {{ \Illuminate\Support\Str::headline($role) }}
                                </div>
                            </div>
                        </div>

                        <div class="profile-detail">
                            <div class="profile-detail-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div>
                                <div class="profile-detail-label">
                                    Date Registered
                                </div>
                                <div class="profile-detail-value">
                                    {{ $user->created_at
                                        ? $user->created_at->format('d M Y')
                                        : 'Not available' }}
                                </div>
                            </div>
                        </div>

                        @if (isset($user->status))
                            <div class="profile-detail">
                                <div class="profile-detail-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <div class="profile-detail-label">
                                        Account Status
                                    </div>

                                    @php
                                        $isActive = in_array(
                                            strtolower((string) $user->status),
                                            ['active', '1', 'enabled']
                                        );
                                    @endphp

                                    <span class="profile-status {{ $isActive ? '' : 'inactive' }}">
                                        <i class="fas fa-circle"
                                           style="font-size: 7px;"></i>
                                        {{ \Illuminate\Support\Str::headline((string) $user->status) }}
                                    </span>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- SECURITY NOTE --}}
                <div class="profile-help">
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>Account security</strong>
                    <br>
                    Keep your login credentials private. Use the
                    Change Password page to update your password.
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
