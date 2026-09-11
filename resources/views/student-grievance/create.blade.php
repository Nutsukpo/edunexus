@extends('students.layouts.app')

@section('title', 'Submit Student Grievance')

@section('content')
<div class="grievance-page">

    {{-- Page Header --}}
    <div class="grievance-header">
        <div class="header-content">
            <div>
                <div class="eyebrow">
                    <i class="fas fa-comments"></i>
                    Student Support
                </div>

                <h1>Submit a Grievance</h1>

                <p>
                    Tell us about an issue you are experiencing. Your concern will be
                    reviewed by the appropriate school department.
                </p>
            </div>

            <a href="{{ route('student-grievance.index') }}" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>My Grievances</span>
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if($errors->any())
        <div class="alert-box alert-danger">
            <div class="alert-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>

            <div class="alert-content">
                <strong>Please review your submission</strong>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                &times;
            </button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert-box alert-success">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>

            <div class="alert-content">
                <strong>Success</strong>
                <div>{{ session('success') }}</div>
            </div>

            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                &times;
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-box alert-danger">
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>

            <div class="alert-content">
                <strong>Unable to submit</strong>
                <div>{{ session('error') }}</div>
            </div>

            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                &times;
            </button>
        </div>
    @endif

    <form action="{{ route('student-grievance.store') }}"
          method="POST"
          enctype="multipart/form-data"
          id="grievanceForm">

        @csrf

        <div class="form-layout">

            {{-- Main Form --}}
            <div class="main-form">

                {{-- Basic Information --}}
                <section class="form-card">

                    <div class="section-heading">
                        <div class="section-icon blue">
                            <i class="fas fa-file-alt"></i>
                        </div>

                        <div>
                            <h2>Grievance Information</h2>
                            <p>Provide the basic details of your concern.</p>
                        </div>
                    </div>

                    <div class="form-body">

                        {{-- Title + Priority --}}
                        <div class="form-grid grid-title">

                            <div class="field-group">
                                <label for="title">
                                    Grievance Title
                                    <span class="required">*</span>
                                </label>

                                <input type="text"
                                       name="title"
                                       id="title"
                                       value="{{ old('title') }}"
                                       class="form-input @error('title') input-error @enderror"
                                       placeholder="e.g. Issue with examination results"
                                       maxlength="255"
                                       required>

                                @error('title')
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror

                                <div class="field-help">
                                    Use a short and descriptive title.
                                </div>
                            </div>

                            <div class="field-group">
                                <label for="priority">
                                    Priority Level
                                    <span class="required">*</span>
                                </label>

                                <div class="select-wrapper">
                                    <select name="priority"
                                            id="priority"
                                            class="form-input @error('priority') input-error @enderror"
                                            required>

                                        <option value="low"
                                            {{ old('priority') == 'low' ? 'selected' : '' }}>
                                            Low
                                        </option>

                                        <option value="medium"
                                            {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>
                                            Medium
                                        </option>

                                        <option value="high"
                                            {{ old('priority') == 'high' ? 'selected' : '' }}>
                                            High
                                        </option>

                                        <option value="urgent"
                                            {{ old('priority') == 'urgent' ? 'selected' : '' }}>
                                            Urgent
                                        </option>

                                    </select>

                                    <i class="fas fa-chevron-down"></i>
                                </div>

                                @error('priority')
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror

                                <div id="priorityHelp" class="field-help"></div>
                            </div>

                        </div>

                        {{-- Category + Class --}}
                        <div class="form-grid">

                            <div class="field-group">
                                <label for="category_id">
                                    Category
                                </label>

                                <div class="select-wrapper">
                                    <select name="category_id"
                                            id="category_id"
                                            class="form-input @error('category_id') input-error @enderror">

                                        <option value="">
                                            Select a category
                                        </option>

                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach

                                    </select>

                                    <i class="fas fa-chevron-down"></i>
                                </div>

                                @error('category_id')
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="field-group">
                                <label for="class_id">
                                    Class
                                </label>

                                <div class="select-wrapper">
                                    <select name="class_id"
                                            id="class_id"
                                            class="form-input @error('class_id') input-error @enderror">

                                        <option value="">
                                            Select your class
                                        </option>

                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}"
                                                {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach

                                    </select>

                                    <i class="fas fa-chevron-down"></i>
                                </div>

                                @error('class_id')
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                        </div>

                        {{-- Description --}}
                        <div class="field-group">
                            <label for="description">
                                Detailed Description
                                <span class="required">*</span>
                            </label>

                            <textarea name="description"
                                      id="description"
                                      rows="9"
                                      maxlength="5000"
                                      class="form-input textarea @error('description') input-error @enderror"
                                      placeholder="Describe your grievance clearly. Include relevant dates, locations, people involved, and any other information that may help the school understand and resolve the issue."
                                      required>{{ old('description') }}</textarea>

                            <div class="textarea-footer">
                                <div class="field-help">
                                    <i class="fas fa-lightbulb"></i>
                                    Be as specific as possible. Detailed information helps us investigate your concern.
                                </div>

                                <div class="character-count">
                                    <span id="charCount">0</span>/5000
                                </div>
                            </div>

                            @error('description')
                                <div class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>
                </section>


                {{-- Privacy --}}
                <section class="form-card">

                    <div class="section-heading">
                        <div class="section-icon purple">
                            <i class="fas fa-shield-alt"></i>
                        </div>

                        <div>
                            <h2>Privacy & Confidentiality</h2>
                            <p>Choose how your grievance should be handled.</p>
                        </div>
                    </div>

                    <div class="form-body">

                        <div class="privacy-grid">

                            {{-- Confidential --}}
                            <label class="privacy-option" for="is_confidential">

                                <div class="privacy-checkbox">
                                    <input type="checkbox"
                                           name="is_confidential"
                                           id="is_confidential"
                                           value="1"
                                           {{ old('is_confidential', true) ? 'checked' : '' }}>

                                    <span class="custom-check">
                                        <i class="fas fa-check"></i>
                                    </span>
                                </div>

                                <div class="privacy-content">
                                    <div class="privacy-title">
                                        <span class="privacy-icon green">
                                            <i class="fas fa-lock"></i>
                                        </span>

                                        Confidential
                                    </div>

                                    <p>
                                        Your identity will be protected and your grievance
                                        will only be accessible to authorized personnel.
                                    </p>
                                </div>

                            </label>


                            {{-- Anonymous --}}
                            <label class="privacy-option" for="is_anonymous">

                                <div class="privacy-checkbox">
                                    <input type="checkbox"
                                           name="is_anonymous"
                                           id="is_anonymous"
                                           value="1"
                                           {{ old('is_anonymous') ? 'checked' : '' }}>

                                    <span class="custom-check">
                                        <i class="fas fa-check"></i>
                                    </span>
                                </div>

                                <div class="privacy-content">
                                    <div class="privacy-title">
                                        <span class="privacy-icon orange">
                                            <i class="fas fa-user-secret"></i>
                                        </span>

                                        Submit Anonymously
                                    </div>

                                    <p>
                                        Your name will not be disclosed in relation to
                                        this grievance.
                                    </p>
                                </div>

                            </label>

                        </div>

                        <div class="privacy-note">
                            <i class="fas fa-info-circle"></i>
                            Confidential and anonymous submissions may affect how the
                            school follows up with you.
                        </div>

                    </div>
                </section>


                {{-- Attachments --}}
                <section class="form-card">

                    <div class="section-heading">
                        <div class="section-icon orange">
                            <i class="fas fa-paperclip"></i>
                        </div>

                        <div>
                            <h2>Supporting Documents</h2>
                            <p>Attach files that may help explain or support your grievance.</p>
                        </div>
                    </div>

                    <div class="form-body">

                        <div class="attachment-grid">

                            {{-- Single attachment --}}
                            <div class="upload-box">

                                <label for="attachment">
                                    <span class="upload-label">
                                        Single Attachment
                                    </span>

                                    <span class="optional">
                                        Optional
                                    </span>
                                </label>

                                <div class="file-drop">
                                    <input type="file"
                                           name="attachment"
                                           id="attachment"
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                           class="@error('attachment') input-error @enderror">

                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>

                                    <div class="upload-text">
                                        <strong>Choose a file</strong>
                                        <span id="attachmentName">
                                            PDF, DOC, DOCX, JPG or PNG
                                        </span>
                                    </div>

                                    <button type="button"
                                            class="browse-button"
                                            onclick="document.getElementById('attachment').click()">
                                        Browse
                                    </button>
                                </div>

                                <div class="upload-help">
                                    Maximum file size: <strong>10MB</strong>
                                </div>

                                @error('attachment')
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            {{-- Multiple attachments --}}
                            <div class="upload-box">

                                <label for="attachments">
                                    <span class="upload-label">
                                        Multiple Attachments
                                    </span>

                                    <span class="optional">
                                        Optional
                                    </span>
                                </label>

                                <div class="file-drop">
                                    <input type="file"
                                           name="attachments[]"
                                           id="attachments"
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                           multiple
                                           class="@error('attachments') input-error @enderror">

                                    <div class="upload-icon">
                                        <i class="fas fa-copy"></i>
                                    </div>

                                    <div class="upload-text">
                                        <strong>Choose multiple files</strong>
                                        <span id="attachmentsName">
                                            Select supporting documents
                                        </span>
                                    </div>

                                    <button type="button"
                                            class="browse-button"
                                            onclick="document.getElementById('attachments').click()">
                                        Browse
                                    </button>
                                </div>

                                <div class="upload-help">
                                    Maximum file size: <strong>10MB per file</strong>
                                </div>

                                @error('attachments')
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                    </div>
                </section>

            </div>


            {{-- Right Information Panel --}}
            <aside class="side-panel">

                <div class="info-card">

                    <div class="info-card-header">
                        <div class="info-icon">
                            <i class="fas fa-info"></i>
                        </div>

                        <div>
                            <h3>Before You Submit</h3>
                            <p>A few things to keep in mind.</p>
                        </div>
                    </div>

                    <div class="info-list">

                        <div class="info-item">
                            <span class="number">1</span>

                            <div>
                                <strong>Be clear</strong>
                                <p>
                                    Explain the issue clearly and provide relevant facts.
                                </p>
                            </div>
                        </div>

                        <div class="info-item">
                            <span class="number">2</span>

                            <div>
                                <strong>Provide evidence</strong>
                                <p>
                                    Attach supporting documents where necessary.
                                </p>
                            </div>
                        </div>

                        <div class="info-item">
                            <span class="number">3</span>

                            <div>
                                <strong>Choose priority carefully</strong>
                                <p>
                                    Only select urgent when immediate attention is required.
                                </p>
                            </div>
                        </div>

                    </div>

                </div>


                <div class="process-card">

                    <div class="process-header">
                        <i class="fas fa-route"></i>
                        <h3>What Happens Next?</h3>
                    </div>

                    <div class="process-step">
                        <span class="step-dot"></span>

                        <div>
                            <strong>Submission</strong>
                            <p>Your grievance is submitted securely.</p>
                        </div>
                    </div>

                    <div class="process-line"></div>

                    <div class="process-step">
                        <span class="step-dot"></span>

                        <div>
                            <strong>Review</strong>
                            <p>The relevant department reviews your concern.</p>
                        </div>
                    </div>

                    <div class="process-line"></div>

                    <div class="process-step">
                        <span class="step-dot"></span>

                        <div>
                            <strong>Investigation</strong>
                            <p>An assigned officer investigates the issue.</p>
                        </div>
                    </div>

                    <div class="process-line"></div>

                    <div class="process-step">
                        <span class="step-dot"></span>

                        <div>
                            <strong>Response</strong>
                            <p>You can track the progress from your grievance dashboard.</p>
                        </div>
                    </div>

                </div>


                <div class="help-card">
                    <i class="fas fa-headset"></i>

                    <div>
                        <strong>Need help?</strong>
                        <p>
                            If you are unsure what to submit, contact the appropriate
                            school office for assistance.
                        </p>
                    </div>
                </div>

            </aside>

        </div>


        {{-- Bottom Actions --}}
        <div class="form-actions">

            <a href="{{ route('student-grievance.index') }}"
               class="action-button cancel-button">
                <i class="fas fa-times"></i>
                Cancel
            </a>

            <button type="button"
                    class="action-button draft-button"
                    onclick="saveDraft()">
                <i class="fas fa-save"></i>
                Save as Draft
            </button>

            <button type="submit"
                    class="action-button submit-button"
                    id="submitBtn">
                <i class="fas fa-paper-plane"></i>
                Submit Grievance
            </button>

        </div>

    </form>

</div>
@endsection


@push('styles')
<style>

/* =========================================================
   STUDENT GRIEVANCE PAGE
   ========================================================= */

.grievance-page {
    width: 100%;
    max-width: 1500px;
    margin: 0 auto;
    padding: 28px 30px 50px;
    color: #1f2937;
}


/* Header */

.grievance-header {
    margin-bottom: 28px;
}

.header-content {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 25px;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #2563eb;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.grievance-header h1 {
    margin: 0;
    font-size: 30px;
    font-weight: 750;
    letter-spacing: -.5px;
    color: #111827;
}

.grievance-header p {
    margin: 8px 0 0;
    color: #6b7280;
    font-size: 14px;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 10px 16px;
    border: 1px solid #dbe2ea;
    border-radius: 9px;
    background: #fff;
    color: #374151;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 600;
    transition: all .2s ease;
}

.back-button:hover {
    border-color: #2563eb;
    color: #2563eb;
    box-shadow: 0 3px 12px rgba(0,0,0,.05);
}


/* Alerts */

.alert-box {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 15px 17px;
    margin-bottom: 22px;
    border-radius: 10px;
    border: 1px solid;
    position: relative;
}

.alert-danger {
    background: #fff5f5;
    border-color: #fecaca;
    color: #991b1b;
}

.alert-success {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #166534;
}

.alert-icon {
    font-size: 19px;
    padding-top: 2px;
}

.alert-content {
    flex: 1;
    font-size: 13px;
}

.alert-content strong {
    display: block;
    margin-bottom: 5px;
}

.alert-content ul {
    margin: 5px 0 0;
    padding-left: 18px;
}

.alert-close {
    border: 0;
    background: transparent;
    font-size: 22px;
    line-height: 1;
    color: inherit;
    opacity: .6;
    cursor: pointer;
}


/* Layout */

.form-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 24px;
    align-items: start;
}

.main-form {
    min-width: 0;
}


/* Cards */

.form-card,
.info-card,
.process-card,
.help-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 3px 15px rgba(15,23,42,.035);
}

.form-card {
    margin-bottom: 22px;
    overflow: hidden;
}

.section-heading {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 20px 24px;
    border-bottom: 1px solid #edf0f3;
}

.section-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.section-icon.blue {
    background: #eff6ff;
    color: #2563eb;
}

.section-icon.purple {
    background: #f5f3ff;
    color: #7c3aed;
}

.section-icon.orange {
    background: #fff7ed;
    color: #ea580c;
}

.section-heading h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}

.section-heading p {
    margin: 3px 0 0;
    color: #6b7280;
    font-size: 12px;
}

.form-body {
    padding: 24px;
}


/* Form fields */

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 21px;
}

.grid-title {
    grid-template-columns: minmax(0, 1.7fr) minmax(180px, .7fr);
}

.field-group {
    min-width: 0;
}

.field-group label {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 7px;
    color: #374151;
    font-size: 13px;
    font-weight: 650;
}

.required {
    color: #dc2626;
}

.optional {
    margin-left: auto;
    color: #9ca3af;
    font-size: 11px;
    font-weight: 500;
}

.form-input {
    width: 100%;
    min-height: 43px;
    padding: 10px 13px;
    border: 1px solid #d6dce4;
    border-radius: 8px;
    background: #fff;
    color: #1f2937;
    font-size: 13px;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    box-sizing: border-box;
}

.form-input::placeholder {
    color: #9ca3af;
}

.form-input:focus {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(37,99,235,.09);
}

.form-input.input-error {
    border-color: #dc2626;
}

.textarea {
    min-height: 190px;
    resize: vertical;
    line-height: 1.6;
}

.select-wrapper {
    position: relative;
}

.select-wrapper .form-input {
    appearance: none;
    padding-right: 38px;
}

.select-wrapper > i {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #9ca3af;
    font-size: 11px;
}

.field-help {
    margin-top: 6px;
    color: #8a94a3;
    font-size: 11px;
    line-height: 1.5;
}

.field-error {
    margin-top: 6px;
    color: #dc2626;
    font-size: 11px;
}

.field-error i {
    margin-right: 3px;
}


/* Character counter */

.textarea-footer {
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.character-count {
    margin-top: 6px;
    color: #8a94a3;
    font-size: 11px;
    white-space: nowrap;
}


/* Privacy */

.privacy-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px;
}

.privacy-option {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    padding: 17px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    cursor: pointer;
    transition: all .2s ease;
}

.privacy-option:hover {
    border-color: #bfdbfe;
    background: #fafcff;
}

.privacy-checkbox {
    position: relative;
    padding-top: 2px;
}

.privacy-checkbox input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.custom-check {
    width: 19px;
    height: 19px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid #cbd5e1;
    border-radius: 5px;
    color: transparent;
    font-size: 10px;
    transition: all .2s;
}

.privacy-checkbox input:checked + .custom-check {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.privacy-content {
    flex: 1;
}

.privacy-title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    font-weight: 700;
    color: #374151;
}

.privacy-icon {
    width: 26px;
    height: 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 7px;
}

.privacy-icon.green {
    background: #ecfdf5;
    color: #059669;
}

.privacy-icon.orange {
    background: #fff7ed;
    color: #ea580c;
}

.privacy-content p {
    margin: 6px 0 0;
    color: #7b8491;
    font-size: 11px;
    line-height: 1.55;
}

.privacy-note {
    display: flex;
    gap: 8px;
    margin-top: 14px;
    padding: 11px 13px;
    border-radius: 8px;
    background: #f8fafc;
    color: #64748b;
    font-size: 11px;
    line-height: 1.5;
}


/* Upload */

.attachment-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.upload-box label {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.upload-label {
    color: #374151;
    font-size: 13px;
    font-weight: 650;
}

.file-drop {
    min-height: 108px;
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 15px;
    border: 1.5px dashed #cbd5e1;
    border-radius: 10px;
    background: #fafbfc;
    transition: all .2s ease;
}

.file-drop:hover {
    border-color: #93c5fd;
    background: #f8fbff;
}

.file-drop input {
    display: none;
}

.upload-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    background: #eff6ff;
    color: #2563eb;
    font-size: 17px;
    flex-shrink: 0;
}

.upload-text {
    flex: 1;
    min-width: 0;
}

.upload-text strong {
    display: block;
    color: #374151;
    font-size: 12px;
}

.upload-text span {
    display: block;
    margin-top: 3px;
    color: #9ca3af;
    font-size: 10px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.browse-button {
    border: 1px solid #dbe2ea;
    border-radius: 7px;
    padding: 7px 11px;
    background: #fff;
    color: #374151;
    font-size: 11px;
    font-weight: 650;
    cursor: pointer;
}

.browse-button:hover {
    border-color: #2563eb;
    color: #2563eb;
}

.upload-help {
    margin-top: 6px;
    color: #9ca3af;
    font-size: 10px;
}


/* Side panel */

.side-panel {
    display: flex;
    flex-direction: column;
    gap: 18px;
    position: sticky;
    top: 20px;
}

.info-card {
    padding: 20px;
}

.info-card-header {
    display: flex;
    align-items: center;
    gap: 11px;
    padding-bottom: 17px;
    border-bottom: 1px solid #edf0f3;
}

.info-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    background: #eff6ff;
    color: #2563eb;
}

.info-card h3,
.process-card h3,
.help-card strong {
    margin: 0;
    color: #111827;
    font-size: 14px;
    font-weight: 700;
}

.info-card-header p {
    margin: 3px 0 0;
    color: #9ca3af;
    font-size: 10px;
}

.info-list {
    padding-top: 8px;
}

.info-item {
    display: flex;
    gap: 11px;
    padding: 12px 0;
}

.number {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #f1f5f9;
    color: #475569;
    font-size: 10px;
    font-weight: 700;
    flex-shrink: 0;
}

.info-item strong {
    display: block;
    margin-bottom: 3px;
    color: #374151;
    font-size: 12px;
}

.info-item p {
    margin: 0;
    color: #8a94a3;
    font-size: 10px;
    line-height: 1.55;
}


/* Process */

.process-card {
    padding: 20px;
}

.process-header {
    display: flex;
    align-items: center;
    gap: 9px;
    padding-bottom: 15px;
    border-bottom: 1px solid #edf0f3;
}

.process-header > i {
    color: #7c3aed;
}

.process-step {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    padding-top: 14px;
}

.step-dot {
    width: 9px;
    height: 9px;
    margin-top: 4px;
    border-radius: 50%;
    background: #93c5fd;
    flex-shrink: 0;
}

.process-step strong {
    display: block;
    color: #374151;
    font-size: 11px;
}

.process-step p {
    margin: 3px 0 0;
    color: #8a94a3;
    font-size: 10px;
    line-height: 1.45;
}

.process-line {
    width: 1px;
    height: 11px;
    margin-left: 4px;
    background: #e2e8f0;
}


/* Help */

.help-card {
    display: flex;
    gap: 12px;
    padding: 16px;
    background: #f8fafc;
}

.help-card > i {
    color: #2563eb;
    font-size: 17px;
    padding-top: 2px;
}

.help-card p {
    margin: 4px 0 0;
    color: #8a94a3;
    font-size: 10px;
    line-height: 1.55;
}


/* Bottom Actions */

.form-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 4px;
    padding-top: 4px;
}

.action-button {
    min-height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 0 17px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 650;
    cursor: pointer;
    text-decoration: none !important;
    transition: all .2s ease;
}

.cancel-button {
    border: 1px solid #dbe2ea;
    background: #fff;
    color: #4b5563;
}

.cancel-button:hover {
    background: #f8fafc;
    color: #111827;
}

.draft-button {
    border: 1px solid #dbe2ea;
    background: #fff;
    color: #4b5563;
}

.draft-button:hover {
    border-color: #94a3b8;
    background: #f8fafc;
}

.submit-button {
    border: 1px solid #2563eb;
    background: #2563eb;
    color: #fff;
    box-shadow: 0 4px 10px rgba(37,99,235,.16);
}

.submit-button:hover {
    background: #1d4ed8;
    border-color: #1d4ed8;
    transform: translateY(-1px);
}

.submit-button:disabled {
    opacity: .65;
    cursor: not-allowed;
    transform: none;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1100px) {

    .form-layout {
        grid-template-columns: 1fr;
    }

    .side-panel {
        position: static;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .help-card {
        grid-column: 1 / -1;
    }
}


@media (max-width: 768px) {

    .grievance-page {
        padding: 20px 15px 35px;
    }

    .header-content {
        align-items: flex-start;
        flex-direction: column;
    }

    .grievance-header h1 {
        font-size: 25px;
    }

    .form-grid,
    .grid-title,
    .privacy-grid,
    .attachment-grid,
    .side-panel {
        grid-template-columns: 1fr;
    }

    .form-body {
        padding: 18px;
    }

    .section-heading {
        padding: 17px 18px;
    }

    .file-drop {
        flex-wrap: wrap;
    }

    .upload-text {
        min-width: calc(100% - 60px);
    }

    .browse-button {
        width: 100%;
    }

    .textarea-footer {
        flex-direction: column;
        gap: 3px;
    }

    .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .action-button {
        width: 100%;
    }
}


@media (max-width: 480px) {

    .grievance-page {
        padding-left: 10px;
        padding-right: 10px;
    }

    .back-button span {
        display: none;
    }

    .back-button {
        width: 40px;
        height: 40px;
        padding: 0;
        justify-content: center;
    }

    .privacy-option {
        padding: 13px;
    }
}

</style>
@endpush


@push('scripts')
<script>

document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
       CHARACTER COUNTER
       ===================================================== */

    const description = document.getElementById('description');
    const charCount = document.getElementById('charCount');

    function updateCharacterCount() {

        if (!description || !charCount) {
            return;
        }

        const maxLength = 5000;

        if (description.value.length > maxLength) {
            description.value = description.value.substring(0, maxLength);
        }

        const currentLength = description.value.length;

        charCount.textContent = currentLength;

        if (currentLength >= 4900) {
            charCount.style.color = '#dc2626';
        } else if (currentLength >= 4500) {
            charCount.style.color = '#d97706';
        } else {
            charCount.style.color = '#8a94a3';
        }
    }

    if (description) {
        description.addEventListener('input', updateCharacterCount);
        updateCharacterCount();
    }


    /* =====================================================
       PRIORITY MESSAGE
       ===================================================== */

    const priority = document.getElementById('priority');
    const priorityHelp = document.getElementById('priorityHelp');

    function updatePriorityMessage() {

        if (!priority || !priorityHelp) {
            return;
        }

        switch (priority.value) {

            case 'urgent':
                priorityHelp.innerHTML =
                    '<span style="color:#dc2626;font-weight:600;">' +
                    '<i class="fas fa-exclamation-triangle"></i> ' +
                    'Urgent grievances will be prioritized.' +
                    '</span>';
                break;

            case 'high':
                priorityHelp.innerHTML =
                    '<span style="color:#d97706;font-weight:600;">' +
                    '<i class="fas fa-exclamation-circle"></i> ' +
                    'High priority issues need prompt attention.' +
                    '</span>';
                break;

            case 'medium':
                priorityHelp.innerHTML =
                    '<span>Select this for issues requiring normal attention.</span>';
                break;

            case 'low':
                priorityHelp.innerHTML =
                    '<span>Select this for general or non-urgent concerns.</span>';
                break;

            default:
                priorityHelp.innerHTML =
                    '<span>Select the appropriate urgency level.</span>';
        }
    }

    if (priority) {
        priority.addEventListener('change', updatePriorityMessage);
        updatePriorityMessage();
    }


    /* =====================================================
       SINGLE FILE
       ===================================================== */

    const attachment = document.getElementById('attachment');
    const attachmentName = document.getElementById('attachmentName');

    if (attachment) {

        attachment.addEventListener('change', function () {

            if (!attachmentName) {
                return;
            }

            if (this.files && this.files.length > 0) {
                attachmentName.textContent = this.files[0].name;
                attachmentName.style.color = '#2563eb';
            } else {
                attachmentName.textContent =
                    'PDF, DOC, DOCX, JPG or PNG';

                attachmentName.style.color = '';
            }
        });
    }


    /* =====================================================
       MULTIPLE FILES
       ===================================================== */

    const attachments = document.getElementById('attachments');
    const attachmentsName = document.getElementById('attachmentsName');

    if (attachments) {

        attachments.addEventListener('change', function () {

            if (!attachmentsName) {
                return;
            }

            const files = Array.from(this.files || []);

            if (files.length === 0) {

                attachmentsName.textContent =
                    'Select supporting documents';

                attachmentsName.style.color = '';

                return;
            }

            if (files.length === 1) {

                attachmentsName.textContent =
                    files[0].name;

            } else {

                attachmentsName.textContent =
                    files.length + ' files selected';
            }

            attachmentsName.style.color = '#2563eb';
        });
    }


    /* =====================================================
       CONFIDENTIAL / ANONYMOUS
       ===================================================== */

    const confidential =
        document.getElementById('is_confidential');

    const anonymous =
        document.getElementById('is_anonymous');

    if (anonymous && confidential) {

        anonymous.addEventListener('change', function () {

            if (this.checked) {
                confidential.checked = true;
            }

        });

    }


    /* =====================================================
       FORM VALIDATION
       ===================================================== */

    const form =
        document.getElementById('grievanceForm');

    if (form) {

        form.addEventListener('submit', function (event) {

            const title =
                document.getElementById('title');

            const descriptionField =
                document.getElementById('description');

            let valid = true;

            if (title && !title.value.trim()) {

                title.classList.add('input-error');
                title.focus();

                valid = false;
            }

            if (descriptionField &&
                !descriptionField.value.trim()) {

                descriptionField.classList.add('input-error');

                if (valid) {
                    descriptionField.focus();
                }

                valid = false;
            }

            if (!valid) {

                event.preventDefault();

                if (typeof toastr !== 'undefined') {

                    toastr.error(
                        'Please complete all required fields.'
                    );
                }

                return false;
            }


            /* Prevent double submission */

            const submitButton =
                document.getElementById('submitBtn');

            if (submitButton) {

                submitButton.disabled = true;

                submitButton.innerHTML =
                    '<i class="fas fa-spinner fa-spin"></i> ' +
                    'Submitting...';
            }

        });

    }


    /* =====================================================
       REMOVE VALIDATION ERRORS WHEN TYPING
       ===================================================== */

    ['title', 'description'].forEach(function (id) {

        const field = document.getElementById(id);

        if (field) {

            field.addEventListener('input', function () {
                this.classList.remove('input-error');
            });

        }

    });

});


/* =========================================================
   SAVE DRAFT
   ========================================================= */

function saveDraft() {

    const form =
        document.getElementById('grievanceForm');

    if (!form) {
        return;
    }


    let draftInput =
        form.querySelector('input[name="is_draft"]');

    if (!draftInput) {

        draftInput =
            document.createElement('input');

        draftInput.type = 'hidden';
        draftInput.name = 'is_draft';
        draftInput.value = '1';

        form.appendChild(draftInput);

    } else {

        draftInput.value = '1';

    }


    form.submit();
}

</script>
@endpush
