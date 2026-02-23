<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php
// Helper to map total percentage to letter grade
// Special rule: If final exam marks < 40 but total percentage >= 40, grade is C-
function getGradeFromPercentage($percentage, $final_exam_marks = null) {
    // Special rule: If final exam marks < 40 but total percentage >= 40, grade is C-
    if ($final_exam_marks !== null && $final_exam_marks < 40 && $percentage >= 40) {
        return 'C-';
    }
    
    if ($percentage >= 85) {
        return 'A+';
    } elseif ($percentage >= 80) {
        return 'A';
    } elseif ($percentage >= 75) {
        return 'A-';
    } elseif ($percentage >= 70) {
        return 'B+';
    } elseif ($percentage >= 65) {
        return 'B';
    } elseif ($percentage >= 60) {
        return 'B-';
    } elseif ($percentage >= 50) {
        return 'C+';
    } elseif ($percentage >= 40) {
        return 'C';
    } elseif ($percentage >= 30) {
        return 'C-';
    } elseif ($percentage >= 20) {
        return 'D';
    }
    return 'F';
}
?>

<style>
    .main-content { margin-left: 280px; margin-top: 70px; padding: 30px; background: white; min-height: calc(100vh - 70px); overflow: auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
    .page-title { font-size: 24px; color: #333; font-weight: 600; }
    .btn-primary { padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; }
    .btn-primary:hover { background: #5568d3; }
    .btn-download { padding: 10px 20px; background: #17a2b8; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; }
    .filter-container { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; }
    .filter-group { flex: 1; min-width: 200px; }
    .filter-group label { display: block; margin-bottom: 6px; color: #333; font-weight: 500; font-size: 13px; }
    .filter-group input, .filter-group select { width: 100%; padding: 10px 12px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px; }
    .btn-filter { padding: 10px 16px; background: #667eea; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
    .btn-reset { padding: 10px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; }
    .table-container { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 1000px; }
    th, td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; text-align: left; font-size: 13px; }
    th small { font-size: 11px; font-weight: normal; color: #666; display: block; margin-top: 2px; }
    thead { background: #f8f9fa; }
    .btn-group { display: flex; gap: 8px; }
    .btn-edit { padding: 6px 12px; background: #667eea; color: white; border-radius: 4px; text-decoration: none; font-size: 12px; }
    .btn-delete { padding: 6px 12px; background: #dc3545; color: white; border-radius: 4px; text-decoration: none; font-size: 12px; }
    .status-pass { color: #28a745; font-weight: 600; }
    .status-fail { color: #dc3545; font-weight: 600; }
    .not-eligible-cell { background-color: #fff5f5; }
    .offense-cell { background-color: #fff5f5; }
    .status-absent { color: #6c757d; }
    .marks-input { width: 80px; padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; text-align: center; }
    .marks-input:focus { border-color: #667eea; outline: none; }
    .saving { opacity: 0.6; pointer-events: none; }
    .percentage-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; display: flex; gap: 30px; align-items: center; flex-wrap: wrap; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); }
    .percentage-item { display: flex; flex-direction: column; gap: 5px; }
    .percentage-item label { font-size: 12px; opacity: 0.9; font-weight: 500; }
    .percentage-item .value { font-size: 24px; font-weight: 700; }
</style>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">Exam Results Management</h1>
        <?php if (in_array($_SESSION['role'] ?? '', ['admin','teacher'])): ?>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <?php if (!empty($_GET['filter_exam'])): ?>
                    <a href="index.php?action=exam_results&sub=addStudents&exam_id=<?php echo htmlspecialchars($_GET['filter_exam']); ?>" class="btn-primary">+ Add Students</a>
                    <a href="index.php?action=exam_results&sub=view&exam_id=<?php echo htmlspecialchars($_GET['filter_exam']); ?>" class="btn-download" style="background:#28a745;">👁️ View Results</a>
                    <a href="index.php?action=exam_results&sub=printReport&exam_id=<?php echo htmlspecialchars($_GET['filter_exam']); ?>" class="btn-download" target="_blank">📄 Print Report</a>
                    <a href="index.php?action=exam_results&sub=printMarksSheets&exam_id=<?php echo htmlspecialchars($_GET['filter_exam']); ?>" class="btn-download" style="background:#17a2b8;" target="_blank">📋 Print Marks Sheets</a>
                    <a href="index.php?action=exam_results&sub=downloadAttendance&exam_id=<?php echo htmlspecialchars($_GET['filter_exam']); ?>" class="btn-download">📋 Attendance Sheet</a>
                    <a href="index.php?action=exam_results&sub=downloadMarking&exam_id=<?php echo htmlspecialchars($_GET['filter_exam']); ?>&type=first" class="btn-download" style="background:#ffc107;">📝 First Marking</a>
                    <a href="index.php?action=exam_results&sub=downloadMarking&exam_id=<?php echo htmlspecialchars($_GET['filter_exam']); ?>&type=second" class="btn-download" style="background:#fd7e14;">📝 Second Marking</a>
                <?php endif; ?>
                <a href="index.php?action=exam_results&sub=printTranscripts" class="btn-download" style="background:#6f42c1;" target="_blank">🎓 Print Transcripts</a>
                <a href="index.php?action=exam_results&sub=admissionCards" class="btn-download" style="background:#0d6efd;">🎫 Admission Cards</a>
                <a href="index.php?action=exam_results&sub=marksSummary" class="btn-download" style="background:#28a745;">📊 Marks Summary (Excel)</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="GET" action="index.php" class="filter-container">
        <input type="hidden" name="action" value="exam_results">
        <div class="filter-group">
            <label for="filter_exam">Exam</label>
            <select id="filter_exam" name="filter_exam">
                <option value="">All Exams</option>
                <?php foreach($exams as $exam): ?>
                    <option value="<?php echo $exam['id']; ?>" <?php echo (($_GET['filter_exam'] ?? '')==$exam['id'])?'selected':''; ?>>
                        <?php echo date('d.m.Y', strtotime($exam['exam_date'])) . ' - ' . htmlspecialchars($exam['module_code'] ?? ''); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="filter_status">Status</label>
            <select id="filter_status" name="filter_status">
                <option value="">All Status</option>
                <option value="pass" <?php echo (($_GET['filter_status'] ?? '')=='pass')?'selected':''; ?>>Pass</option>
                <option value="fail" <?php echo (($_GET['filter_status'] ?? '')=='fail')?'selected':''; ?>>Fail</option>
                <option value="absent" <?php echo (($_GET['filter_status'] ?? '')=='absent')?'selected':''; ?>>Absent</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filter_search">Search</label>
            <input type="text" id="filter_search" name="filter_search" placeholder="Reg no, name..." value="<?php echo htmlspecialchars($_GET['filter_search'] ?? ''); ?>">
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn-filter">Filter</button>
            <a href="index.php?action=exam_results" class="btn-reset">Reset</a>
        </div>
    </form>

    <?php 
    // Get exam details and percentage values from first result if available
    $courseName = '';
    $moduleCode = '';
    $moduleName = '';
    $examDate = '';
    $timeSlot = '';
    $location = '';
    $assessmentPercent = 0;
    $finalExamPercent = 0;
    if (!empty($results)) {
        $firstResult = $results[0];
        $courseName = $firstResult['course_name'] ?? '';
        $moduleCode = $firstResult['module_code'] ?? '';
        $moduleName = $firstResult['module_name'] ?? '';
        $examDate = $firstResult['exam_date'] ?? '';
        $timeSlot = $firstResult['time_slot'] ?? '';
        $location = $firstResult['location'] ?? '';
        $assessmentPercent = $firstResult['assessment_percentage'] ?? 0;
        $finalExamPercent = $firstResult['final_exam_percentage'] ?? 0;
    }
    ?>
    
    <?php if (!empty($results)): ?>
        <div class="percentage-card">
            <div style="width: 100%; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 10px;">
                <strong style="font-size: 20px; opacity: 0.95;"><?php echo htmlspecialchars($courseName ?: 'Course Name'); ?></strong>
            </div>
            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap; width: 100%;">
                <div class="percentage-item">
                    <label>Module Code</label>
                    <div class="value" style="font-size: 16px;"><?php echo htmlspecialchars($moduleCode ?: '-'); ?></div>
                </div>
                <div class="percentage-item">
                    <label>Module Name</label>
                    <div class="value" style="font-size: 16px;"><?php echo htmlspecialchars($moduleName ?: '-'); ?></div>
                </div>
                <div class="percentage-item">
                    <label>Date</label>
                    <div class="value" style="font-size: 16px;"><?php echo $examDate ? date('d.m.Y', strtotime($examDate)) : '-'; ?></div>
                </div>
                <div class="percentage-item">
                    <label>Time</label>
                    <div class="value" style="font-size: 16px;"><?php echo htmlspecialchars($timeSlot ?: '-'); ?></div>
                </div>
                <div class="percentage-item">
                    <label>Location</label>
                    <div class="value" style="font-size: 16px;"><?php echo htmlspecialchars($location ?: '-'); ?></div>
                </div>
                <?php if ($assessmentPercent > 0 || $finalExamPercent > 0): ?>
                    <div class="percentage-item" style="margin-left: auto; border-left: 1px solid rgba(255,255,255,0.2); padding-left: 20px;">
                        <label>Assessment %</label>
                        <div class="value"><?php echo number_format($assessmentPercent, 2); ?>%</div>
                    </div>
                    <div class="percentage-item">
                        <label>Final Exam %</label>
                        <div class="value"><?php echo number_format($finalExamPercent, 2); ?>%</div>
                    </div>
                    <div class="percentage-item">
                        <label>Total Weight</label>
                        <div class="value"><?php echo number_format($assessmentPercent + $finalExamPercent, 2); ?>%</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Reg No</th>
                    <th>Student Name</th>
                    <th>Eligibility</th>
                    <th>Student Offense</th>
                    <th>Attempt</th>
                    <th>Assessment Marks</th>
                    <th>Final Exam Marks</th>
                    <th>Assessment Percentage<br><small>(from Assessment %)</small></th>
                    <th>Final Exam Percentage<br><small>(from Final Exam %)</small></th>
                    <th>Total Percentage</th>
                    <th>Grade</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)): ?>
                    <tr><td colspan="13" style="text-align:center; padding:30px; color:#777;">No results found.</td></tr>
                <?php else: ?>
                    <?php foreach($results as $result): ?>
                        <tr>
                           
                            <td><?php echo htmlspecialchars($result['reg_no'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($result['student_name'] ?? '-'); ?></td>
                            <td class="<?php echo (($result['eligibility'] ?? '') == 'not_eligible') ? 'not-eligible-cell' : ''; ?>">
                                <?php 
                                $eligibility = ucfirst(htmlspecialchars($result['eligibility'] ?? '-'));
                                if (($result['eligibility'] ?? '') == 'not_eligible') {
                                    echo '<span style="color: #dc3545; font-weight: 600;">' . $eligibility . '</span>';
                                } else {
                                    echo $eligibility;
                                }
                                ?>
                            </td>
                            <td class="<?php echo !empty($result['student_offense']) ? 'offense-cell' : ''; ?>">
                                <?php 
                                $offense = htmlspecialchars($result['student_offense'] ?? '-');
                                if (!empty($result['student_offense'])) {
                                    $offenseLabels = [
                                        'cheating' => 'Cheating',
                                        'misconduct' => 'Misconduct',
                                        'absent_without_excuse' => 'Absent Without Excuse',
                                        'other' => 'Other'
                                    ];
                                    $offenseText = $offenseLabels[$result['student_offense']] ?? ucfirst($result['student_offense']);
                                    echo '<span style="color: #dc3545; font-weight: 600;">' . htmlspecialchars($offenseText) . '</span>';
                                } else {
                                    echo $offense;
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($result['attempt'] ?? '1'); ?></td>
                            <?php 
                            $isNotEligible = (($result['eligibility'] ?? '') == 'not_eligible') || !empty($result['student_offense']);
                            ?>
                            <td>
                                <?php if ($isNotEligible): ?>
                                    <span style="color: #dc3545; font-weight: 600;">NE</span>
                                <?php elseif (in_array($_SESSION['role'] ?? '', ['admin','teacher'])): ?>
                                    <input type="number" 
                                           class="marks-input assessment-marks" 
                                           data-result-id="<?php echo $result['id']; ?>"
                                           data-assessment-percent="<?php echo $result['assessment_percentage'] ?? 0; ?>"
                                           data-final-exam-percent="<?php echo $result['final_exam_percentage'] ?? 0; ?>"
                                           step="0.01" min="0" max="100" 
                                           value="<?php echo number_format($result['assessment_marks'] ?? 0, 2); ?>"
                                           oninput="updateMarksLive(this)">
                                <?php else: ?>
                                    <?php echo number_format($result['assessment_marks'] ?? 0, 2); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isNotEligible): ?>
                                    <span style="color: #dc3545; font-weight: 600;">NE</span>
                                <?php elseif (in_array($_SESSION['role'] ?? '', ['admin','teacher'])): ?>
                                    <input type="number" 
                                           class="marks-input final-exam-marks" 
                                           data-result-id="<?php echo $result['id']; ?>"
                                           data-assessment-percent="<?php echo $result['assessment_percentage'] ?? 0; ?>"
                                           data-final-exam-percent="<?php echo $result['final_exam_percentage'] ?? 0; ?>"
                                           step="0.01" min="0" max="100" 
                                           value="<?php echo number_format($result['final_exam_marks'] ?? 0, 2); ?>"
                                           oninput="updateMarksLive(this)">
                                <?php else: ?>
                                    <?php echo number_format($result['final_exam_marks'] ?? 0, 2); ?>
                                <?php endif; ?>
                            </td>
                           
                            <td class="assessment-percentage-marks-cell" data-result-id="<?php echo $result['id']; ?>">
                                <?php 
                                if ($isNotEligible) {
                                    echo '<span style="color: #dc3545; font-weight: 600;">NE</span>';
                                } else {
                                    $assess_contrib = ($result['assessment_marks'] ?? 0) * (($result['assessment_percentage'] ?? 0) / 100);
                                    echo number_format($assess_contrib, 2);
                                }
                                ?>
                            </td>
                            <td class="final-exam-percentage-marks-cell" data-result-id="<?php echo $result['id']; ?>">
                                <?php 
                                if ($isNotEligible) {
                                    echo '<span style="color: #dc3545; font-weight: 600;">NE</span>';
                                } else {
                                    $final_contrib = ($result['final_exam_marks'] ?? 0) * (($result['final_exam_percentage'] ?? 0) / 100);
                                    echo number_format($final_contrib, 2);
                                }
                                ?>
                            </td>
                            <?php 
                            if ($isNotEligible) {
                                $total_percentage = 0;
                                $assess_contrib = 0;
                                $final_contrib = 0;
                            } else {
                                $assess_contrib = ($result['assessment_marks'] ?? 0) * (($result['assessment_percentage'] ?? 0) / 100);
                                $final_contrib = ($result['final_exam_marks'] ?? 0) * (($result['final_exam_percentage'] ?? 0) / 100);
                                $total_percentage = $assess_contrib + $final_contrib;
                            }
                            $currentStatus = $result['status'] ?? 'absent';
                            $final_exam_marks = $result['final_exam_marks'] ?? 0;
                            $grade = ($currentStatus === 'absent' || $isNotEligible) ? '-' : getGradeFromPercentage($total_percentage, $final_exam_marks);
                            ?>
                            <td class="total-percentage-cell" data-result-id="<?php echo $result['id']; ?>">
                                <?php 
                                if ($isNotEligible) {
                                    echo '<span style="color: #dc3545; font-weight: 600;">NE</span>';
                                } else {
                                    echo number_format($total_percentage, 2);
                                }
                                ?>
                            </td>
                            <td class="grade-cell" data-result-id="<?php echo $result['id']; ?>">
                                <?php echo htmlspecialchars($grade); ?>
                            </td>
                            
                            <td>
                                <span class="status-cell status-<?php echo htmlspecialchars($currentStatus); ?>" data-result-id="<?php echo $result['id']; ?>">
                                    <?php echo ucfirst(htmlspecialchars($currentStatus)); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <?php if (in_array($_SESSION['role'] ?? '', ['admin','teacher'])): ?>
                                        <a href="index.php?action=exam_results&sub=edit&id=<?php echo $result['id']; ?>" class="btn-edit">Edit</a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role']=='admin'): ?>
                                        <a href="index.php?action=exam_results&sub=delete&id=<?php echo $result['id']; ?>" class="btn-delete" data-delete="this result">Delete</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
let updateTimeout = {};

function validateAndClamp(input, min, max) {
    let value = parseFloat(input.value) || 0;
    if (value < min) {
        value = min;
        input.value = min;
    } else if (value > max) {
        value = max;
        input.value = max;
    }
    return value;
}

function updateMarksLive(input) {
    // Validate and clamp value
    const value = validateAndClamp(input, 0, 100);
    
    const resultId = input.getAttribute('data-result-id');
    const row = input.closest('tr');
    const assessmentPercent = parseFloat(input.getAttribute('data-assessment-percent')) || 0;
    const finalExamPercent = parseFloat(input.getAttribute('data-final-exam-percent')) || 0;
    
    // Get both marks inputs
    const assessmentInput = row.querySelector('.assessment-marks');
    const finalExamInput = row.querySelector('.final-exam-marks');
    
    const assessmentMarks = parseFloat(assessmentInput.value) || 0;
    const finalExamMarks = parseFloat(finalExamInput.value) || 0;
    
    // Calculate final marks
    const finalMarks = assessmentMarks + finalExamMarks;
    
    // Calculate weighted percentage for status
    const assessmentContribution = (assessmentMarks * assessmentPercent) / 100;
    const finalExamContribution = (finalExamMarks * finalExamPercent) / 100;
    const weightedPercentage = assessmentContribution + finalExamContribution;
    
    // Update Assessment Percentage display
    const assessmentPercentageCell = row.querySelector('.assessment-percentage-marks-cell');
    if (assessmentPercentageCell) {
        assessmentPercentageCell.textContent = assessmentContribution.toFixed(2);
    }
    
    // Update Final Exam Percentage display
    const finalExamPercentageCell = row.querySelector('.final-exam-percentage-marks-cell');
    if (finalExamPercentageCell) {
        finalExamPercentageCell.textContent = finalExamContribution.toFixed(2);
    }
    
    // Update Total Percentage display
    const totalPercentageCell = row.querySelector('.total-percentage-cell');
    if (totalPercentageCell) {
        totalPercentageCell.textContent = weightedPercentage.toFixed(2);
    }
    
    // Update Grade display (client-side only, for instant feedback)
    const gradeCell = row.querySelector('.grade-cell');
    if (gradeCell) {
        let grade = '-';
        if (!(finalExamMarks === 0 && assessmentMarks === 0)) {
            // Special rule: If final exam marks < 40 but total percentage >= 40, grade is C-
            if (finalExamMarks < 40 && weightedPercentage >= 40) {
                grade = 'C-';
            } else if (weightedPercentage >= 85) {
                grade = 'A+';
            } else if (weightedPercentage >= 80) {
                grade = 'A';
            } else if (weightedPercentage >= 75) {
                grade = 'A-';
            } else if (weightedPercentage >= 70) {
                grade = 'B+';
            } else if (weightedPercentage >= 65) {
                grade = 'B';
            } else if (weightedPercentage >= 60) {
                grade = 'B-';
            } else if (weightedPercentage >= 50) {
                grade = 'C+';
            } else if (weightedPercentage >= 40) {
                grade = 'C';
            } else if (weightedPercentage >= 30) {
                grade = 'C-';
            } else if (weightedPercentage >= 20) {
                grade = 'D';
            } else {
                grade = 'F';
            }
        }
        gradeCell.textContent = grade;
    }
    
    // Update Status
    // Status rules: 
    // - If grade is C- or below (C-, D, F), status is fail
    // - If grade is C or above, status is pass
    // - If both marks are 0, status is absent
    const statusCell = row.querySelector('.status-cell');
    if (statusCell) {
        let status = 'absent';
        let statusClass = 'status-absent';

        if (finalExamMarks === 0 && assessmentMarks === 0) {
            status = 'absent';
            statusClass = 'status-absent';
        } else {
            // Determine grade first
            let grade = '';
            if (finalExamMarks < 40 && weightedPercentage >= 40) {
                grade = 'C-';
            } else if (weightedPercentage >= 85) {
                grade = 'A+';
            } else if (weightedPercentage >= 80) {
                grade = 'A';
            } else if (weightedPercentage >= 75) {
                grade = 'A-';
            } else if (weightedPercentage >= 70) {
                grade = 'B+';
            } else if (weightedPercentage >= 65) {
                grade = 'B';
            } else if (weightedPercentage >= 60) {
                grade = 'B-';
            } else if (weightedPercentage >= 50) {
                grade = 'C+';
            } else if (weightedPercentage >= 40) {
                grade = 'C';
            } else if (weightedPercentage >= 30) {
                grade = 'C-';
            } else if (weightedPercentage >= 20) {
                grade = 'D';
            } else {
                grade = 'F';
            }
            
            // If grade is C- or below, status is fail
            if (['C-', 'D', 'F'].includes(grade)) {
                status = 'fail';
                statusClass = 'status-fail';
            } else {
                status = 'pass';
                statusClass = 'status-pass';
            }
        }
        
        statusCell.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        statusCell.className = 'status-cell ' + statusClass;
    }
    
    // Debounce: Save to database after user stops typing (500ms delay)
    clearTimeout(updateTimeout[resultId]);
    updateTimeout[resultId] = setTimeout(() => {
        saveMarksToDatabase(resultId, assessmentMarks, finalExamMarks, finalMarks, weightedPercentage);
    }, 500);
}

function saveMarksToDatabase(resultId, assessmentMarks, finalExamMarks, finalMarks, weightedPercentage) {
    // Determine status: 
    // - If grade is C- or below (C-, D, F), status is fail
    // - If grade is C or above, status is pass
    // - If both marks are 0, status is absent
    let status = 'absent';
    if (finalExamMarks === 0 && assessmentMarks === 0) {
        status = 'absent';
    } else {
        // Determine grade first
        let grade = '';
        if (finalExamMarks < 40 && weightedPercentage >= 40) {
            grade = 'C-';
        } else if (weightedPercentage >= 85) {
            grade = 'A+';
        } else if (weightedPercentage >= 80) {
            grade = 'A';
        } else if (weightedPercentage >= 75) {
            grade = 'A-';
        } else if (weightedPercentage >= 70) {
            grade = 'B+';
        } else if (weightedPercentage >= 65) {
            grade = 'B';
        } else if (weightedPercentage >= 60) {
            grade = 'B-';
        } else if (weightedPercentage >= 50) {
            grade = 'C+';
        } else if (weightedPercentage >= 40) {
            grade = 'C';
        } else if (weightedPercentage >= 30) {
            grade = 'C-';
        } else if (weightedPercentage >= 20) {
            grade = 'D';
        } else {
            grade = 'F';
        }
        
        // If grade is C- or below, status is fail
        if (['C-', 'D', 'F'].includes(grade)) {
            status = 'fail';
        } else {
            status = 'pass';
        }
    }
    
    const formData = new FormData();
    formData.append('result_id', resultId);
    formData.append('assessment_marks', assessmentMarks);
    formData.append('final_exam_marks', finalExamMarks);
    formData.append('final_marks', finalMarks);
    formData.append('status', status);
    
    fetch('index.php?action=exam_results&sub=updateMarks', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Optionally show a subtle success indicator
            const row = document.querySelector(`[data-result-id="${resultId}"]`)?.closest('tr');
            if (row) {
                row.style.backgroundColor = '#d4edda';
                setTimeout(() => {
                    row.style.backgroundColor = '';
                }, 500);
            }
        } else {
            console.error('Error updating marks:', data.error);
            alert('Error updating marks: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving marks. Please try again.');
    });
}
</script>
