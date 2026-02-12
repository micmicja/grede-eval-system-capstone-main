<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student List</title>
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
    </style>
</head>
<body>
    <div class="letterhead">
        <p class="institution">Grade Evaluation System</p>
        <p class="address">Academic Records and Assessment Department</p>
    </div>

    <div class="header">
        <h1>Official Student Roster</h1>
        <p><strong>Teacher:</strong> {{ $teacher->full_name }}</p>
        <p><strong>Date Generated:</strong> {{ $date }}</p>
    </div>

    <div class="summary">
        <strong>Total Students:</strong> {{ $students->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Section/Course</th>
                <th>Subject</th>
                <th>Risk Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student->student_id ?? 'N/A' }}</td>
                <td>{{ $student->full_name }}</td>
                <td>{{ $student->section }}</td>
                <td>{{ $student->subject }}</td>
                <td style="font-weight: bold; color: {{ 
                    $student->risk_status === 'High Risk' ? '#dc3545' : 
                    ($student->risk_status === 'Mid High Risk' ? '#fd7e14' : 
                    ($student->risk_status === 'Mid Risk' ? '#ffc107' : '#28a745')) 
                }};">{{ $student->risk_status ?? 'N/A' }}</td>
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
                Registrar's Office<br>
                Academic Affairs
            </div>
        </div>
    </div>

    <div class="document-id">
        Document ID: SL-{{ date('YmdHis') }}
    </div>

    <div class="footer">
        <p>This is an official document generated by the Grade Evaluation System</p>
        <p style="font-size: 7pt; margin-top: 5px;">Generated on {{ $date }}</p>
    </div>
</body>
</html>
