<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Lookup - University College of Jaffna</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom, #f5f7fa 0%, #e8ecf1 100%);
            background-image: url('views/includes/img/background.jpg');
            background-size: cover;
            background-position: center center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
            padding: 0;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(245, 247, 250, 0.85);
            z-index: 0;
        }

        .top-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
            border-bottom: 3px solid #1e3a5f;
        }

        .logo-left {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            min-width: 120px;
        }

        .logo-left img {
            height: 70px;
            width: auto;
            max-width: 100px;
            object-fit: contain;
        }

        .logo-left-text {
            font-size: 16px;
            font-weight: 600;
            color: #1e3a5f;
        }

        .header-center {
            text-align: center;
            flex: 1;
            padding: 0 20px;
        }

        .header-center h1 {
            font-size: 24px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .header-center .subtitle {
            font-size: 13px;
            color: #4a5568;
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .header-center .system-name {
            font-size: 15px;
            font-weight: 600;
            color: #2c5282;
            line-height: 1.3;
        }

        .logo-right {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            min-width: 100px;
        }

        .logo-right img {
            height: 60px;
            width: auto;
            max-width: 90px;
            object-fit: contain;
        }

        .logo-right-text {
            font-size: 12px;
            font-weight: 600;
            color: #1e3a5f;
            margin-top: 5px;
        }

        .main-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            position: relative;
            z-index: 5;
        }

        .search-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            padding: 40px;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
            position: relative;
            z-index: 1;
        }

        .search-title {
            text-align: center;
            font-size: 26px;
            font-weight: 600;
            color: #1e3a5f;
            margin-bottom: 30px;
            letter-spacing: 0.5px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 500;
            font-size: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #cbd5e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2c5282;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(44, 82, 130, 0.1);
        }

        .sample-format {
            font-size: 13px;
            color: #666;
            margin-top: 8px;
            font-style: italic;
        }

        .search-button-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn-search {
            padding: 14px 45px;
            background: linear-gradient(135deg, #2c5282 0%, #1e3a5f 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(30, 58, 95, 0.2);
        }

        .btn-search:hover {
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(30, 58, 95, 0.3);
        }

        .disclaimer {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
            font-size: 13px;
            color: #92400e;
        }

        .disclaimer strong {
            display: block;
            margin-bottom: 5px;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .student-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .student-info h3 {
            color: #1e3a5f;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .info-row {
            display: flex;
            margin-bottom: 10px;
        }

        .info-label {
            font-weight: 600;
            width: 150px;
            color: #666;
        }

        .info-value {
            color: #333;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .results-table th,
        .results-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .results-table th {
            background: linear-gradient(135deg, #2c5282 0%, #1e3a5f 100%);
            color: white;
            font-weight: 600;
        }

        .results-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .results-table tr:hover {
            background-color: #e9ecef;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .login-link a {
            color: #2c5282;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .login-link a:hover {
            color: #1e3a5f;
            text-decoration: underline;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pass {
            background-color: #d4edda;
            color: #155724;
        }

        .status-fail {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-absent {
            background-color: #fff3cd;
            color: #856404;
        }

        @media (max-width: 768px) {
            .top-header {
                padding: 15px 20px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .logo-left {
                min-width: 80px;
                flex: 0 0 auto;
                order: 1;
                justify-content: flex-start;
            }

            .logo-left img {
                height: 50px;
                max-width: 70px;
            }

            .header-center {
                flex: 1 1 100%;
                padding: 10px 0;
                order: 3;
                width: 100%;
                margin-top: 10px;
            }

            .header-center h1 {
                font-size: 18px;
            }

            .header-center .subtitle {
                font-size: 11px;
            }

            .header-center .system-name {
                font-size: 12px;
            }

            .logo-right {
                min-width: 80px;
                flex: 0 0 auto;
                order: 2;
                justify-content: flex-end;
            }

            .logo-right img {
                height: 45px;
                max-width: 65px;
            }

            .main-container {
                margin: 20px auto;
                padding: 0 15px;
            }

            .search-panel {
                padding: 25px 20px;
            }

            .search-title {
                font-size: 20px;
                margin-bottom: 20px;
            }

            .info-row {
                flex-direction: column;
            }

            .info-label {
                width: 100%;
                margin-bottom: 5px;
            }

            .results-table {
                font-size: 11px;
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .results-table th,
            .results-table td {
                padding: 6px 8px;
            }

            .btn-search {
                width: 100%;
                padding: 12px;
            }

            .search-button-container {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .top-header {
                padding: 12px 15px;
                flex-direction: column;
                align-items: center;
            }

            .logo-left {
                order: 1;
                margin-bottom: 10px;
                justify-content: center;
            }

            .logo-left img {
                height: 45px;
                max-width: 65px;
            }

            .header-center {
                order: 2;
                margin-top: 0;
                margin-bottom: 10px;
                padding: 0;
            }

            .header-center h1 {
                font-size: 16px;
            }

            .header-center .subtitle {
                font-size: 10px;
            }

            .header-center .system-name {
                font-size: 11px;
            }

            .logo-right {
                order: 3;
                justify-content: center;
            }

            .logo-right img {
                height: 40px;
                max-width: 60px;
            }

            .search-panel {
                padding: 20px 15px;
            }

            .search-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="top-header">
        <div class="logo-left">
            <img src="/exam/views/includes/img/ucjlogo.jpg" alt="UCJ Logo" onerror="this.style.display='none'">
        </div>
        <div class="header-center">
            <h1>UNIVERSITY COLLEGE OF JAFFNA</h1>
            <div class="subtitle">UNIVERSITY OF VOCATIONAL TECHNOLOGY</div>
            <div class="system-name">RESULT MANAGEMENT SYSTEM</div>
        </div>
        <div class="logo-right">
            <img src="/exam/views/includes/img/univertech logo.jpg" alt="UNIVOTEC Logo" onerror="this.style.display='none'">
        </div>
    </div>

    <div class="main-container">
        <div class="search-panel">
            <div class="search-title">Search Student Result</div>

            <?php if ($message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="search">Enter Registration Number or NIC</label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        required 
                        placeholder="Enter your Registration Number or NIC"
                        value="<?php echo htmlspecialchars($_POST['search'] ?? ''); ?>"
                    >
                    <div class="sample-format">
                        Sample Format - JF/MNT/21/01 OR (980350096V / 199803500069)
                    </div>
                </div>

                <div class="search-button-container">
                    <button type="submit" class="btn-search">Search</button>
                </div>
            </form>

            <div class="disclaimer">
                <strong>⚠️ Disclaimer:</strong>
                This result lookup is for informational purposes only and is not intended for official use. 
                For official transcripts or certificates, please contact the administration office.
            </div>

        <?php if ($student): ?>
            <div class="search-panel" style="margin-top: 30px;">
                <div class="student-info">
                    <h3>Student Information</h3>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student->fullname); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Registration No:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student->reg_no); ?></span>
                    </div>
                    <?php if (!empty($student->nic)): ?>
                    <div class="info-row">
                        <span class="info-label">NIC:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student->nic); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

            <?php if (empty($results)): ?>
                <div class="alert alert-error">
                    No exam results found for this student.
                </div>
            <?php else: ?>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Exam Date</th>
                            <th>Module Code</th>
                            <th>Module Name</th>
                            <th>Course</th>
                            <th>Assessment</th>
                            <th>Final Exam</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Attempt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Helper function for grade calculation
                        function getGradeFromPercentage($percentage, $final_exam_marks = null) {
                            if ($final_exam_marks !== null && $final_exam_marks < 40 && $percentage >= 40) {
                                return 'C-';
                            }
                            if ($percentage >= 85) return 'A+';
                            elseif ($percentage >= 80) return 'A';
                            elseif ($percentage >= 75) return 'A-';
                            elseif ($percentage >= 70) return 'B+';
                            elseif ($percentage >= 65) return 'B';
                            elseif ($percentage >= 60) return 'B-';
                            elseif ($percentage >= 50) return 'C+';
                            elseif ($percentage >= 40) return 'C';
                            elseif ($percentage >= 30) return 'C-';
                            elseif ($percentage >= 20) return 'D';
                            return 'F';
                        }
                        
                        foreach ($results as $result): 
                            $isNotEligible = (($result['eligibility'] ?? '') == 'not_eligible') || !empty($result['student_offense']);
                            
                            if ($isNotEligible) {
                                $total_percentage = 0;
                                $grade = '-';
                            } else {
                                $assess_contrib = ($result['assessment_marks'] ?? 0) * (($result['assessment_percentage'] ?? 0) / 100);
                                $final_contrib = ($result['final_exam_marks'] ?? 0) * (($result['final_exam_percentage'] ?? 0) / 100);
                                $total_percentage = $assess_contrib + $final_contrib;
                                $grade = getGradeFromPercentage($total_percentage, $result['final_exam_marks'] ?? 0);
                            }
                            
                            $statusClass = 'status-absent';
                            if ($result['status'] == 'pass') $statusClass = 'status-pass';
                            elseif ($result['status'] == 'fail') $statusClass = 'status-fail';
                        ?>
                        <tr>
                            <td><?php echo $result['exam_date'] ? date('d.m.Y', strtotime($result['exam_date'])) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($result['module_code'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($result['module_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($result['course_name'] ?? '-'); ?></td>
                            <td><?php echo $isNotEligible ? 'NE' : number_format($result['assessment_marks'] ?? 0, 2); ?></td>
                            <td><?php echo $isNotEligible ? 'NE' : number_format($result['final_exam_marks'] ?? 0, 2); ?></td>
                            <td><?php echo $isNotEligible ? 'NE' : number_format($total_percentage, 2) . '% (' . $grade . ')'; ?></td>
                            <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo strtoupper($result['status'] ?? 'ABSENT'); ?></span></td>
                            <td><?php echo htmlspecialchars($result['attempt'] ?? '1'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>

