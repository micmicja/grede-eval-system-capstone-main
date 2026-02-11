<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Report - {{ $student->full_name }}</title>
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
        .header .subtitle {
            font-size: 10pt;
            color: #555;
            margin-top: 5px;
        }
        .info-box {
            background-color: #f9fafb;
            padding: 18px;
            margin: 20px 0;
            border: 1px solid #d1d5db;
            border-left: 4px solid #1a237e;
        }
        .info-box p {
            margin: 8px 0;
            font-size: 10pt;
        }
        .info-box strong {
            color: #1a237e;
            min-width: 120px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            border: 1px solid #d1d5db;
        }
        th {
            background-color: #1a237e;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
            border-bottom: 2px solid #0d47a1;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9pt;
        }
        tbody tr:hover {
            background-color: #f9fafb;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 15px;
            color: #1a237e;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-card {
            display: inline-block;
            width: 30%;
            padding: 12px;
            margin: 1%;
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            text-align: center;
            vertical-align: top;
        }
        .summary-card h3 {
            margin: 0;
            font-size: 20pt;
            color: #1a237e;
            font-weight: bold;
        }
        .summary-card p {
            margin: 5px 0;
            font-size: 9pt;
            color: #555;
        }
        .summary-card .label {
            font-weight: 600;
            color: #333;
        }
        .overall-score {
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
            color: white;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
            border: 3px solid #1a237e;
        }
        .overall-score .label {
            margin: 0;
            font-size: 11pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
        }
        .overall-score h2 {
            margin: 8px 0 0 0;
            font-size: 32pt;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #1a237e;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
        .signature-section {
            margin-top: 50px;
            padding-top: 20px;
        }
        .signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
            margin: 0 2%;
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
        .risk-box {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .risk-high {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .risk-mid-high {
            background-color: #fff3cd;
            border-color: #fd7e14;
            color: #856404;
        }
        .risk-mid {
            background-color: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .risk-low {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .risk-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .risk-box p {
            margin: 5px 0;
            font-size: 11px;
        }
        .behaviors-list {
            margin-top: 10px;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="letterhead">
        <p class="institution">Grade Evaluation System</p>
        <p class="address">Academic Records and Assessment Department</p>
    </div>
    
    <div class="header">
        <h1>Student Academic Report</h1>
        <p class="subtitle">{{ $semester == 1 ? 'First' : 'Second' }} Semester, Academic Year {{ $year }}</p>
    </div>

    <div class="info-box">
        <p><strong>Student Name:</strong> {{ $student->full_name }}</p>
        <p><strong>Student ID:</strong> {{ $student->student_id ?? 'N/A' }}</p>
        <p><strong>Section/Course:</strong> {{ $student->section }}</p>
        <p><strong>Subject:</strong> {{ $student->subject }}</p>
        <p><strong>Teacher:</strong> {{ $teacher->full_name }}</p>
        <p><strong>Report Date:</strong> {{ $date }}</p>
    </div>

    <div class="overall-score">
        <p class="label">Overall Academic Performance</p>
        <h2>{{ number_format($overallScore, 2) }}%</h2>
    </div>

    @if($riskAssessment)
    <div class="risk-box 
        @if($riskAssessment->risk_status == 'High Risk') risk-high
        @elseif($riskAssessment->risk_status == 'Mid High Risk') risk-mid-high
        @elseif($riskAssessment->risk_status == 'Mid Risk') risk-mid
        @else risk-low
        @endif">
        <h3>🔔 Dropout Risk Assessment</h3>
        <p><strong>Risk Status:</strong> {{ $riskAssessment->risk_status }}</p>
        <p><strong>Calculated Average:</strong> {{ number_format($riskAssessment->calculated_average, 2) }}%</p>
        <p><strong>Assessment Date:</strong> {{ \Carbon\Carbon::parse($riskAssessment->created_at)->format('F d, Y') }}</p>
        @if($riskAssessment->referred_to_councilor)
        <p><strong>Status:</strong> Referred to Counselor</p>
        @endif
        @php
            $behaviors = $riskAssessment->observed_behaviors ?? [];
        @endphp
        @if(count($behaviors) > 0)
        <p><strong>Observed Behaviors/Factors:</strong></p>
        <ul class="behaviors-list">
            @foreach($behaviors as $behavior)
            <li>{{ $behavior }}</li>
            @endforeach
        </ul>
        @endif
    </div>
    @endif

    <div class="section-title">Grade Summary</div>
    <div style="margin-bottom: 20px;">
        <div class="summary-card">
            <h3>{{ number_format($quizAvg, 2) }}%</h3>
            <p class="label">Quiz Average</p>
            <p>({{ $weights['quiz'] }}% weight)</p>
        </div>
        <div class="summary-card">
            <h3>{{ number_format($examAvg, 2) }}%</h3>
            <p class="label">Exam Average</p>
            <p>({{ $weights['exam'] }}% weight)</p>
        </div>
        <div class="summary-card">
            <h3>{{ number_format($activityAvg, 2) }}%</h3>
            <p class="label">Activity Average</p>
            <p>({{ $weights['activity'] }}% weight)</p>
        </div>
        <div class="summary-card">
            <h3>{{ number_format($projectAvg, 2) }}%</h3>
            <p class="label">Project Average</p>
            <p>({{ $weights['project'] }}% weight)</p>
        </div>
        <div class="summary-card">
            <h3>{{ number_format($recitationAvg, 2) }}%</h3>
            <p class="label">Recitation Average</p>
            <p>({{ $weights['recitation'] }}% weight)</p>
        </div>
        <div class="summary-card">
            <h3>{{ number_format($attendancePercentage, 2) }}%</h3>
            <p class="label">Attendance</p>
            <p>({{ $weights['attendance'] }}% weight)</p>
        </div>
    </div>

    @if($quizzes->count() > 0)
    <div class="section-title">Quiz Records</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Title</th>
                <th>Score</th>
                <th>Total</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quizzes as $quiz)
            <tr>
                <td>{{ \Carbon\Carbon::parse($quiz->date_taken)->format('M d, Y') }}</td>
                <td>{{ $quiz->activity_title }}</td>
                <td>{{ $quiz->score }}</td>
                <td>{{ $quiz->total_score }}</td>
                <td>{{ $quiz->total_score > 0 ? number_format(($quiz->score / $quiz->total_score) * 100, 2) : '0.00' }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($exams->count() > 0)
    <div class="section-title">Exam Records</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Title</th>
                <th>Score</th>
                <th>Total</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($exams as $exam)
            <tr>
                <td>{{ \Carbon\Carbon::parse($exam->date_taken)->format('M d, Y') }}</td>
                <td>{{ $exam->activity_title }}</td>
                <td>{{ $exam->score }}</td>
                <td>{{ $exam->total_score }}</td>
                <td>{{ $exam->total_score > 0 ? number_format(($exam->score / $exam->total_score) * 100, 2) : '0.00' }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                <strong>{{ $teacher->full_name }}</strong><br>
                Subject Teacher
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
        Document ID: SR-{{ $student->id }}-{{ date('YmdHis') }}
    </div>

    <div class="footer">
        <p>This is an official document generated by the Grade Evaluation System</p>
        <p style="font-size: 7pt; margin-top: 5px;">Generated on {{ $date }}</p>
    </div>
</body>
</html>
