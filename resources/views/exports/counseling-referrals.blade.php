<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Counseling Referrals</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
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
            font-size: 16pt;
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
            background-color: #1a237e;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #0d47a1;
            font-size: 9pt;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9pt;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tbody tr:hover {
            background-color: #f3f4f6;
        }
        .footer {
            margin-top: 50px;
            padding-top: 15px;
            border-top: 2px solid #1a237e;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
        .summary {
            margin: 25px 0;
            padding: 15px;
            background-color: #f9fafb;
            border-left: 4px solid #1a237e;
            border: 1px solid #d1d5db;
        }
        .summary strong {
            color: #1a237e;
            font-size: 11pt;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-info {
            background-color: #17a2b8;
            color: white;
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
        <p class="address">Guidance & Counseling Department</p>
    </div>

    <div class="header">
        <h1>Student Counseling Referrals</h1>
        <p><strong>Counselor:</strong> {{ $counselor->full_name }}</p>
        <p><strong>Date Generated:</strong> {{ $date }}</p>
        
        @if(!empty($filters['search']) || !empty($filters['teacher']) || !empty($filters['status']))
            <p style="font-size: 9pt; color: #666;">
                <strong>Filters Applied:</strong>
                @if(!empty($filters['search']))
                    Search: "{{ $filters['search'] }}"
                @endif
                @if(!empty($filters['teacher']))
                    | Teacher ID: {{ $filters['teacher'] }}
                @endif
                @if(!empty($filters['status']))
                    | Status: {{ ucfirst($filters['status']) }}
                @endif
            </p>
        @endif
    </div>

    <div class="summary">
        <strong>Total Referrals:</strong> {{ $referrals->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 11%;">Type</th>
                <th style="width: 16%;">Student</th>
                <th style="width: 9%;">Section</th>
                <th style="width: 12%;">Subject</th>
                <th style="width: 13%;">Referred By</th>
                <th style="width: 20%;">Reason/Risk Level</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 7%;">Schedule</th>
            </tr>
        </thead>
        <tbody>
            @php $index = 1; @endphp
            
            {{-- All referrals in merged alphabetical order --}}
            @foreach($referrals as $item)
            <tr>
                <td>{{ $index++ }}</td>
                <td>
                    @if($item->referral_type === 'risk_assessment')
                        <strong>Risk Assessment</strong>
                    @else
                        <strong>Referral</strong>
                    @endif
                </td>
                <td>{{ $item->student->last_name }} {{ $item->student->first_name }} {{ $item->student->middle_name }}</td>
                <td>{{ $item->student->section ?? 'N/A' }}</td>
                <td>{{ $item->student->subject ?? 'N/A' }}</td>
                <td>{{ $item->teacher->full_name ?? 'N/A' }}</td>
                <td>
                    @if($item->referral_type === 'risk_assessment')
                        <span class="badge {{ $item->risk_status === 'High Risk' ? 'badge-danger' : 'badge-warning' }}">
                            {{ $item->risk_status }}
                        </span>
                        @php
                            $behaviors = is_array($item->observed_behaviors) 
                                ? $item->observed_behaviors 
                                : json_decode($item->observed_behaviors, true) ?? [];
                            if(count($behaviors) > 0) {
                                echo '<br><small>' . implode(', ', array_slice($behaviors, 0, 2));
                                if(count($behaviors) > 2) echo '...';
                                echo '</small>';
                            }
                        @endphp
                    @else
                        {{ $item->comments ?? 'N/A' }}
                    @endif
                </td>
                <td>
                    @if($item->referral_type === 'risk_assessment')
                        <span class="badge {{ $item->counseling_status === 'resolved' ? 'badge-success' : ($item->counseling_status === 'ongoing' ? 'badge-info' : 'badge-warning') }}">
                            {{ ucfirst($item->counseling_status ?? 'pending') }}
                        </span>
                    @else
                        <span class="badge {{ $item->status === 'resolved' ? 'badge-success' : ($item->status === 'ongoing' ? 'badge-info' : 'badge-warning') }}">
                            {{ ucfirst($item->status ?? 'pending') }}
                        </span>
                    @endif
                </td>
                <td style="font-size: 8pt;">
                    @if($item->referral_type === 'risk_assessment')
                        {{ $item->scheduled_at ? $item->scheduled_at->format('M d, Y h:i A') : 'Not Scheduled' }}
                    @else
                        {{ $item->scheduled_at ? $item->scheduled_at->format('M d, Y h:i A') : 'Not Set' }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="document-id">
        Document ID: CR-{{ date('YmdHis') }}
    </div>

    <div class="footer">
        This is an official document generated by the Grade Evaluation System - Guidance & Counseling Department<br>
        Generated on {{ Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}
    </div>
</body>
</html>
