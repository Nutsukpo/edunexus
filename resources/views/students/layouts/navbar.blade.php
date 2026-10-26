{{--
    Student Portal Top Bar
    Requires: Bootstrap 5 (JS bundle), Font Awesome 5/6
    Optional variables: $pageTitle (string), $unreadNotifications (int)
    Any element with the attribute  data-logout-trigger  opens the logout confirmation
    (e.g. the sidebar logout button).
--}}

@php
    $u        = Auth::guard('student')->user();
    $fullName = $u->full_name ?? 'Student';
    $firstName = $u->first_name ?? explode(' ', trim($fullName))[0];
    $studentId = $u->student_id ?? '';
    $initials = collect(explode(' ', trim($fullName)))->filter()->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: 'S';
    $notifCount = (int) ($unreadNotifications ?? 0);
    $title = $pageTitle ?? 'Student Dashboard';
@endphp

<header class="tb">
    <div class="tb-left">
        <span class="tb-mark"><i class="fas fa-graduation-cap"></i></span>
        <div class="tb-titles">
            <h1>{{ $title }}</h1>
            <span>{{ now()->format('l, j F Y') }}</span>
        </div>
    </div>

    <div class="tb-right">
        {{-- Notifications --}}
        <button type="button" class="tb-icon-btn" aria-label="Notifications{{ $notifCount ? ' ('.$notifCount.' unread)' : '' }}">
            <i class="far fa-bell"></i>
            @if ($notifCount > 0)
                <span class="tb-dot">{{ $notifCount > 9 ? '9+' : $notifCount }}</span>
            @endif
        </button>

        <span class="tb-divider"></span>

        {{-- User menu --}}
        <div class="dropdown">
            <button class="tb-user" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="tb-avatar">
                    @if (!empty($u->photo))
                        <img src="{{ asset('storage/' . $u->photo) }}" alt="{{ $fullName }}">
                    @else
                        {{ $initials }}
                    @endif
                    <i class="tb-status"></i>
                </span>
                <span class="tb-user-info">
                    <strong>{{ $fullName }}</strong>
                    <small>{{ $studentId }}</small>
                </span>
                <i class="fas fa-chevron-down tb-caret"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end tb-menu">
                <li class="tb-menu-head">
                    <strong>{{ $fullName }}</strong>
                    <small><i class="fas fa-id-card"></i> {{ $studentId }}</small>
                </li>
                <li><a class="dropdown-item" href="{{ url('/student/profile') }}"><i class="far fa-user"></i> My profile</a></li>
                <li><a class="dropdown-item" href="{{ url('/student/fees') }}"><i class="fas fa-wallet"></i> School fees</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button type="button" class="dropdown-item text-danger" data-logout-trigger>
                        <i class="fas fa-right-from-bracket"></i> Log out
                    </button>
                </li>
            </ul>
        </div>
    </div>
</header>

{{-- ================= Logout modal ================= --}}
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content tb-modal">
            <div class="tb-modal-body">
                <span class="tb-modal-icon"><i class="fas fa-right-from-bracket"></i></span>
                <h5 id="logoutModalLabel">Log out of your account?</h5>
                <p>
                    {{ $firstName }}, you'll be signed out of the Student Portal and returned to the login page.
                </p>
                <div class="tb-modal-actions">
                    <button type="button" class="tb-btn ghost" data-bs-dismiss="modal">Stay signed in</button>
                    <button type="button" class="tb-btn danger" id="confirmLogoutBtn">
                        <i class="fas fa-right-from-bracket"></i> Log out
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="topbarLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

<style>
    .tb {
        --tb-navy: #0b2447; --tb-blue: #1d6fd6; --tb-ink: #14213d; --tb-muted: #667388; --tb-line: #e7ebf1;
        position: sticky; top: 0; z-index: 1030;
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        height: 68px; padding: 0 clamp(16px, 3vw, 36px);
        background: rgba(255, 255, 255, .88); backdrop-filter: saturate(180%) blur(12px);
        border-bottom: 1px solid var(--tb-line);
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    }
    .tb-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .tb-mark {
        width: 40px; height: 40px; flex-shrink: 0; border-radius: 12px; display: grid; place-items: center;
        color: #fff; background: linear-gradient(135deg, #4da3ff, #1d6fd6); box-shadow: 0 4px 12px rgba(29,111,214,.35);
    }
    .tb-titles h1 { margin: 0; font-size: 17px; font-weight: 700; letter-spacing: -.01em; color: var(--tb-ink); line-height: 1.2; }
    .tb-titles span { font-size: 12px; color: var(--tb-muted); }

    .tb-right { display: flex; align-items: center; gap: 12px; }
    .tb-divider { width: 1px; height: 28px; background: var(--tb-line); }

    .tb-icon-btn {
        position: relative; width: 40px; height: 40px; border-radius: 12px; border: 1px solid var(--tb-line);
        background: #fff; color: var(--tb-navy); font-size: 16px; display: grid; place-items: center;
        transition: background .15s ease, border-color .15s ease;
    }
    .tb-icon-btn:hover { background: #f1f5fb; border-color: #d3dcea; }
    .tb-dot {
        position: absolute; top: -5px; right: -5px; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 99px;
        background: #d64545; color: #fff; font-size: 10px; font-weight: 700; display: grid; place-items: center;
        border: 2px solid #fff;
    }

    .tb-user {
        display: flex; align-items: center; gap: 10px; padding: 5px 10px 5px 5px; border-radius: 14px;
        border: 1px solid transparent; background: transparent; text-align: left; transition: background .15s ease, border-color .15s ease;
    }
    .tb-user:hover, .tb-user[aria-expanded="true"] { background: #f1f5fb; border-color: var(--tb-line); }
    .tb-avatar {
        position: relative; width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%;
        display: grid; place-items: center; font-size: 13px; font-weight: 700; color: #fff;
        background: linear-gradient(135deg, #1d6fd6, #0b2447);
    }
    .tb-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
    .tb-status { position: absolute; right: -1px; bottom: -1px; width: 11px; height: 11px; border-radius: 50%; background: #16a36a; border: 2px solid #fff; }
    .tb-user-info { line-height: 1.2; }
    .tb-user-info strong { display: block; font-size: 13.5px; color: var(--tb-ink); max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .tb-user-info small { font-size: 11.5px; color: var(--tb-muted); }
    .tb-caret { font-size: 10px; color: var(--tb-muted); }

    .tb-menu { min-width: 230px; padding: 8px; border: 1px solid var(--tb-line); border-radius: 14px; box-shadow: 0 16px 40px rgba(16,36,72,.14); margin-top: 10px !important; }
    .tb-menu-head { padding: 8px 12px 10px; }
    .tb-menu-head strong { display: block; font-size: 14px; color: var(--tb-ink); }
    .tb-menu-head small { font-size: 12px; color: var(--tb-muted); }
    .tb-menu .dropdown-item { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 9px; font-size: 13.5px; }
    .tb-menu .dropdown-item i { width: 16px; text-align: center; opacity: .75; }
    .tb-menu .dropdown-item:hover { background: #eef4fd; }
    .tb-menu .dropdown-item.text-danger:hover { background: #fdecec; }

    /* Modal */
    .tb-modal { border: 0; border-radius: 20px; box-shadow: 0 24px 60px rgba(11,36,71,.25); overflow: hidden; }
    .tb-modal-body { padding: 32px 28px 26px; text-align: center; }
    .tb-modal-icon {
        width: 58px; height: 58px; margin: 0 auto 16px; border-radius: 50%; display: grid; place-items: center;
        font-size: 22px; background: #fdecec; color: #d64545;
    }
    .tb-modal-body h5 { font-size: 19px; font-weight: 700; color: #14213d; margin-bottom: 8px; }
    .tb-modal-body p { font-size: 13.5px; line-height: 1.55; color: #667388; margin-bottom: 24px; }
    .tb-modal-actions { display: flex; gap: 10px; }
    .tb-btn {
        flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 16px;
        border-radius: 12px; font-size: 14px; font-weight: 600; border: 1px solid transparent; transition: background .15s ease, box-shadow .15s ease;
    }
    .tb-btn.ghost { background: #fff; border-color: #dbe2ec; color: #14213d; }
    .tb-btn.ghost:hover { background: #f1f5fb; }
    .tb-btn.danger { background: #d64545; color: #fff; box-shadow: 0 6px 16px rgba(214,69,69,.3); }
    .tb-btn.danger:hover { background: #bf3636; }
    .tb-btn:disabled { opacity: .7; cursor: not-allowed; }

    /* Toast */
    .tb-toast {
        position: fixed; top: 20px; right: 20px; z-index: 2000; display: none; align-items: center; gap: 12px;
        padding: 14px 18px; border-radius: 14px; background: #0b2447; color: #fff; font-size: 13.5px;
        box-shadow: 0 14px 36px rgba(11,36,71,.35); animation: tbIn .3s ease-out;
    }
    .tb-toast.show { display: flex; }
    @keyframes tbIn { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: none; } }

    @media (max-width: 767.98px) {
        .tb { height: 62px; }
        .tb-titles span, .tb-user-info, .tb-caret, .tb-divider { display: none; }
        .tb-user { padding: 4px; }
        .tb-titles h1 { font-size: 15px; }
    }
    @media (prefers-reduced-motion: reduce) { .tb-toast { animation: none; } }
</style>

<div class="tb-toast" id="logoutToast" role="status" aria-live="polite">
    <i class="fas fa-spinner fa-spin"></i> Signing you out…
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('logoutModal');
        if (!modalEl || typeof bootstrap === 'undefined') return;

        var modal = new bootstrap.Modal(modalEl);
        var confirmBtn = document.getElementById('confirmLogoutBtn');
        var toast = document.getElementById('logoutToast');
        var form = document.getElementById('topbarLogoutForm');
        var defaultLabel = confirmBtn.innerHTML;

        document.querySelectorAll('[data-logout-trigger]').forEach(function (el) {
            el.addEventListener('click', function (e) { e.preventDefault(); modal.show(); });
        });

        confirmBtn.addEventListener('click', function () {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing out…';
            toast.classList.add('show');
            modal.hide();
            setTimeout(function () { form.submit(); }, 600);
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = defaultLabel;
        });

        document.addEventListener('keydown', function (e) {
            if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'l') {
                e.preventDefault();
                modal.show();
            }
        });
    });
</script>