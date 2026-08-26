<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg shadow-sm mb-4" 
     style="background: linear-gradient(135deg, #e3f2fd, #bbdefb, #90caf9); border-bottom: 3px solid #1976d2;">

    <div class="container-fluid">
        <!-- Brand/Logo -->
        <div class="d-flex align-items-center">
            <div class="brand-icon me-3" 
                 style="width: 40px; height: 40px; background: linear-gradient(135deg, #0d47a1, #1976d2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-graduation-cap text-white"></i>
            </div>
            <h4 class="mb-0" style="color: #0d47a1; font-weight: 700;">
                Student Dashboard
                <small class="text-muted d-block" style="font-size: 0.65rem; font-weight: 400; color: #1565c0 !important;">
                    <i class="fas fa-user-graduate me-1"></i> Welcome back!
                </small>
            </h4>
        </div>

        <!-- Right Side -->
        <div class="d-flex align-items-center">
            <!-- Notifications -->
            <div class="notification-wrapper me-3 position-relative">
                <button class="btn btn-light btn-sm rounded-circle position-relative" 
                        style="width: 40px; height: 40px; background: rgba(255,255,255,0.8); border: 1px solid rgba(13, 71, 161, 0.2);">
                    <i class="fas fa-bell fs-5" style="color: #0d47a1;"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                          style="font-size: 0.6rem; padding: 0.25rem 0.5rem;">
                        3
                    </span>
                </button>
            </div>

            <!-- User Profile -->
            <div class="user-profile d-flex align-items-center ms-2 p-2 rounded-3"
                 style="background: rgba(255,255,255,0.7); border: 1px solid rgba(13, 71, 161, 0.15);">
                <!-- Avatar -->
                <div class="avatar-wrapper me-2 position-relative">
                    <img src="{{ Auth::guard('student')->user()->photo
                            ? asset('storage/'.Auth::guard('student')->user()->photo)
                            : asset('images/default-avatar.png') }}"
                         width="45"
                         height="45"
                         class="rounded-circle border border-2" 
                         style="border-color: #1976d2 !important; object-fit: cover;">
                    <!-- Online Status Indicator -->
                    <span class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success rounded-circle border border-2 border-white"
                          style="width: 12px; height: 12px;"></span>
                </div>

                <!-- User Info -->
                <div class="user-info" style="line-height: 1.2;">
                    <strong class="d-block" style="color: #0d47a1; font-size: 0.9rem;">
                        {{ Auth::guard('student')->user()->full_name }}
                    </strong>
                    <small class="text-muted" style="color: #1565c0 !important; font-size: 0.7rem;">
                        <i class="fas fa-id-card me-1"></i>
                        {{ Auth::guard('student')->user()->student_id }}
                    </small>
                </div>

                <!-- Logout Button -->
                <button id="logoutBtn" 
                        class="btn btn-sm ms-2" 
                        style="background: rgba(13, 71, 161, 0.1); color: #0d47a1; border: 1px solid rgba(13, 71, 161, 0.2); border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;"
                        onmouseover="this.style.background='rgba(211, 47, 47, 0.15)'; this.style.borderColor='#d32f2f'; this.style.color='#d32f2f';"
                        onmouseout="this.style.background='rgba(13, 71, 161, 0.1)'; this.style.borderColor='rgba(13, 71, 161, 0.2)'; this.style.color='#0d47a1';"
                        title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); border-radius: 16px 16px 0 0;">
                <div class="d-flex align-items-center">
                    <div class="icon-wrapper me-3" 
                         style="width: 50px; height: 50px; background: linear-gradient(135deg, #0d47a1, #1976d2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-sign-out-alt text-white fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold" id="logoutModalLabel" style="color: #0d47a1;">
                            Confirm Logout
                        </h5>
                        <small class="text-muted" style="color: #1565c0 !important;">
                            Are you sure you want to leave?
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-4 py-4">
                <div class="alert alert-info d-flex align-items-center" 
                     style="background: #e3f2fd; border: 1px solid #90caf9; border-radius: 12px;">
                    <i class="fas fa-info-circle me-3" style="color: #0d47a1; font-size: 1.2rem;"></i>
                    <div>
                        <strong style="color: #0d47a1;">Hey {{ Auth::guard('student')->user()->first_name ?? 'Student' }}!</strong>
                        <p class="mb-0" style="color: #1565c0; font-size: 0.9rem;">
                            You will be redirected to the login page after logging out.
                        </p>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div>
                        <i class="fas fa-user-circle me-2" style="color: #1976d2;"></i>
                        <span style="color: #0d47a1; font-weight: 500;">
                            {{ Auth::guard('student')->user()->full_name }}
                        </span>
                    </div>
                    <span class="badge" style="background: #1976d2; padding: 0.4rem 0.8rem;">
                        <i class="fas fa-clock me-1"></i> Session Active
                    </span>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" 
                        style="border-radius: 10px; padding: 0.6rem 1.8rem; border: 1px solid #dee2e6;">
                    <i class="fas fa-times me-2"></i> Cancel
                </button>
                <button type="button" class="btn" id="confirmLogoutBtn"
                        style="background: linear-gradient(135deg, #0d47a1, #1976d2); color: white; border-radius: 10px; padding: 0.6rem 1.8rem; border: none; box-shadow: 0 4px 15px rgba(13, 71, 161, 0.3); transition: all 0.3s ease;"
                        onmouseover="this.style.boxShadow='0 6px 25px rgba(13, 71, 161, 0.4)';"
                        onmouseout="this.style.boxShadow='0 4px 15px rgba(13, 71, 161, 0.3)';">
                    <i class="fas fa-sign-out-alt me-2"></i> Yes, Logout
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Logout Toast Notification -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 9999; display: none;" id="logoutToast">
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" 
         style="border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.12); border-left: 4px solid #1976d2;">
        <div class="toast-header" style="background: linear-gradient(135deg, #0d47a1, #1976d2); color: white; border-radius: 12px 12px 0 0;">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" style="color: #0d47a1; padding: 1rem;">
            <i class="fas fa-spinner fa-spin me-2"></i>
            You have been logged out successfully. Redirecting...
        </div>
    </div>
</div>

<style>
    /* Navbar Styles */
    .navbar {
        border-radius: 16px !important;
        padding: 0.8rem 1.5rem !important;
        backdrop-filter: blur(10px);
    }

    .brand-icon {
        transition: transform 0.3s ease;
    }
    .brand-icon:hover {
        transform: scale(1.05) rotate(-5deg);
    }

    .notification-wrapper .btn {
        transition: all 0.3s ease;
    }
    .notification-wrapper .btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(13, 71, 161, 0.2);
    }

    .user-profile {
        transition: all 0.3s ease;
    }
    .user-profile:hover {
        box-shadow: 0 4px 20px rgba(13, 71, 161, 0.15);
        transform: translateY(-2px);
    }

    .avatar-wrapper {
        cursor: pointer;
    }
    .avatar-wrapper img {
        transition: all 0.3s ease;
    }
    .avatar-wrapper:hover img {
        transform: scale(1.05);
        border-color: #0d47a1 !important;
    }

    /* Modal Styles */
    .modal-content {
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* Toast Animation */
    .toast {
        animation: slideInRight 0.5s ease-out;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .navbar {
            padding: 0.6rem 1rem !important;
            border-radius: 12px !important;
        }
        
        .user-info {
            display: none;
        }
        
        .user-profile {
            padding: 0.3rem !important;
        }
        
        .brand-icon {
            width: 32px;
            height: 32px;
        }
        
        .brand-icon i {
            font-size: 0.8rem;
        }
        
        h4 {
            font-size: 1.1rem;
        }
    }

    @media (max-width: 576px) {
        .navbar {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .user-profile {
            margin-left: 0 !important;
        }
        
        .notification-wrapper {
            margin-right: 0.5rem !important;
        }
    }
</style>

<!-- Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ---- DOM Elements ----
        const logoutBtn = document.getElementById('logoutBtn');
        const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
        const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'), {
            backdrop: 'static',
            keyboard: true
        });
        const logoutToast = document.getElementById('logoutToast');

        // ---- Logout Button Click ----
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            logoutModal.show();
        });

        // ---- Confirm Logout ----
        confirmLogoutBtn.addEventListener('click', function() {
            // Show loading state
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Logging out...';

            // Show toast notification
            logoutToast.style.display = 'block';
            
            // Hide modal
            logoutModal.hide();

            // Redirect to login page after 2 seconds
            setTimeout(function() {
                // Use Laravel logout route
                const logoutForm = document.createElement('form');
                logoutForm.method = 'POST';
                logoutForm.action = '{{ route("logout") }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                logoutForm.appendChild(csrfToken);
                
                document.body.appendChild(logoutForm);
                logoutForm.submit();
            }, 2000);
        });

        // ---- Reset Modal on Close ----
        document.getElementById('logoutModal').addEventListener('hidden.bs.modal', function() {
            confirmLogoutBtn.disabled = false;
            confirmLogoutBtn.innerHTML = '<i class="fas fa-sign-out-alt me-2"></i> Yes, Logout';
        });

        // ---- Keyboard Shortcut: Ctrl+Shift+L ----
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && (e.key === 'L' || e.key === 'l')) {
                e.preventDefault();
                logoutBtn.click();
            }
        });

        // ---- Auto-hide toast after 3 seconds if not closed ----
        setTimeout(function() {
            const toast = document.querySelector('.toast');
            if (toast) {
                const bsToast = new bootstrap.Toast(toast, {
                    delay: 5000
                });
                // Only auto-hide if not clicked
                toast.addEventListener('hidden.bs.toast', function() {
                    logoutToast.style.display = 'none';
                });
            }
        }, 100);

        console.log('Student dashboard with logout confirmation initialized');
    });
</script>