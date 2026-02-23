<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results Report</title>
    <style>
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 20px; }
        }
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #333; padding-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header h2 { margin: 10px 0 0 0; font-size: 18px; color: #666; }
        .exam-info { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; }
        .info-item { }
        .info-item label { font-weight: 600; display: block; margin-bottom: 5px; }
        .info-item .value { }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #667eea; color: white; font-weight: 600; }
        tr:nth-child(even) { background: #f8f9fa; }
        .status-pass { color: #28a745; font-weight: 600; }
        .status-fail { color: #dc3545; font-weight: 600; }
        .not-eligible { color: #dc3545; font-weight: 600; }
        .btn-print { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; margin: 10px 5px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
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
        <button onclick="window.print()" class="btn-print">🖨️ Print Report</button>
        <button onclick="window.close()" class="btn-print" style="background: #6c757d;">Close</button>
    </div>

    <div class="header">
        <h1>EXAM RESULTS REPORT</h1>
        <h2><?php echo htmlspecialchars($courseName); ?></h2>
    </div>

    <div class="exam-info">
        <div class="info-item">
            <label>Module Code:</label>
            <div class="value"><?php echo htmlspecialchars($moduleCode); ?></div>
        </div>
        <div class="info-item">
            <label>Module Name:</label>
            <div class="value"><?php echo htmlspecialchars($moduleName); ?></div>
        </div>
        <div class="info-item">
            <label>Exam Date:</label>
            <div class="value"><?php echo $exam->exam_date ? date('d.m.Y', strtotime($exam->exam_date)) : '-'; ?></div>
        </div>
        <div class="info-item">
            <label>Time Slot:</label>
            <div class="value"><?php echo htmlspecialchars($exam->time_slot ?? '-'); ?></div>
        </div>
        <div class="info-item">
            <label>Location:</label>
            <div class="value"><?php echo htmlspecialchars($exam->location ?? '-'); ?></div>
        </div>
        <div class="info-item">
            <label>Assessment % / Final Exam %:</label>
            <div class="value"><?php echo number_format($exam->assessment_percentage ?? 0, 2); ?>% / <?php echo number_format($exam->final_exam_percentage ?? 0, 2); ?>%</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Reg No</th>
                <th>Student Name</th>
                <th>Eligibility</th>
                <th>Offense</th>
                <th>Attempt</th>
                <th>Assessment Marks</th>
                <th>Final Exam Marks</th>
                <th>Total Percentage</th>
                <th>Grade</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach($results as $result): 
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
            ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($result['reg_no'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($result['student_name'] ?? '-'); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($result['eligibility'] ?? '-')); ?></td>
                    <td>
                        <?php 
                        if (!empty($result['student_offense'])) {
                            $offenseLabels = [
                                'cheating' => 'Cheating',
                                'misconduct' => 'Misconduct',
                                'absent_without_excuse' => 'Absent Without Excuse',
                                'other' => 'Other'
                            ];
                            echo '<span class="not-eligible">' . htmlspecialchars($offenseLabels[$result['student_offense']] ?? ucfirst($result['student_offense'])) . '</span>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($result['attempt'] ?? '1'); ?></td>
                    <td><?php echo $isNotEligible ? '<span class="not-eligible">NE</span>' : number_format($result['assessment_marks'] ?? 0, 2); ?></td>
                    <td><?php echo $isNotEligible ? '<span class="not-eligible">NE</span>' : number_format($result['final_exam_marks'] ?? 0, 2); ?></td>
                    <td><?php echo $isNotEligible ? '<span class="not-eligible">NE</span>' : number_format($total_percentage, 2); ?></td>
                    <td><?php echo htmlspecialchars($grade); ?></td>
                    <td>
                        <span class="status-<?php echo htmlspecialchars($result['status'] ?? 'absent'); ?>">
                            <?php echo ucfirst(htmlspecialchars($result['status'] ?? 'absent')); ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on: <?php echo date('d.m.Y H:i:s'); ?></p>
        <p>Total Students: <?php echo count($results); ?></p>
    </div>
</body>
</html>

