<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
    .main-content { margin-left: 280px; margin-top: 70px; padding: 30px; background: white; min-height: calc(100vh - 70px); overflow: auto; }
    .view-container { max-width: 1200px; margin: 0 auto; }
    .exam-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); }
    .exam-header h1 { margin: 0 0 15px 0; font-size: 28px; }
    .exam-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px; }
    .info-item { background: rgba(255,255,255,0.1); padding: 12px; border-radius: 8px; }
    .info-item label { display: block; font-size: 12px; opacity: 0.9; margin-bottom: 5px; }
    .info-item .value { font-size: 16px; font-weight: 600; }
    .btn-group { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .btn { padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block; }
    .btn-primary { background: #667eea; color: white; }
    .btn-print { background: #28a745; color: white; }
    .results-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .results-table th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
    .results-table td { padding: 12px; border-bottom: 1px solid #f0f0f0; }
    .results-table tr:hover { background: #f8f9fa; }
    .status-pass { color: #28a745; font-weight: 600; }
    .status-fail { color: #dc3545; font-weight: 600; }
    .status-absent { color: #6c757d; }
    .not-eligible { color: #dc3545; font-weight: 600; }
</style>

<div class="main-content">
    <div class="view-container">
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
        
        // Helper function for grade
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
        
        <div class="exam-header">
            <h1>Exam Results View</h1>
            <div class="exam-info">
                <div class="info-item">
                    <label>Course</label>
                    <div class="value"><?php echo htmlspecialchars($courseName); ?></div>
                </div>
                <div class="info-item">
                    <label>Module Code</label>
                    <div class="value"><?php echo htmlspecialchars($moduleCode); ?></div>
                </div>
                <div class="info-item">
                    <label>Module Name</label>
                    <div class="value"><?php echo htmlspecialchars($moduleName); ?></div>
                </div>
                <div class="info-item">
                    <label>Exam Date</label>
                    <div class="value"><?php echo $exam->exam_date ? date('d.m.Y', strtotime($exam->exam_date)) : '-'; ?></div>
                </div>
                <div class="info-item">
                    <label>Time Slot</label>
                    <div class="value"><?php echo htmlspecialchars($exam->time_slot ?? '-'); ?></div>
                </div>
                <div class="info-item">
                    <label>Location</label>
                    <div class="value"><?php echo htmlspecialchars($exam->location ?? '-'); ?></div>
                </div>
                <div class="info-item">
                    <label>Assessment %</label>
                    <div class="value"><?php echo number_format($exam->assessment_percentage ?? 0, 2); ?>%</div>
                </div>
                <div class="info-item">
                    <label>Final Exam %</label>
                    <div class="value"><?php echo number_format($exam->final_exam_percentage ?? 0, 2); ?>%</div>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <a href="index.php?action=exam_results&filter_exam=<?php echo $exam->id; ?>" class="btn btn-primary">← Back to Results</a>
            <a href="index.php?action=exam_results&sub=printReport&exam_id=<?php echo $exam->id; ?>" class="btn btn-print" target="_blank">🖨️ Print Report</a>
            <a href="index.php?action=exam_results&sub=printMarksSheets&exam_id=<?php echo $exam->id; ?>" class="btn btn-print" target="_blank">📋 Print Marks Sheets</a>
        </div>

        <table class="results-table">
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
                <?php if (empty($results)): ?>
                    <tr><td colspan="11" style="text-align:center; padding:30px;">No results found.</td></tr>
                <?php else: ?>
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
                            <td class="<?php echo (($result['eligibility'] ?? '') == 'not_eligible') ? 'not-eligible' : ''; ?>">
                                <?php echo ucfirst(htmlspecialchars($result['eligibility'] ?? '-')); ?>
                            </td>
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
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

