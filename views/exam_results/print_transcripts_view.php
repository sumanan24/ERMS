<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Transcripts</title>
    <style>
        @media print {
            .no-print { display: none; }
            .transcript { page-break-after: always; }
            .transcript:last-child { page-break-after: auto; }
        }
        body { font-family: Arial, sans-serif; margin: 20px; }
        .transcript { border: 2px solid #333; padding: 30px; margin-bottom: 30px; background: white; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 22px; }
        .student-info { margin: 20px 0; }
        .student-info p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #333; padding: 10px; text-align: left; }
        th { background: #667eea; color: white; font-weight: 600; }
        tr:nth-child(even) { background: #f8f9fa; }
        .status-pass { color: #28a745; font-weight: 600; }
        .status-fail { color: #dc3545; font-weight: 600; }
        .btn-print { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; margin: 10px 5px; }
    </style>
</head>
<body>
    <?php
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
        <button onclick="window.print()" class="btn-print">🖨️ Print All Transcripts</button>
        <button onclick="window.close()" class="btn-print" style="background: #6c757d;">Close</button>
    </div>

    <?php foreach($studentResults as $studentData): 
        $student = $studentData['student'];
        $studentResultsList = $studentData['results'];
    ?>
        <div class="transcript">
            <div class="header">
                <h1>ACADEMIC TRANSCRIPT</h1>
            </div>

            <div class="student-info">
                <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
                <p><strong>Registration Number:</strong> <?php echo htmlspecialchars($student['reg_no']); ?></p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Course</th>
                        <th>Module Code</th>
                        <th>Module Name</th>
                        <th>Exam Date</th>
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
                    foreach($studentResultsList as $result): 
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
                            <td><?php echo htmlspecialchars($result['course_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($result['module_code'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($result['module_name'] ?? '-'); ?></td>
                            <td><?php echo $result['exam_date'] ? date('d.m.Y', strtotime($result['exam_date'])) : '-'; ?></td>
                            <td><?php echo $isNotEligible ? '<span class="status-fail">NE</span>' : number_format($result['assessment_marks'] ?? 0, 2); ?></td>
                            <td><?php echo $isNotEligible ? '<span class="status-fail">NE</span>' : number_format($result['final_exam_marks'] ?? 0, 2); ?></td>
                            <td><?php echo $isNotEligible ? '<span class="status-fail">NE</span>' : number_format($total_percentage, 2); ?></td>
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

            <div style="margin-top: 30px; text-align: right;">
                <p><strong>Generated on:</strong> <?php echo date('d.m.Y H:i:s'); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</body>
</html>

