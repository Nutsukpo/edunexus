@extends('students.layouts.app')

@section('title', 'Class History - EduNexus')

@section('content')

<style>
    .class-history-page {
        --nx-primary: #1a56db;
        --nx-primary-dark: #1e40af;
        --nx-primary-light: #dbeafe;
        --nx-primary-soft: #eff6ff;
        --nx-secondary: #3b82f6;
        --nx-success: #059669;
        --nx-success-soft: #ecfdf5;
        --nx-info: #2563eb;
        --nx-info-soft: #eff6ff;
        --nx-warning: #d97706;
        --nx-warning-soft: #fffbeb;
        --nx-purple: #7c3aed;
        --nx-purple-soft: #f5f3ff;
        --nx-text: #0f172a;
        --nx-muted: #64748b;
        --nx-border: #e2e8f0;
        --nx-bg: #f1f5f9;
        --nx-shadow: rgba(30, 64, 175, 0.10);
        --nx-shadow-hover: rgba(30, 64, 175, 0.18);
    }

    /* ---------------------------------------------------------
       PAGE
    --------------------------------------------------------- */

    .class-history-page {
        color: var(--nx-text);
        padding-bottom: 30px;
    }

    /* ---------------------------------------------------------
       HERO
    --------------------------------------------------------- */

    .history-hero {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        padding: 35px;
        margin-bottom: 28px;
        color: #fff;
        background:
            radial-gradient(circle at 85% 15%, rgba(255,255,255,.12), transparent 30%),
            radial-gradient(circle at 10% 100%, rgba(255,255,255,.08), transparent 35%),
            linear-gradient(135deg, #1e3a5f 0%, #1a56db 50%, #3b82f6 100%);
        box-shadow: 0 12px 40px rgba(26, 86, 219, .25);
        transition: all 0.3s ease;
    }

    .history-hero::before,
    .history-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }

    .history-hero::before {
        width: 300px;
        height: 300px;
        right: -100px;
        top: -150px;
        border: 50px solid rgba(255,255,255,.04);
    }

    .history-hero::after {
        width: 200px;
        height: 200px;
        left: -100px;
        bottom: -120px;
        border: 40px solid rgba(255,255,255,.04);
    }

    .history-hero-content {
        position: relative;
        z-index: 2;
    }

    .history-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .15em;
        color: rgba(255,255,255,.7);
        margin-bottom: 10px;
        background: rgba(255,255,255,.10);
        padding: 6px 14px;
        border-radius: 50px;
        backdrop-filter: blur(8px);
    }

    .history-hero h1 {
        margin: 0 0 10px;
        font-size: 2.1rem;
        font-weight: 800;
        letter-spacing: -.02em;
        text-shadow: 0 2px 4px rgba(0,0,0,.10);
    }

    .history-hero-description {
        margin: 0;
        color: rgba(255,255,255,.85);
        font-size: .95rem;
        max-width: 650px;
        line-height: 1.6;
    }

    .student-identity {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-top: 25px;
        padding: 12px 18px;
        background: rgba(255,255,255,.10);
        border-radius: 14px;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,.10);
        max-width: 450px;
    }

    .student-avatar {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(255,255,255,.15);
        border: 2px solid rgba(255,255,255,.20);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: #fff;
        flex-shrink: 0;
    }

    .student-identity-name {
        font-size: 1rem;
        font-weight: 700;
    }

    .student-identity-meta {
        margin-top: 2px;
        color: rgba(255,255,255,.7);
        font-size: .8rem;
    }

    .student-identity-meta strong {
        color: #fff;
        font-weight: 600;
    }

    .hero-date {
        position: relative;
        z-index: 2;
        min-width: 160px;
        padding: 14px 18px;
        border-radius: 14px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.12);
        text-align: center;
        backdrop-filter: blur(8px);
    }

    .hero-date i {
        display: block;
        margin-bottom: 6px;
        color: #93c5fd;
        font-size: 1.1rem;
    }

    .hero-date-label {
        display: block;
        color: rgba(255,255,255,.5);
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .1em;
    }

    .hero-date-value {
        display: block;
        margin-top: 3px;
        font-size: .8rem;
        font-weight: 600;
        color: #fff;
    }

    /* ---------------------------------------------------------
       STAT CARDS
    --------------------------------------------------------- */

    .history-stat {
        height: 100%;
        background: #fff;
        border: 1px solid var(--nx-border);
        border-radius: 18px;
        padding: 24px 22px;
        transition: all .25s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
        position: relative;
        overflow: hidden;
    }

    .history-stat::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--nx-primary), var(--nx-secondary));
        opacity: 0;
        transition: opacity .3s ease;
    }

    .history-stat:hover::before {
        opacity: 1;
    }

    .history-stat:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px var(--nx-shadow-hover);
        border-color: var(--nx-secondary);
    }

    .history-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .history-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: transform .3s ease;
    }

    .history-stat:hover .history-stat-icon {
        transform: scale(1.05) rotate(-3deg);
    }

    .history-stat-icon.blue {
        color: var(--nx-primary);
        background: var(--nx-primary-soft);
    }

    .history-stat-icon.green {
        color: var(--nx-success);
        background: var(--nx-success-soft);
    }

    .history-stat-icon.indigo {
        color: var(--nx-info);
        background: var(--nx-info-soft);
    }

    .history-stat-icon.purple {
        color: var(--nx-purple);
        background: var(--nx-purple-soft);
    }

    .history-stat-number {
        margin-top: 18px;
        font-size: 2rem;
        line-height: 1;
        font-weight: 800;
        letter-spacing: -.03em;
        color: var(--nx-text);
    }

    .history-stat-label {
        margin-top: 8px;
        color: var(--nx-muted);
        font-size: .8rem;
        font-weight: 600;
    }

    .history-stat-description {
        margin-top: 6px;
        color: #94a3b8;
        font-size: .7rem;
        line-height: 1.4;
    }

    /* ---------------------------------------------------------
       MAIN PANEL
    --------------------------------------------------------- */

    .history-panel {
        background: #fff;
        border: 1px solid var(--nx-border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0,0,0,.04);
    }

    .history-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 22px 26px;
        border-bottom: 2px solid var(--nx-border);
        background: var(--nx-bg);
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .panel-title-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: var(--nx-primary-soft);
        color: var(--nx-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .panel-title h2 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 750;
        color: var(--nx-text);
    }

    .panel-title p {
        margin: 2px 0 0;
        color: var(--nx-muted);
        font-size: .75rem;
    }

    .history-actions {
        display: flex;
        gap: 10px;
    }

    .history-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 10px;
        padding: 9px 16px;
        font-size: .76rem;
        font-weight: 650;
        border: 1px solid var(--nx-border);
        background: #fff;
        color: #475569;
        transition: all .2s ease;
        cursor: pointer;
        text-decoration: none;
    }

    .history-btn:hover {
        border-color: var(--nx-primary);
        background: var(--nx-primary-soft);
        color: var(--nx-primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 86, 219, .12);
    }

    .history-btn.primary {
        color: #fff;
        border-color: var(--nx-primary);
        background: var(--nx-primary);
        box-shadow: 0 4px 12px rgba(26, 86, 219, .20);
    }

    .history-btn.primary:hover {
        background: var(--nx-primary-dark);
        border-color: var(--nx-primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(26, 86, 219, .30);
        color: #fff;
    }

    .history-btn i {
        font-size: .85rem;
    }

    /* ---------------------------------------------------------
       TIMELINE
    --------------------------------------------------------- */

    .history-list {
        position: relative;
        padding: 15px 26px 20px;
    }

    .history-list::before {
        content: "";
        position: absolute;
        left: 47px;
        top: 40px;
        bottom: 42px;
        width: 3px;
        background: linear-gradient(180deg, var(--nx-primary-light), var(--nx-border));
        border-radius: 4px;
    }

    .history-item {
        position: relative;
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr) auto;
        gap: 18px;
        padding: 18px 0;
        border-bottom: 1px solid #f1f5f9;
        transition: all .3s ease;
    }

    .history-item:last-child {
        border-bottom: 0;
    }

    .history-item:hover {
        background: var(--nx-primary-soft);
        margin: 0 -12px;
        padding-left: 12px;
        padding-right: 12px;
        border-radius: 12px;
    }

    .history-node {
        position: relative;
        z-index: 2;
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px var(--nx-border);
        background: #f8fafc;
        color: #64748b;
        transition: all .3s ease;
    }

    .history-item:hover .history-node {
        transform: scale(1.05);
    }

    .history-item.current .history-node {
        background: var(--nx-primary);
        color: #fff;
        box-shadow: 0 0 0 2px var(--nx-primary), 0 6px 20px rgba(26,86,219,.25);
    }

    .history-item.graduated .history-node {
        background: var(--nx-success);
        color: #fff;
        box-shadow: 0 0 0 2px var(--nx-success);
    }

    .history-item.promoted .history-node {
        background: var(--nx-info);
        color: #fff;
        box-shadow: 0 0 0 2px var(--nx-info);
    }

    .history-item.repeated .history-node {
        background: var(--nx-warning);
        color: #fff;
        box-shadow: 0 0 0 2px var(--nx-warning);
    }

    .history-content {
        min-width: 0;
        padding-top: 2px;
    }

    .history-class-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .history-class-name {
        font-size: .95rem;
        font-weight: 750;
        color: var(--nx-text);
    }

    .history-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: .65rem;
        font-weight: 700;
        line-height: 1.4;
        letter-spacing: .02em;
    }

    .history-status.current {
        color: var(--nx-primary);
        background: var(--nx-primary-soft);
    }

    .history-status.completed {
        color: #64748b;
        background: #f1f5f9;
    }

    .history-status.graduated {
        color: var(--nx-success);
        background: var(--nx-success-soft);
    }

    .history-status.promoted {
        color: var(--nx-info);
        background: var(--nx-info-soft);
    }

    .history-status.repeated {
        color: var(--nx-warning);
        background: var(--nx-warning-soft);
    }

    .history-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 20px;
        margin-top: 8px;
    }

    .history-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--nx-muted);
        font-size: .72rem;
    }

    .history-meta-item i {
        width: 14px;
        text-align: center;
        color: #94a3b8;
    }

    .history-note {
        margin-top: 8px;
        color: #94a3b8;
        font-size: .7rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .history-note i {
        font-size: .5rem;
    }

    .history-date {
        min-width: 110px;
        text-align: right;
        padding-top: 3px;
    }

    .history-date-main {
        color: #475569;
        font-size: .72rem;
        font-weight: 650;
    }

    .history-date-sub {
        margin-top: 3px;
        color: #94a3b8;
        font-size: .64rem;
    }

    /* ---------------------------------------------------------
       EMPTY STATE
    --------------------------------------------------------- */

    .history-empty {
        padding: 70px 25px;
        text-align: center;
    }

    .history-empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--nx-primary-soft);
        color: var(--nx-primary);
        font-size: 2rem;
        transition: all .3s ease;
    }

    .history-empty-icon:hover {
        transform: scale(1.05) rotate(-5deg);
        box-shadow: 0 8px 24px rgba(26,86,219,.15);
    }

    .history-empty h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 750;
        color: var(--nx-text);
    }

    .history-empty p {
        max-width: 430px;
        margin: 10px auto 22px;
        color: var(--nx-muted);
        font-size: .8rem;
        line-height: 1.6;
    }

    /* ---------------------------------------------------------
       INFORMATION CARDS
    --------------------------------------------------------- */

    .info-card {
        height: 100%;
        background: #fff;
        border: 1px solid var(--nx-border);
        border-radius: 18px;
        padding: 24px;
        transition: all .3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
    }

    .info-card:hover {
        border-color: var(--nx-secondary);
        box-shadow: 0 8px 24px var(--nx-shadow-hover);
        transform: translateY(-2px);
    }

    .info-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        font-size: .85rem;
        font-weight: 750;
        color: var(--nx-text);
    }

    .info-card-title i {
        color: var(--nx-primary);
        font-size: 1rem;
    }

    .distribution {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .distribution-item {
        flex: 1;
        text-align: center;
        padding: 15px 10px;
        border-radius: 14px;
        background: var(--nx-bg);
        transition: all .3s ease;
        border: 1px solid transparent;
    }

    .distribution-item:hover {
        background: var(--nx-primary-soft);
        border-color: var(--nx-primary-light);
        transform: scale(1.02);
    }

    .distribution-number {
        display: block;
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--nx-text);
    }

    .distribution-number.text-primary {
        color: var(--nx-primary);
    }

    .distribution-number.text-secondary {
        color: #64748b;
    }

    .distribution-number.text-success {
        color: var(--nx-success);
    }

    .distribution-label {
        display: block;
        margin-top: 4px;
        color: var(--nx-muted);
        font-size: .66rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .tips-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .tips-list li {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 12px;
        color: var(--nx-muted);
        font-size: .72rem;
        line-height: 1.5;
        padding: 8px 12px;
        border-radius: 10px;
        transition: all .3s ease;
    }

    .tips-list li:hover {
        background: var(--nx-primary-soft);
    }

    .tips-list li:last-child {
        margin-bottom: 0;
    }

    .tips-list i {
        margin-top: 2px;
        color: var(--nx-primary);
        font-size: .85rem;
        flex-shrink: 0;
    }

    .tips-list li strong {
        color: var(--nx-text);
        font-weight: 600;
    }

    /* ---------------------------------------------------------
       PAGINATION
    --------------------------------------------------------- */

    .history-pagination {
        padding: 16px 26px;
        border-top: 1px solid var(--nx-border);
        background: var(--nx-bg);
    }

    .history-pagination .pagination {
        margin: 0;
        justify-content: center;
        gap: 4px;
    }

    .history-pagination .page-link {
        border-radius: 8px;
        border: 1px solid var(--nx-border);
        color: var(--nx-text);
        padding: 6px 14px;
        font-size: .8rem;
        transition: all .3s ease;
        background: #fff;
    }

    .history-pagination .page-link:hover {
        background: var(--nx-primary-soft);
        border-color: var(--nx-primary);
        color: var(--nx-primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26,86,219,.12);
    }

    .history-pagination .page-item.active .page-link {
        background: var(--nx-primary);
        border-color: var(--nx-primary);
        color: #fff;
        box-shadow: 0 4px 12px rgba(26,86,219,.25);
    }

    /* ---------------------------------------------------------
       RESPONSIVE
    --------------------------------------------------------- */

    @media (max-width: 768px) {

        .history-hero {
            padding: 24px;
            border-radius: 16px;
        }

        .history-hero h1 {
            font-size: 1.6rem;
        }

        .history-hero-description {
            font-size: .85rem;
        }

        .hero-date {
            margin-top: 18px;
            width: 100%;
            min-width: unset;
        }

        .history-panel-header {
            align-items: flex-start;
            flex-direction: column;
            padding: 18px 20px;
        }

        .history-actions {
            width: 100%;
            flex-wrap: wrap;
        }

        .history-btn {
            flex: 1;
            min-width: 120px;
        }

        .history-list {
            padding-left: 16px;
            padding-right: 16px;
        }

        .history-list::before {
            left: 38px;
        }

        .history-item {
            grid-template-columns: 40px minmax(0, 1fr);
            gap: 14px;
            padding: 14px 0;
        }

        .history-node {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            font-size: .85rem;
        }

        .history-date {
            grid-column: 2;
            text-align: left;
            padding-top: 0;
            min-width: 0;
        }

        .history-meta {
            gap: 6px 14px;
        }

        .student-identity {
            max-width: 100%;
        }

        .history-stat-number {
            font-size: 1.7rem;
        }
    }

    @media (max-width: 576px) {

        .history-hero {
            border-radius: 14px;
            padding: 20px;
        }

        .history-hero h1 {
            font-size: 1.4rem;
        }

        .student-identity {
            flex-direction: column;
            align-items: flex-start;
            padding: 12px 16px;
        }

        .student-avatar {
            width: 44px;
            height: 44px;
        }

        .history-stat {
            padding: 18px 16px;
        }

        .history-stat-number {
            font-size: 1.5rem;
        }

        .distribution {
            flex-direction: column;
        }

        .distribution-item {
            width: 100%;
        }

        .history-btn {
            min-width: 80px;
            font-size: .7rem;
            padding: 7px 12px;
        }

        .history-panel-header {
            padding: 14px 16px;
        }

        .panel-title h2 {
            font-size: .9rem;
        }

        .history-item:hover {
            margin: 0 -8px;
            padding-left: 8px;
            padding-right: 8px;
        }
    }

    /* ---------------------------------------------------------
       PRINT
    --------------------------------------------------------- */

    @media print {

        body {
            background: #fff !important;
        }

        .history-hero {
            color: #000 !important;
            background: #f8fafc !important;
            border: 2px solid #e2e8f0;
            box-shadow: none;
            padding: 20px;
        }

        .history-hero h1 {
            color: #0f172a !important;
        }

        .history-hero-description,
        .student-identity-meta,
        .history-eyebrow,
        .hero-date-label {
            color: #475569 !important;
        }

        .history-eyebrow {
            background: #f1f5f9 !important;
            color: #475569 !important;
        }

        .student-identity {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0;
        }

        .hero-date {
            border: 1px solid #e2e8f0;
            background: #f8fafc !important;
        }

        .hero-date-value {
            color: #0f172a !important;
        }

        .history-actions,
        .history-btn,
        .tips-list {
            display: none !important;
        }

        .history-panel,
        .history-stat,
        .info-card {
            box-shadow: none !important;
            border: 1px solid #e2e8f0 !important;
        }

        .history-stat::before {
            display: none !important;
        }

        .history-list::before {
            background: #e2e8f0 !important;
        }

        .history-item {
            page-break-inside: avoid;
            border-bottom-color: #f1f5f9 !important;
        }

        .history-item:hover {
            background: transparent !important;
            margin: 0 !important;
            padding: 18px 0 !important;
        }

        .history-pagination {
            display: none !important;
        }

        .history-stat-icon {
            background: #f1f5f9 !important;
            color: #475569 !important;
        }

        .distribution-item {
            background: #f8fafc !important;
        }

        .history-status.current {
            background: #dbeafe !important;
            color: #1a56db !important;
        }

        .history-status.graduated {
            background: #d1fae5 !important;
            color: #059669 !important;
        }
    }

    /* ---------------------------------------------------------
       ANIMATIONS
    --------------------------------------------------------- */

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .history-stat {
        animation: fadeInUp .5s ease forwards;
    }

    .history-stat:nth-child(1) { animation-delay: 0.05s; }
    .history-stat:nth-child(2) { animation-delay: 0.10s; }
    .history-stat:nth-child(3) { animation-delay: 0.15s; }
    .history-stat:nth-child(4) { animation-delay: 0.20s; }

    .history-item {
        animation: fadeInUp .4s ease forwards;
        opacity: 0;
    }

    .history-item:nth-child(1) { animation-delay: 0.05s; }
    .history-item:nth-child(2) { animation-delay: 0.10s; }
    .history-item:nth-child(3) { animation-delay: 0.15s; }
    .history-item:nth-child(4) { animation-delay: 0.20s; }
    .history-item:nth-child(5) { animation-delay: 0.25s; }
    .history-item:nth-child(6) { animation-delay: 0.30s; }
    .history-item:nth-child(7) { animation-delay: 0.35s; }
    .history-item:nth-child(8) { animation-delay: 0.40s; }
    .history-item:nth-child(9) { animation-delay: 0.45s; }
    .history-item:nth-child(10) { animation-delay: 0.50s; }
}
</style>

<div class="class-history-page">

@php
    /*
    |--------------------------------------------------------------------------
    | Safe Student Information
    |--------------------------------------------------------------------------
    */

    $studentName = $student->full_name
        ?? trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))
        ?: 'Student';

    /*
    |--------------------------------------------------------------------------
    | Determine Current Class From Assignment History
    |--------------------------------------------------------------------------
    */

    $currentAssignment = collect($classHistory ?? [])->first(function ($item) {
        return (bool) ($item->is_current ?? false);
    });

    $currentClassName = $currentAssignment?->studentClass?->name
        ?? 'Not Assigned';

    /*
    |--------------------------------------------------------------------------
    | Summary Values
    |--------------------------------------------------------------------------
    */

    $totalClasses = $summary['total_classes'] ?? 0;
    $currentClasses = $summary['current_class'] ?? 0;
    $completedClasses = $summary['completed_classes'] ?? 0;
    $totalYears = $summary['total_years'] ?? 0;
@endphp

{{-- =========================================================
     HERO
========================================================== --}}

<div class="history-hero">
    <div class="history-hero-content">

        <div class="d-flex justify-content-between align-items-start flex-wrap">

            <div class="flex-grow-1">

                <div class="history-eyebrow">
                    <i class="fas fa-graduation-cap"></i>
                    Student Academic Record
                </div>

                <h1>My Class History</h1>

                <p class="history-hero-description">
                    View your academic progression and the classes you have been assigned to throughout your journey at EduNexus.
                </p>

                <div class="student-identity">

                    <div class="student-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>

                    <div>
                        <div class="student-identity-name">
                            {{ $studentName }}
                        </div>

                        <!-- <div class="student-identity-meta">
                            Current Class:
                            <strong>{{ $currentClassName }}</strong>
                        </div> -->
                    </div>

                </div>

            </div>

            <div class="hero-date">

                <i class="far fa-calendar-alt"></i>

                <span class="hero-date-label">
                    Today
                </span>

                <span class="hero-date-value">
                    {{ now()->format('D, M d, Y') }}
                </span>

            </div>

        </div>

    </div>
</div>


{{-- =========================================================
     SUMMARY
========================================================== --}}

<div class="row g-3 mb-4">

    <div class="col-xl-3 col-md-6">

        <div class="history-stat">

            <div class="history-stat-top">

                <div class="history-stat-icon blue">
                    <i class="fas fa-school"></i>
                </div>

            </div>

            <div class="history-stat-number">
                {{ $totalClasses }}
            </div>

            <div class="history-stat-label">
                Total Classes
            </div>

            <div class="history-stat-description">
                Classes recorded in your academic history
            </div>

        </div>

    </div>


    <div class="col-xl-3 col-md-6">

        <div class="history-stat">

            <div class="history-stat-top">

                <div class="history-stat-icon indigo">
                    <i class="fas fa-star"></i>
                </div>

            </div>

            <div class="history-stat-number">
                {{ $currentClasses }}
            </div>

            <div class="history-stat-label">
                Current Class
            </div>

            <div class="history-stat-description">
                Your active class assignment
            </div>

        </div>

    </div>


    <div class="col-xl-3 col-md-6">

        <div class="history-stat">

            <div class="history-stat-top">

                <div class="history-stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>

            </div>

            <div class="history-stat-number">
                {{ $completedClasses }}
            </div>

            <div class="history-stat-label">
                Completed Classes
            </div>

            <div class="history-stat-description">
                Previous class assignments completed
            </div>

        </div>

    </div>


    <div class="col-xl-3 col-md-6">

        <div class="history-stat">

            <div class="history-stat-top">

                <div class="history-stat-icon purple">
                    <i class="fas fa-calendar-alt"></i>
                </div>

            </div>

            <div class="history-stat-number">
                {{ $totalYears }}
            </div>

            <div class="history-stat-label">
                Academic Years
            </div>

            <div class="history-stat-description">
                Academic years represented in your record
            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     CLASS HISTORY
========================================================== --}}

<div class="history-panel mb-4">

    <div class="history-panel-header">

        <div class="panel-title">

            <div class="panel-title-icon">
                <i class="fas fa-route"></i>
            </div>

            <div>

                <h2>Academic Journey</h2>

                <p>
                    Your class progression from earliest to current assignment
                </p>

            </div>

        </div>


        <div class="history-actions">

            <button
                type="button"
                class="history-btn"
                id="printClassHistory"
            >
                <i class="fas fa-print"></i>
                Print
            </button>

            <button
                type="button"
                class="history-btn primary"
                id="exportClassHistory"
            >
                <i class="fas fa-file-pdf"></i>
                Save PDF
            </button>

        </div>

    </div>


    @if(isset($classHistory) && $classHistory->count())

        <div class="history-list">

            @foreach($classHistory as $assignment)

                @php

                    /*
                    |--------------------------------------------------------------------------
                    | Determine Assignment Status
                    |--------------------------------------------------------------------------
                    */

                    $assignmentStatus = strtolower(
                        (string) ($assignment->status ?? '')
                    );

                    if ($assignment->is_current ?? false) {

                        $statusClass = 'current';
                        $statusLabel = 'Current';
                        $statusIcon = 'fa-star';

                    } elseif ($assignmentStatus === 'graduated') {

                        $statusClass = 'graduated';
                        $statusLabel = 'Graduated';
                        $statusIcon = 'fa-graduation-cap';

                    } elseif ($assignmentStatus === 'promoted') {

                        $statusClass = 'promoted';
                        $statusLabel = 'Promoted';
                        $statusIcon = 'fa-arrow-up';

                    } elseif ($assignmentStatus === 'repeated') {

                        $statusClass = 'repeated';
                        $statusLabel = 'Repeated';
                        $statusIcon = 'fa-redo';

                    } else {

                        $statusClass = 'completed';
                        $statusLabel = 'Completed';
                        $statusIcon = 'fa-check';

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Safe Dates
                    |--------------------------------------------------------------------------
                    */

                    $startDate = $assignment->start_date ?? $assignment->assigned_date ?? $assignment->created_at ?? null;
                    $endDate = $assignment->end_date ?? $assignment->promotion_date ?? null;

                    $startDateText = 'Date unavailable';

                    if ($startDate) {
                        try {
                            $startDateText = \Carbon\Carbon::parse($startDate)->format('M d, Y');
                        } catch (\Throwable $e) {
                            $startDateText = 'Invalid date';
                        }
                    }

                    $durationText = null;

                    if ($startDate && $endDate) {
                        try {
                            $duration = \Carbon\Carbon::parse($startDate)
                                ->diffInDays(\Carbon\Carbon::parse($endDate));
                            $durationText = $duration . ' ' . \Illuminate\Support\Str::plural('day', $duration);
                        } catch (\Throwable $e) {
                            $durationText = null;
                        }
                    }

                @endphp


                <div class="history-item {{ $statusClass }}">

                    {{-- Timeline Node --}}
                    <div class="history-node">
                        <i class="fas {{ $statusIcon }}"></i>
                    </div>


                    {{-- Main Information --}}
                    <div class="history-content">

                        <div class="history-class-row">

                            <span class="history-class-name">
                                {{ $assignment->studentClass?->name ?? 'Class Not Available' }}
                            </span>

                            <span class="history-status {{ $statusClass }}">
                                <i class="fas {{ $statusIcon }}"></i>
                                {{ $statusLabel }}
                            </span>

                        </div>


                        <div class="history-meta">

                            <span class="history-meta-item">

                                <i class="fas fa-calendar-alt"></i>

                                {{ $assignment->academicYear?->name ?? 'Academic year unavailable' }}

                            </span>


                            @if(!empty($assignment->studentClass?->class_code))

                                <span class="history-meta-item">

                                    <i class="fas fa-hashtag"></i>

                                    {{ $assignment->studentClass->class_code }}

                                </span>

                            @endif


                            @if($assignment->assigned_date)

                                <span class="history-meta-item">

                                    <i class="fas fa-sign-in-alt"></i>

                                    Assigned
                                    {{ \Carbon\Carbon::parse($assignment->assigned_date)->format('M d, Y') }}

                                </span>

                            @endif

                        </div>


                        <div class="history-note">

                            @if($assignment->is_current ?? false)

                                <i class="fas fa-circle text-primary"
                                   style="font-size:.5rem; color: var(--nx-primary);"></i>

                                You are currently enrolled in this class.

                            @elseif($assignment->promotion_date)

                                <i class="fas fa-arrow-right"></i>

                                Class assignment ended on
                                {{ \Carbon\Carbon::parse($assignment->promotion_date)->format('M d, Y') }}.

                            @elseif($assignment->end_date)

                                <i class="fas fa-calendar-check"></i>

                                Completed on
                                {{ \Carbon\Carbon::parse($assignment->end_date)->format('M d, Y') }}.

                            @else

                                <i class="fas fa-info-circle"></i>

                                Historical class assignment.

                            @endif

                        </div>

                    </div>


                    {{-- Date --}}
                    <div class="history-date">

                        <div class="history-date-main">
                            {{ $startDateText }}
                        </div>

                        @if($durationText)

                            <div class="history-date-sub">
                                {{ $durationText }}
                            </div>

                        @elseif($assignment->is_current ?? false)

                            <div class="history-date-sub">
                                Current
                            </div>

                        @endif

                    </div>

                </div>

            @endforeach

        </div>


        {{-- Pagination --}}

        @if($classHistory->hasPages())

            <div class="history-pagination">

                {{ $classHistory->links() }}

            </div>

        @endif

    @else

        <div class="history-empty">

            <div class="history-empty-icon">
                <i class="fas fa-history"></i>
            </div>

            <h3>No Class History Yet</h3>

            <p>
                Your class history will appear here once a class assignment
                has been created for your student record.
            </p>

            <a
                href="{{ route('students.dashboard') }}"
                class="history-btn primary"
            >
                <i class="fas fa-home"></i>
                Back to Dashboard
            </a>

        </div>

    @endif

</div>


{{-- =========================================================
     ADDITIONAL INFORMATION
========================================================== --}}

@if(isset($classHistory) && $classHistory->count())

    <div class="row g-3">

        {{-- Distribution --}}
        <div class="col-lg-6">

            <div class="info-card">

                <div class="info-card-title">

                    <i class="fas fa-chart-pie"></i>

                    Class Distribution

                </div>


                <div class="distribution">

                    <div class="distribution-item">

                        <span class="distribution-number text-primary">
                            {{ $currentClasses }}
                        </span>

                        <span class="distribution-label">
                            Current
                        </span>

                    </div>


                    <div class="distribution-item">

                        <span class="distribution-number text-secondary">
                            {{ $completedClasses }}
                        </span>

                        <span class="distribution-label">
                            Completed
                        </span>

                    </div>


                    <div class="distribution-item">

                        <span class="distribution-number text-primary">
                            {{ $totalClasses }}
                        </span>

                        <span class="distribution-label">
                            Total
                        </span>

                    </div>

                </div>

            </div>

        </div>


        {{-- Tips --}}
        <div class="col-lg-6">

            <div class="info-card">

                <div class="info-card-title">

                    <i class="fas fa-lightbulb"></i>

                    About Your Class History

                </div>


                <ul class="tips-list">

                    <li>

                        <i class="fas fa-check-circle"></i>

                        <span>
                            Your class history records your academic progression
                            across different school years.
                        </span>

                    </li>

                    <li>

                        <i class="fas fa-star"></i>

                        <span>
                            The class marked <strong>Current</strong> is your
                            active class assignment.
                        </span>

                    </li>

                    <li>

                        <i class="fas fa-calendar-alt"></i>

                        <span>
                            Academic years help organize your class progression
                            over time.
                        </span>

                    </li>

                    <li>

                        <i class="fas fa-file-pdf"></i>

                        <span>
                            Use the Print or Save PDF option when you need a
                            copy of your academic history.
                        </span>

                    </li>

                </ul>

            </div>

        </div>

    </div>

@endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

    const printButton = document.getElementById('printClassHistory');

    if (printButton) {

        printButton.addEventListener('click', function () {

            window.print();

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Save PDF
    |--------------------------------------------------------------------------
    |
    | Uses the browser's native print dialog.
    | The user can select "Save as PDF".
    |
    */

    const exportButton = document.getElementById('exportClassHistory');

    if (exportButton) {

        exportButton.addEventListener('click', function () {

            window.print();

        });

    }

});
</script>

@endsection