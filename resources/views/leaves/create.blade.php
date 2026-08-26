@extends('layouts.master')

@section('title', 'Applying Leave')

@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Leave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a73e8;
            --primary-light: #e8f0fe;
            --primary-dark: #1557b0;
            --primary-gradient: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            --secondary-color: #4285f4;
            --accent-color: #34a853;
            --border-color: #dadce0;
            --text-primary: #202124;
            --text-secondary: #5f6368;
            --bg-light: #f0f7ff;
            --shadow-color: rgba(26, 115, 232, 0.15);
        }
        
        body {
            background-color: #f5f9ff;
            font-family: 'Segoe UI', 'Roboto', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-primary);
            line-height: 1.6;
        }
        
        .application-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(26, 115, 232, 0.10);
            overflow: hidden;
            background: white;
        }
        
        .card-header {
            background: var(--primary-gradient);
            color: white;
            border-bottom: none;
            padding: 1.8rem 2.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }
        
        .card-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }
        
        .card-header h4, .card-header p {
            position: relative;
            z-index: 1;
        }
        
        .card-body {
            padding: 2.5rem;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--primary-light);
            position: relative;
            font-size: 1.1rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--primary-gradient);
            border-radius: 3px;
        }
        
        .section-title i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        .section-guide {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            padding: 0.75rem 1.25rem;
            background-color: var(--primary-light);
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
        }
        
        .section-guide i {
            color: var(--primary-color);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-size: 0.9rem;
        }
        
        .form-label.required::after {
            content: " *";
            color: #dc3545;
            font-weight: bold;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--border-color);
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.15);
            border-color: var(--primary-color);
        }
        
        .form-control-lg {
            padding: 0.9rem 1.25rem;
            font-size: 1rem;
        }
        
        .input-group-text {
            background-color: #f8faff;
            border: 1.5px solid var(--border-color);
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .input-group .form-control:focus {
            border-left: none;
        }
        
        #signature-pad {
            border: 2px dashed #c5d8f8;
            border-radius: 12px;
            background-color: white;
            cursor: crosshair;
            width: 50%;
            height: 100%;
            touch-action: none;
            transition: border-color 0.3s ease;
        }
        
        #signature-pad:hover {
            border-color: var(--primary-color);
        }
        
        .signature-container {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .signature-clear {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            background: white;
            border: 1px solid #dc3545;
            color: #dc3545;
            border-radius: 8px;
            padding: 0.3rem 0.8rem;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .signature-clear:hover {
            background: #dc3545;
            color: white;
        }
        
        .official-section {
            background: linear-gradient(135deg, #f8faff 0%, #eef6ff 100%);
            border: 1px solid #d4e4ff;
            border-radius: 14px;
            padding: 1.8rem;
        }
        
        .official-title {
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 1px dashed #d4e4ff;
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .official-title i {
            color: var(--primary-color);
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 115, 232, 0.35);
            color: white;
        }
        
        .btn-outline-secondary {
            border-radius: 10px;
            padding: 0.8rem 2rem;
            font-weight: 500;
            border: 1.5px solid var(--border-color);
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }
        
        .btn-outline-secondary:hover {
            background: #f8faff;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-info {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 115, 232, 0.35);
            color: white;
        }
        
        .info-badge {
            display: inline-flex;
            align-items: center;
            background-color: var(--primary-light);
            color: var(--primary-color);
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .info-badge i {
            margin-right: 0.4rem;
        }
        
        .calculation-hint {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 0.4rem;
        }
        
        .calculation-hint i {
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
            
            .card-header {
                padding: 1.25rem;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .d-flex.gap-3 {
                flex-direction: column;
            }
            
            #signature-pad {
                width: 100%;
            }
        }
        
        .step-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
            padding: 0 1rem;
        }
        
        .step-progress::before {
            content: '';
            position: absolute;
            top: 16px;
            left: 10%;
            right: 10%;
            height: 3px;
            background-color: #e8f0fe;
            z-index: 1;
            border-radius: 3px;
        }
        
        .step {
            position: relative;
            z-index: 2;
            text-align: center;
            width: 100%;
        }
        
        .step-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background-color: #e8f0fe;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-size: 14px;
            font-weight: 600;
            color: #5f6368;
            transition: all 0.3s ease;
        }
        
        .step.active .step-icon {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.3);
        }
        
        .step.completed .step-icon {
            background-color: var(--accent-color);
            color: white;
        }
        
        .step-text {
            font-size: 0.8rem;
            color: #9aa0a6;
            font-weight: 500;
        }
        
        .step.active .step-text {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .step.completed .step-text {
            color: var(--accent-color);
        }
        
        /* Custom checkbox and radio styling */
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        /* Alert styling */
        .alert-info {
            background-color: var(--primary-light);
            border-color: #c5d8f8;
            color: var(--primary-dark);
        }
        
        .alert-info i {
            color: var(--primary-color);
        }
        
        /* Badge styling */
        .badge-light {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        /* Form validation styling */
        .was-validated .form-control:invalid,
        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        .was-validated .form-control:valid,
        .form-control.is-valid {
            border-color: var(--accent-color);
        }
    </style>
</head>
<body>
    <div class="container py-4 application-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0 fw-bold"><i class="fas fa-file-alt me-2"></i>Apply for Leave</h4>
                    <p class="mb-0 mt-1 opacity-75">Complete the form below to submit your leave request</p>
                </div>
                <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Back to Leaves
                </a>
            </div>

            <div class="card-body">
                <!-- Progress Steps -->
                <div class="step-progress">
                    <div class="step active">
                        <div class="step-icon">1</div>
                        <div class="step-text">Applicant Info</div>
                    </div>
                    <div class="step">
                        <div class="step-icon">2</div>
                        <div class="step-text">Leave Details</div>
                    </div>
                    <div class="step">
                        <div class="step-icon">3</div>
                        <div class="step-text">Signature</div>
                    </div>
                    <div class="step">
                        <div class="step-icon">4</div>
                        <div class="step-text">Review</div>
                    </div>
                </div>

                <form action="{{ route('leaves.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf

                    <!-- Applicant Details -->
                    <div class="mb-5">
                        <h5 class="section-title"><i class="fas fa-user-circle"></i>Your Details</h5>
                        <div class="section-guide">
                            <i class="fas fa-info-circle me-2"></i>Please provide your personal information and select the type of leave you're applying for.
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="fullName" class="form-label required">Full Name</label>
                                <input type="text" id="fullName" name="full_name" class="form-control form-control-lg" placeholder="Enter your full name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="designation" class="form-label">Designation</label>
                                <input type="text" id="designation" name="designation" class="form-control form-control-lg" placeholder="Enter your designation">
                            </div>
                            <div class="col-md-6">
                                <label for="contactNumber" class="form-label">Contact Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">+233</span>
                                    <input type="tel" id="contactNumber" name="contact_number" class="form-control form-control-lg" placeholder="Enter contact number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="leaveType" class="form-label required">Leave Type</label>
                                <select id="leaveType" name="leave_type" class="form-select form-select-lg" required>
                                    <option value="" disabled selected>— Select leave type —</option>
                                    <option>Annual leave</option>
                                    <option>Part leave</option>
                                    <option>Maternity leave</option>
                                    <option>Sick leave</option>
                                    <option>Unpaid leave</option>
                                    <option>Leave of Absence</option>
                                    <option>Funeral-Relationship</option>
                                    <option>Vacation leave</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="reason" class="form-label">Reason for Leave</label>
                                <textarea id="reason" name="reason" class="form-control" rows="3" placeholder="Briefly explain the reason for your leave..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Details -->
                    <div class="mb-5">
                        <h5 class="section-title"><i class="fas fa-calendar-alt"></i>Leave Period</h5>
                        <div class="section-guide">
                            <i class="fas fa-info-circle me-2"></i>Provide the dates for your leave. The number of days will be calculated automatically.
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="dateCommencement" class="form-label required">Date of Commencement</label>
                                <input type="date" id="dateCommencement" name="date_commencement" class="form-control form-control-lg" required>
                            </div>
                            <div class="col-md-6">
                                <label for="dateResumption" class="form-label required">Date of Resumption</label>
                                <input type="date" id="dateResumption" name="date_resumption" class="form-control form-control-lg" required>
                            </div>
                            <div class="col-md-6">
                                <label for="daysApplied" class="form-label">Days Applied For</label>
                                <input type="number" id="daysApplied" name="days_applied_for" class="form-control form-control-lg bg-light" min="0" readonly>
                                <div class="calculation-hint"><i class="fas fa-calculator me-1"></i>This field is automatically calculated based on your selected dates</div>
                            </div>
                            <div class="col-md-6">
                                <label for="date_of_application" class="form-label required">Date of Application</label>
                                <input type="date" id="date_of_application" name="date_of_application" class="form-control form-control-lg" required>
                            </div>
                        </div>
                    </div>

                    <!-- Previous Leave Info -->
                    <div class="mb-5">
                        <h5 class="section-title"><i class="fas fa-history"></i>Previous Leave Information</h5>
                        <div class="section-guide">
                            <i class="fas fa-info-circle me-2"></i>Provide information about your previous leave (if applicable).
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="dateLastLeave" class="form-label">Date of Last Leave</label>
                                <input type="date" id="dateLastLeave" name="date_last_leave" class="form-control form-control-lg">
                            </div>
                            <div class="col-md-6">
                                <label for="daysEntitled" class="form-label">Days Entitled</label>
                                <input type="number" id="daysEntitled" name="days_entitled" class="form-control form-control-lg" min="0" placeholder="Enter days entitled">
                            </div>
                            <div class="col-md-6">
                                <label for="daysUtilized" class="form-label">Days Already Utilized</label>
                                <input type="number" id="daysUtilized" name="days_already_utilized" class="form-control form-control-lg" min="0" placeholder="Enter days utilized">
                            </div>
                        </div>
                    </div>

                    <!-- Signature & Submission -->
                    <div class="mb-5">
                        <h5 class="section-title"><i class="fas fa-pen"></i>Signature</h5>
                        <div class="section-guide">
                            <i class="fas fa-info-circle me-2"></i>By signing, you confirm that the information provided is accurate and complete.
                        </div>
                        
                        <div class="signature-container">
                            <canvas id="signature-pad" width="250" height="150"></canvas>
                            <button type="button" id="clear" class="btn btn-sm btn-outline-danger signature-clear">
                                <i class="fas fa-eraser me-1"></i> Clear
                            </button>
                        </div>
                        <input type="hidden" name="signature" id="signature">
                        <div class="calculation-hint"><i class="fas fa-pen me-1"></i>Draw your signature in the box above</div>
                    </div>

                    <!-- Official Use Only -->
                    <div class="official-section mb-5">
                        <h5 class="official-title"><i class="fas fa-lock me-2"></i>For Official Use Only</h5>
                        <div class="section-guide">
                            <i class="fas fa-exclamation-circle me-2"></i>This section is for administrative purposes and should not be filled by the applicant.
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="respectOf" class="form-label">Application for Leave in Respect of</label>
                                <input type="text" id="respectOf" name="respect_of" class="form-control" disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="recommendation" class="form-label">Recommendation</label>
                                <select id="recommendation" name="recommendation" class="form-select" disabled>
                                    <option value="" disabled selected>— Select —</option>
                                    <option value="recommended">Recommended</option>
                                    <option value="not_recommended">Not Recommended</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <label for="daysGranted" class="form-label">Days Granted</label>
                                <input type="number" id="daysGranted" name="days_granted" class="form-control form-control-lg bg-light" min="0" >
                                <div class="calculation-hint"><i class="fas fa-info-circle me-1"></i>This field is based on the coordinator's discretion</div>
                            </div>
                            <div class="col-md-6">
                                <label for="adminResumptionDate" class="form-label">Date of Resumption</label>
                                <input type="date" id="adminResumptionDate" name="admin_resumption_date" class="form-control form-control-lg" disabled>
                            </div>
                        </div>
                        
                        
                        <div class="row g-4 mt-3">
                            <!-- Admin -->
                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded-3 shadow-sm">
                                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-user-shield me-2"></i>Administrator</h6>
                                    <div class="mb-3">
                                        <label for="adminName" class="form-label">Name</label>
                                        <input type="text" id="adminName" name="administrator_name" class="form-control" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="adminSignature" class="form-label">Signature</label>
                                        <input type="text" id="adminSignature" name="administrator_signature" class="form-control" disabled>
                                    </div>
                                    <div>
                                        <label for="adminDate" class="form-label">Date</label>
                                        <input type="date" id="adminDate" name="administrator_date" class="form-control" disabled>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Zonal Coordinator -->
                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded-3 shadow-sm">
                                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-user-tie me-2"></i>Head Master</h6>
                                    <div class="mb-3">
                                        <label for="zonalName" class="form-label">Name</label>
                                        <input type="text" id="zonalName" name="zonal_coordinator_name" class="form-control" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="zonalSignature" class="form-label">Signature</label>
                                        <input type="text" id="zonalSignature" name="zonal_coordinator_signature" class="form-control" disabled>
                                    </div>
                                    <div>
                                        <label for="zonalDate" class="form-label">Date</label>
                                        <input type="date" id="zonalDate" name="zonal_coordinator_date" class="form-control" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit and Cancel Buttons -->
                    <div class="d-flex justify-content-end gap-3 mt-5">
                        <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-info btn-lg px-4">
                            <i class="fas fa-paper-plane me-2"></i>Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>
    <script>
        // Initialize signature pad
        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)'
        });
        
        // Adjust canvas size for high DPI displays
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }
        
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        // Form submission handler
        document.querySelector('form').addEventListener('submit', function (e) {
            if (!signaturePad.isEmpty()) {
                document.getElementById('signature').value = signaturePad.toDataURL();
            } else {
                e.preventDefault();
                alert('Please provide your signature before submitting the form.');
            }
        });

        // Clear signature
        document.getElementById('clear').addEventListener('click', function () {
            signaturePad.clear();
        });

        // Auto-calculate days applied
        const commencementDate = document.getElementById('dateCommencement');
        const resumptionDate = document.getElementById('dateResumption');
        const daysApplied = document.getElementById('daysApplied');

        function calculateDays() {
            if (commencementDate.value && resumptionDate.value) {
                const start = new Date(commencementDate.value);
                const end = new Date(resumptionDate.value);
                
                // Validate that end date is after start date
                if (end <= start) {
                    alert('Resumption date must be after commencement date');
                    resumptionDate.value = '';
                    daysApplied.value = '';
                    return;
                }
                
                const diffTime = Math.abs(end - start);
                daysApplied.value = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            }
        }

        commencementDate.addEventListener('change', calculateDays);
        resumptionDate.addEventListener('change', calculateDays);
        
        // Add today's date to the application date field by default
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const yyyy = today.getFullYear();
            let mm = today.getMonth() + 1;
            let dd = today.getDate();
            
            if (dd < 10) dd = '0' + dd;
            if (mm < 10) mm = '0' + mm;
            
            const formattedToday = `${yyyy}-${mm}-${dd}`;
            document.getElementById('date_of_application').value = formattedToday;
        });
    </script>
</body>
</html>
@endsection