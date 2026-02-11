<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Risk Assessment Report</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 10pt;
            line-height: 1.6;
            color: #333;
        }
        .letterhead {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #1a237e;
        }
        .letterhead .institution {
            font-size: 18pt;
            font-weight: bold;
            color: #1a237e;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .letterhead .address {
            font-size: 9pt;
            color: #666;
            margin: 5px 0;
        }
        .header {
            text-align: center;
            margin: 25px 0;
        }
        .header h1 {
            margin: 0;
            font-size: 15pt;
            color: #1a237e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
        }
        .header p {
            margin: 8px 0;
            font-size: 10pt;
            color: #555;
        }
        .header strong {
            color: #1a237e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border: 1px solid #d1d5db;
        }
        th {
            background-color: #7f1d1d;
            color: white;
            padding: 10px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
            border-bottom: 2px solid #991b1b;
        }
        td {
            padding: 8px 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 8.5pt;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tbody tr:hover {
            background-color: #fef2f2;
        }
        .risk-high {
            color: #dc3545;
            font-weight: bold;
        }
        .risk-mid-high {
            color: #fd7e14;
            font-weight: bold;
        }
        .risk-mid {
            color: #ffc107;
            font-weight: bold;
        }
        .risk-low {
            color: #28a745;
            font-weight: bold;
        }
        .summary {
            margin: 25px 0;
            padding: 18px;
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #7f1d1d;
        }
        .summary strong {
            color: #7f1d1d;
            font-size: 11pt;
            display: block;
            margin-bottom: 15px;
        }
        .summary-stats {
            display: inline-block;
            width: 22%;
            margin: 1%;
            padding: 12px;
            background-color: white;
            border: 1px solid #d1d5db;
            text-align: center;
            vertical-align: top;
        }
        .summary-stats h3 {
            margin: 5px 0;
            font-size: 18pt;
            font-weight: bold;
        }
        .summary-stats p {
            margin: 5px 0;
            font-size: 8.5pt;
            color: #555;
        }
        .footer {
            margin-top: 50px;
            padding-top: 15px;
            border-top: 2px solid #1a237e;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
        .behaviors {
            font-size: 8pt;
            color: #666;
        }
        .confidential-notice {
            background-color: #fef2f2;
            border: 2px solid #dc2626;
            padding: 12px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            color: #991b1b;
            font-size: 9pt;
        }
        .signature-section {
            margin-top: 60px;
        }
        .signature-box {
            display: inline-block;
            width: 30%;
            text-align: center;
            margin: 0 1.5%;
            vertical-align: top;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 9pt;
        }
        .document-id {
            font-size: 8pt;
            color: #999;
            text-align: right;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="letterhead">
        <p class="institution">Grade Evaluation System</p>
        <p class="address">Student Counseling and Guidance Department</p>
    </div>

    <div class="confidential-notice">
        ⚠ CONFIDENTIAL DOCUMENT - Student Welfare Report
    </div>

    <div class="header">
        <h1>Student Dropout Risk Assessment Report</h1>
        @if($observations->count() > 0 && $observations->pluck('student_id')->unique()->count() == 1)
            <p><strong>Student:</strong> {{ $observations->first()->student->full_name ?? 'N/A' }}</p>
        @endif
        <p><strong>Teacher:</strong> {{ $teacher->full_name }}</p>
        <p><strong>Date Generated:</strong> {{ $date }}</p>
    </div>

    @php
        $highRisk = $observations->where('risk_status', 'High Risk')->count();
        $midHighRisk = $observations->where('risk_status', 'Mid High Risk')->count();
        $midRisk = $observations->where('risk_status', 'Mid Risk')->count();
        $lowRisk = $observations->where('risk_status', 'Low Risk')->count();
    @endphp

    <div class="summary">
        <strong>Risk Assessment Summary Statistics</strong>
        <div style="margin-top: 10px;">
            <div class="summary-stats">
                <h3 style="color: #dc3545; margin: 5px;">{{ $highRisk }}</h3>
                <p style="margin: 5px;">High Risk</p>
            </div>
            <div class="summary-stats">
                <h3 style="color: #fd7e14; margin: 5px;">{{ $midHighRisk }}</h3>
                <p style="margin: 5px;">Mid High Risk</p>
            </div>
            <div class="summary-stats">
                <h3 style="color: #ffc107; margin: 5px;">{{ $midRisk }}</h3>
                <p style="margin: 5px;">Mid Risk</p>
            </div>
            <div class="summary-stats">
                <h3 style="color: #28a745; margin: 5px;">{{ $lowRisk }}</h3>
                <p style="margin: 5px;">Low Risk</p>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Calculated Average</th>
                <th>Risk Status</th>
                <th>Referred to Counselor</th>
                <th>Observed Behaviors</th>
                <th>Date Assessed</th>
            </tr>
        </thead>
        <tbody>
            @forelse($observations as $observation)
            <tr>
                <td>{{ $observation->student->full_name ?? 'N/A' }}</td>
                <td>{{ number_format($observation->calculated_average, 2) }}%</td>
                <td class="
                    @if($observation->risk_status == 'High Risk') risk-high
                    @elseif($observation->risk_status == 'Mid High Risk') risk-mid-high
                    @elseif($observation->risk_status == 'Mid Risk') risk-mid
                    @else risk-low
                    @endif
                ">{{ $observation->risk_status }}</td>
                <td>{{ $observation->referred_to_councilor ? 'Yes' : 'No' }}</td>
                <td class="behaviors">
                    @php
                        $behaviors = $observation->observed_behaviors ?? [];
                    @endphp
                    @if(count($behaviors) > 0)
                        {{ implode(', ', array_slice($behaviors, 0, 3)) }}
                        @if(count($behaviors) > 3)
                            <br><em>+({{ count($behaviors) - 3 }} more)</em>
                        @endif
                    @else
                        None reported
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($observation->created_at)->format('M d, Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #999;">
                    No risk assessments found for the selected criteria.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                <strong>{{ $teacher->full_name }}</strong><br>
                Subject Teacher
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                Guidance Counselor<br>
                Student Welfare Office
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                Department Head<br>
                Academic Affairs
            </div>
        </div>
    </div>

    <div class="document-id">
        Document ID: RA-{{ date('YmdHis') }} | Classification: Confidential
    </div>

    <div class="footer">
        <p>This is a confidential document generated by the Grade Evaluation System</p>
        <p style="font-size: 7pt; margin-top: 5px;">Generated on {{ $date }} | For official use only</p>
    </div>
</body>
</html>
