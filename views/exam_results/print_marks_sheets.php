<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marks Sheets</title>
    <style>
        @media print {
            .no-print { display: none; }
            .marks-sheet { page-break-after: always; }
            .marks-sheet:last-child { page-break-after: auto; }
        }
        body { font-family: Arial, sans-serif; margin: 20px; }
        .marks-sheet { border: 2px solid #333; padding: 30px; margin-bottom: 30px; background: white; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header h2 { margin: 5px 0; font-size: 16px; color: #666; }
        .exam-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 20px 0; }
        .info-item { }
        .info-item label { font-weight: 600; }
        .marks-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .marks-table th, .marks-table td { border: 1px solid #333; padding: 12px; text-align: left; }
        .marks-table th { background: #667eea; color: white; font-weight: 600; }
        .signature-section { margin-top: 40px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 40px; }
        .signature-box { border-top: 1px solid #333; padding-top: 10px; }
        .btn-print { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; margin: 10px 5px; }
    </style>
</head>
<body>
    <?php
    require_once __DIR__ . '/../../models/Module.php';
    require_once __DIR__ . '/../../models/Course.php';
    $module = new Module();
    $course = new Course();
    if ($module->getById($exam->module_id)) {
        $moduleCode = $module->mcode;
        $moduleName = $module->mname;
    } else {
        $moduleCode = '-';
        $moduleName = '-';
    }
    if ($course->getById($exam->course_id)) {
        $courseName = $course->cname;
    } else {
        $courseName = '-';
    }
    
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
    ?>
    
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" class="btn-print">🖨️ Print All Marks Sheets</button>
        <button onclick="window.close()" class="btn-print" style="background: #6c757d;">Close</button>
    </div>

    <?php foreach($results as $result): 
        $isNotEligible = (($result['eligibility'] ?? '') == 'not_eligible') || !empty($result['student_offense']);
        if ($isNotEligible) {
            $total_percentage = 0;
            $grade = '-';
            $assess_contrib = 0;
            $final_contrib = 0;
        } else {
            $assess_contrib = ($result['assessment_marks'] ?? 0) * (($result['assessment_percentage'] ?? 0) / 100);
            $final_contrib = ($result['final_exam_marks'] ?? 0) * (($result['final_exam_percentage'] ?? 0) / 100);
            $total_percentage = $assess_contrib + $final_contrib;
            $grade = getGradeFromPercentage($total_percentage, $result['final_exam_marks'] ?? 0);
        }
    ?>
        <div class="marks-sheet">
            <div class="header">
                <h1>EXAMINATION MARKS SHEET</h1>
                <h2><?php echo htmlspecialchars($courseName); ?></h2>
            </div>

            <div class="exam-info">
                <div class="info-item">
                    <label>Student Name:</label> <?php echo htmlspecialchars($result['student_name'] ?? '-'); ?>
                </div>
                <div class="info-item">
                    <label>Registration Number:</label> <?php echo htmlspecialchars($result['reg_no'] ?? '-'); ?>
                </div>
                <div class="info-item">
                    <label>Module Code:</label> <?php echo htmlspecialchars($moduleCode); ?>
                </div>
                <div class="info-item">
                    <label>Module Name:</label> <?php echo htmlspecialchars($moduleName); ?>
                </div>
                <div class="info-item">
                    <label>Exam Date:</label> <?php echo $exam->exam_date ? date('d.m.Y', strtotime($exam->exam_date)) : '-'; ?>
                </div>
                <div class="info-item">
                    <label>Time Slot:</label> <?php echo htmlspecialchars($exam->time_slot ?? '-'); ?>
                </div>
                <div class="info-item">
                    <label>Location:</label> <?php echo htmlspecialchars($exam->location ?? '-'); ?>
                </div>
                <div class="info-item">
                    <label>Attempt:</label> <?php echo htmlspecialchars($result['attempt'] ?? '1'); ?>
                </div>
            </div>

            <table class="marks-table">
                <thead>
                    <tr>
                        <th>Component</th>
                        <th>Marks Obtained</th>
                        <th>Weight (%)</th>
                        <th>Weighted Marks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Assessment</td>
                        <td><?php echo $isNotEligible ? '<strong style="color:#dc3545;">NE</strong>' : number_format($result['assessment_marks'] ?? 0, 2); ?></td>
                        <td><?php echo number_format($exam->assessment_percentage ?? 0, 2); ?>%</td>
                        <td><?php echo $isNotEligible ? '<strong style="color:#dc3545;">NE</strong>' : number_format($assess_contrib, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Final Exam</td>
                        <td><?php echo $isNotEligible ? '<strong style="color:#dc3545;">NE</strong>' : number_format($result['final_exam_marks'] ?? 0, 2); ?></td>
                        <td><?php echo number_format($exam->final_exam_percentage ?? 0, 2); ?>%</td>
                        <td><?php echo $isNotEligible ? '<strong style="color:#dc3545;">NE</strong>' : number_format($final_contrib, 2); ?></td>
                    </tr>
                    <tr style="background: #f8f9fa; font-weight: 600;">
                        <td colspan="3">Total Percentage</td>
                        <td><?php echo $isNotEligible ? '<strong style="color:#dc3545;">NE</strong>' : number_format($total_percentage, 2); ?>%</td>
                    </tr>
                    <tr style="background: #f8f9fa; font-weight: 600;">
                        <td colspan="3">Grade</td>
                        <td><?php echo htmlspecialchars($grade); ?></td>
                    </tr>
                    <tr style="background: #f8f9fa; font-weight: 600;">
                        <td colspan="3">Status</td>
                        <td>
                            <span style="color: <?php echo ($result['status'] ?? 'absent') == 'pass' ? '#28a745' : (($result['status'] ?? 'absent') == 'fail' ? '#dc3545' : '#6c757d'); ?>;">
                                <?php echo ucfirst(htmlspecialchars($result['status'] ?? 'absent')); ?>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php if ($isNotEligible): ?>
                <div style="margin-top: 20px; padding: 15px; background: #fff5f5; border-left: 4px solid #dc3545;">
                    <strong style="color: #dc3545;">Note:</strong> 
                    <?php if (!empty($result['student_offense'])): ?>
                        Student has an offense: <?php 
                        $offenseLabels = [
                            'cheating' => 'Cheating',
                            'misconduct' => 'Misconduct',
                            'absent_without_excuse' => 'Absent Without Excuse',
                            'other' => 'Other'
                        ];
                        echo htmlspecialchars($offenseLabels[$result['student_offense']] ?? ucfirst($result['student_offense']));
                        ?>
                    <?php else: ?>
                        Student is not eligible for this examination.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="signature-section">
                <div class="signature-box">
                    <p><strong>Examiner Signature:</strong></p>
                    <p style="margin-top: 40px;">_________________________</p>
                </div>
                <div class="signature-box">
                    <p><strong>Date:</strong> <?php echo date('d.m.Y'); ?></p>
                    <p style="margin-top: 40px;">_________________________</p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</body>
</html>

