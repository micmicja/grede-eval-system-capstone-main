<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $activityType }} Results - {{ $activityTitle }}</title>
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
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tbody tr:hover {
            background-color: #f3f4f6;
        }
        .statistics {
            margin: 25px 0;
            padding: 18px;
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
        }
        .statistics strong {
            color: #1a237e;
            font-size: 11pt;
            display: block;
            margin-bottom: 15px;
        }
        .stat-item {
            display: inline-block;
            width: 18%;
            margin: 0.5%;
            padding: 12px;
            background-color: white;
            border: 1px solid #d1d5db;
            text-align: center;
        }
        .stat-item h3 {
            margin: 5px 0;
            font-size: 18pt;
            font-weight: bold;
        }
        .stat-item p {
            margin: 5px 0;
            font-size: 9pt;
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
        .signature-section {
            margin-top: 60px;
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
        .pass {
            color: #28a745;
            font-weight: bold;
        }
        .fail {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="letterhead">
        <p class="institution">Grade Evaluation System</p>
        <p class="address">Academic Records and Assessment Department</p>
    </div>

    <div class="header">
        <h1>{{ $activityType }} Performance Report</h1>
        <p><strong>Title:</strong> {{ $activityTitle }}</p>
        <p><strong>Date Administered:</strong> {{ \Carbon\Carbon::parse($dateTaken)->format('F d, Y') }}</p>
    </div>

    <div class="info-box">
        <p><strong>Teacher:</strong> {{ $teacher->full_name }}</p>
        <p><strong>Total Students:</strong> {{ $results->count() }}</p>
        <p><strong>Report Generated:</strong> {{ $date }}</p>
    </div>

    @php
        $totalScore = $results->first()->total_score ?? 100;
        $averageScore = $results->avg('score');
        $highestScore = $results->max('score');
        $lowestScore = $results->min('score');
        $passingScore = $totalScore * 0.6;
        $passedCount = $results->where('score', '>=', $passingScore)->count();
        $failedCount = $results->where('score', '<', $passingScore)->count();
    @endphp

    <div class="statistics">
        <strong>Performance Statistics and Analysis</strong>
        <div style="margin-top: 10px;">
            <div class="stat-item">
                <h3 style="color: #1a237e;">{{ number_format($averageScore, 2) }}</h3>
                <p>Average Score</p>
            </div>
            <div class="stat-item">
                <h3 style="color: #1a237e;">{{ $highestScore }}</h3>
                <p>Highest Score</p>
            </div>
            <div class="stat-item">
                <h3 style="color: #1a237e;">{{ $lowestScore }}</h3>
                <p>Lowest Score</p>
            </div>
            <div class="stat-item">
                <h3 style="color: #28a745;">{{ $passedCount }}</h3>
                <p>Passed (≥60%)</p>
            </div>
            <div class="stat-item">
                <h3 style="color: #dc3545;">{{ $failedCount }}</h3>
                <p>Failed (<60%)</p>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Score</th>
                <th>Total</th>
                <th>Percentage</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results->sortByDesc('score') as $index => $result)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $result->full_name }}</td>
                <td>{{ $result->score }}</td>
                <td>{{ $totalScore }}</td>
                <td>{{ $totalScore > 0 ? number_format(($result->score / $totalScore) * 100, 2) : '0.00' }}%</td>
                <td class="{{ $result->score >= $passingScore ? 'pass' : 'fail' }}">
                    {{ $result->score >= $passingScore ? 'Passed' : 'Failed' }}
                </td>
            </tr>
            @endforeach
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
                Department Head<br>
                Academic Affairs
            </div>
        </div>
    </div>

    <div class="document-id">
        Document ID: {{ strtoupper(substr($activityType, 0, 2)) }}-{{ date('YmdHis') }}
    </div>

    <div class="footer">
        <p>This is an official document generated by the Grade Evaluation System</p>
        <p style="font-size: 7pt; margin-top: 5px;">Generated on {{ $date }}</p>
    </div>
</body>
</html>
