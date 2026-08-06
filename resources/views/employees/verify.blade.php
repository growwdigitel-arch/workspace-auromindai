@extends('layouts.public')

@php
    $isInvalid = $isInvalid ?? false;
    $location = $companyAddress?->location ?: 'Chennai, Tamil Nadu, India';
    $joiningDate = $employeeDetail?->joining_date ? $employeeDetail->joining_date->format('d-m-Y') : '--';
    $companyLogo = $company?->light_logo_url ?: $company?->logo_url ?: global_setting()->logo_url;
@endphp

@push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', 'Poppins', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(34, 197, 94, 0.16), transparent 28%),
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 22%),
                linear-gradient(180deg, #f8fbff 0%, #eef4f8 100%);
        }

        .verification-shell {
            max-width: 980px;
            margin: 0 auto;
            padding: 12px 0 24px;
        }

        .verification-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 24px;
            padding: 18px 20px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.06);
            backdrop-filter: blur(18px);
        }

        .verification-header img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            border-radius: 16px;
            background: #fff;
            padding: 8px;
            box-shadow: inset 0 0 0 1px #edf2f7;
        }

        .verification-kicker {
            margin: 0 0 2px;
            font-size: 13px;
            font-weight: 600;
            color: #22c55e;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .verification-company {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: #111827;
        }

        .verification-subtitle {
            margin: 4px 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .verification-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 32px;
            padding: 28px;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(18px);
            transition: transform 0.28s ease, box-shadow 0.28s ease;
        }

        .verification-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 28px 90px rgba(15, 23, 42, 0.12);
        }

        .verification-main {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 24px;
            align-items: center;
        }

        .verification-photo {
            width: 160px;
            height: 160px;
            border-radius: 28px;
            object-fit: cover;
            background: linear-gradient(180deg, #f8fafc 0%, #e5edf5 100%);
            border: 1px solid #e6edf5;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
        }

        .verification-name {
            margin: 0 0 8px;
            color: #0f172a;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.08;
        }

        .verification-role {
            margin: 0 0 4px;
            font-size: 1rem;
            font-weight: 600;
            color: #334155;
        }

        .verification-department {
            margin: 0;
            color: #64748b;
            font-size: 15px;
        }

        .verification-meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 24px;
        }

        .verification-meta-card {
            padding: 16px 18px;
            border-radius: 20px;
            background: #f8fafc;
            border: 1px solid #e8eef5;
        }

        .verification-meta-label {
            margin-bottom: 6px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .verification-meta-value {
            color: #0f172a;
            font-size: 16px;
            font-weight: 700;
            word-break: break-word;
        }

        .verification-status {
            margin-top: 24px;
            padding: 22px;
            border-radius: 24px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .verification-status-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .verification-status-title {
            margin: 0 0 6px;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .verification-status-text {
            margin: 0;
            color: #475569;
            font-size: 15px;
            line-height: 1.6;
        }

        .verification-status.is-verified {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.18);
        }

        .verification-status.is-verified .verification-status-icon {
            background: #22c55e;
            color: #fff;
        }

        .verification-status.is-verified .verification-status-title {
            color: #15803d;
        }

        .verification-status.is-exited,
        .verification-status.is-invalid {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.18);
        }

        .verification-status.is-exited .verification-status-icon,
        .verification-status.is-invalid .verification-status-icon {
            background: #ef4444;
            color: #fff;
        }

        .verification-status.is-exited .verification-status-title,
        .verification-status.is-invalid .verification-status-title {
            color: #b91c1c;
        }

        .verification-status.is-pending {
            background: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.18);
        }

        .verification-status.is-pending .verification-status-icon {
            background: #f59e0b;
            color: #fff;
        }

        .verification-status.is-pending .verification-status-title {
            color: #b45309;
        }

        .verification-note {
            margin: 18px auto 0;
            text-align: center;
            color: #6b7280;
            font-size: 13px;
        }

        .verification-footer {
            margin-top: 24px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        @media (max-width: 767.98px) {
            .verification-header {
                padding: 16px;
                border-radius: 20px;
            }

            .verification-card {
                padding: 18px;
                border-radius: 24px;
            }

            .verification-main {
                grid-template-columns: 1fr;
            }

            .verification-photo {
                width: 120px;
                height: 120px;
                border-radius: 24px;
            }

            .verification-name {
                font-size: 1.55rem;
            }

            .verification-meta {
                grid-template-columns: 1fr;
            }

            .verification-status {
                padding: 18px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="verification-shell">
        <div class="verification-header">
            <img src="{{ $companyLogo }}" alt="{{ $verificationCompanyName }}">
            <div>
                <p class="verification-kicker">Verification</p>
                <h1 class="verification-company">{{ $verificationCompanyName }}</h1>
                <p class="verification-subtitle">{{ $verificationPortalSubtitle }}</p>
            </div>
        </div>

        <div class="verification-card">
            @if ($isInvalid)
                <div class="verification-status is-invalid mt-0">
                    <div class="verification-status-icon">
                        <i class="fa fa-times-circle"></i>
                    </div>
                    <div>
                        <h3 class="verification-status-title">Invalid Employee QR</h3>
                        <p class="verification-status-text">This QR code is not recognized by AuromindAI Technologies.</p>
                    </div>
                </div>
            @else
                <div class="verification-main">
                    <img src="{{ $employee->image_url }}" alt="{{ $employee->name }}" class="verification-photo">

                    <div>
                        <h2 class="verification-name">{{ $employee->name }}</h2>
                        <p class="verification-role">{{ $employeeDetail?->designation?->name ?? 'Employee' }}</p>
                        <p class="verification-department">{{ $employeeDetail?->department?->team_name ?? 'Operations' }}</p>

                        <div class="verification-meta">
                            <div class="verification-meta-card">
                                <div class="verification-meta-label">Employee ID</div>
                                <div class="verification-meta-value">{{ $employeeDetail?->employee_id ?? request('eid') }}</div>
                            </div>
                            <div class="verification-meta-card">
                                <div class="verification-meta-label">Joining Date</div>
                                <div class="verification-meta-value">{{ $joiningDate }}</div>
                            </div>
                            <div class="verification-meta-card">
                                <div class="verification-meta-label">Location</div>
                                <div class="verification-meta-value">{{ $location }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="verification-status {{ $verificationStatus['class'] }}">
                    <div class="verification-status-icon">
                        <i class="fa fa-{{ $verificationStatus['icon'] }}"></i>
                    </div>
                    <div>
                        <h3 class="verification-status-title">{{ $verificationStatus['label'] }}</h3>
                        <p class="verification-status-text">{{ $verificationStatus['message'] }}</p>
                    </div>
                </div>
            @endif
        </div>

        <p class="verification-note">
            @if ($isInvalid)
                This page is generated from AuromindAI employee identification system.
            @else
                This page is generated from AuromindAI employee identification system.
            @endif
        </p>

        <div class="verification-footer">
            <div>{{ $verificationCompanyName }}</div>
            <div>Chennai, Tamil Nadu, India</div>
        </div>
    </div>
@endsection
