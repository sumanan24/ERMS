<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
    .main-content { margin-left:280px; margin-top:70px; padding:30px; background:white; min-height:calc(100vh - 70px); overflow:auto; }
    .form-container { max-width:700px; background:white; padding:30px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
    .form-group { margin-bottom:18px; }
    .form-group label { display:block; margin-bottom:6px; color:#333; font-weight:500; }
    .form-group input, .form-group select { width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; }
    .form-row { display:flex; gap:15px; }
    .form-row .form-group { flex:1; }
    .btn-submit { padding:12px 24px; background:#667eea; color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer; }
    .btn-cancel { padding:12px 24px; background:#6c757d; color:white; text-decoration:none; border-radius:8px; font-weight:600; margin-left:8px; }
    .info-box { background:#f8f9fa; padding:15px; border-radius:8px; margin-bottom:20px; }
    .percentage-warning { color:#dc3545; font-size:12px; margin-top:5px; }
    .percentage-success { color:#28a745; font-size:12px; margin-top:5px; }
</style>

<div class="main-content">
    <div class="form-container">
        <h1 style="margin-bottom: 20px;">Edit Exam Result</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=exam_results&sub=edit&id=<?php echo $result->id; ?>" id="resultForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="eligibility">Eligibility *</label>
                    <select id="eligibility" name="eligibility" required onchange="handleEligibilityChange()" 
                            style="<?php echo (($result->eligibility ?? 'eligible') == 'not_eligible') ? 'border-color: #dc3545; color: #dc3545; font-weight: 600;' : ''; ?>">
                        <option value="eligible" <?php echo (($result->eligibility ?? 'eligible') == 'eligible')?'selected':''; ?>>Eligible</option>
                        <option value="not_eligible" style="color: #dc3545; font-weight: 600;" <?php echo (($result->eligibility ?? '') == 'not_eligible')?'selected':''; ?>>Not Eligible</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="student_offense">Student Offense</label>
                    <select id="student_offense" name="student_offense" onchange="handleOffenseChange()"
                            style="<?php echo !empty($result->student_offense) ? 'border-color: #dc3545; color: #dc3545; font-weight: 600;' : ''; ?>">
                        <option value="">-- No Offense --</option>
                        <option value="cheating" style="color: #dc3545; font-weight: 600;" <?php echo (($result->student_offense ?? '') == 'cheating')?'selected':''; ?>>Cheating</option>
                        <option value="misconduct" style="color: #dc3545; font-weight: 600;" <?php echo (($result->student_offense ?? '') == 'misconduct')?'selected':''; ?>>Misconduct</option>
                        <option value="absent_without_excuse" style="color: #dc3545; font-weight: 600;" <?php echo (($result->student_offense ?? '') == 'absent_without_excuse')?'selected':''; ?>>Absent Without Excuse</option>
                        <option value="other" style="color: #dc3545; font-weight: 600;" <?php echo (!empty($result->student_offense) && !in_array($result->student_offense, ['cheating', 'misconduct', 'absent_without_excuse']))?'selected':''; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="attempt">Attempt *</label>
                    <input type="number" id="attempt" name="attempt" value="<?php echo htmlspecialchars($result->attempt ?? 1); ?>" min="1" max="5" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="assessment_marks">Assessment Marks (0-100) *</label>
                    <?php 
                    $hasOffense = !empty($result->student_offense) || ($result->eligibility ?? 'eligible') == 'not_eligible';
                    if ($hasOffense): ?>
                        <input type="text" id="assessment_marks" name="assessment_marks" value="NE" 
                               readonly style="background:#f5f5f5; cursor:not-allowed;">
                    <?php else: ?>
                        <input type="number" id="assessment_marks" name="assessment_marks" step="0.01" min="0" max="100" 
                               value="<?php echo htmlspecialchars($result->assessment_marks ?? 0); ?>" 
                               required oninput="validateAndUpdate(this, 0, 100); calculateTotals();">
                    <?php endif; ?>
                    <small style="color:#666;"><?php echo $hasOffense ? 'NE = Not Eligible' : 'Marks out of 100%'; ?></small>
                </div>
                <div class="form-group">
                    <label for="final_exam_marks">Final Exam Marks (0-100) *</label>
                    <?php if ($hasOffense): ?>
                        <input type="text" id="final_exam_marks" name="final_exam_marks" value="NE" 
                               readonly style="background:#f5f5f5; cursor:not-allowed;">
                    <?php else: ?>
                        <input type="number" id="final_exam_marks" name="final_exam_marks" step="0.01" min="0" max="100" 
                               value="<?php echo htmlspecialchars($result->final_exam_marks ?? 0); ?>" 
                               required oninput="validateAndUpdate(this, 0, 100); calculateTotals();">
                    <?php endif; ?>
                    <small style="color:#666;"><?php echo $hasOffense ? 'NE = Not Eligible' : 'Marks out of 100%'; ?></small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Assessment Percentage (from Exam)</label>
                    <input type="text" value="<?php echo htmlspecialchars($examPercentages['assessment_percentage'] ?? 0); ?>%" readonly 
                           style="background:#f5f5f5; cursor:not-allowed;">
                    <small style="color: #666;">Set in Exam Schedule</small>
                </div>
                <div class="form-group">
                    <label>Final Exam Percentage (from Exam)</label>
                    <input type="text" value="<?php echo htmlspecialchars($examPercentages['final_exam_percentage'] ?? 0); ?>%" readonly 
                           style="background:#f5f5f5; cursor:not-allowed;">
                    <small style="color: #666;">Set in Exam Schedule</small>
                </div>
            </div>

            <div class="info-box">
                <strong>Calculated Values:</strong><br>
                Assessment Percentage Marks: <span id="assessment_percentage_marks_display"><?php 
                    if ($hasOffense) {
                        echo 'NE';
                    } else {
                        $assessment_contribution = ($result->assessment_marks ?? 0) * (($examPercentages['assessment_percentage'] ?? 0) / 100);
                        echo number_format($assessment_contribution, 2);
                    }
                ?></span><br>
                Final Exam Percentage Marks: <span id="final_exam_percentage_marks_display"><?php 
                    if ($hasOffense) {
                        echo 'NE';
                    } else {
                        $final_exam_contribution = ($result->final_exam_marks ?? 0) * (($examPercentages['final_exam_percentage'] ?? 0) / 100);
                        echo number_format($final_exam_contribution, 2);
                    }
                ?></span><br>
                Total Marks: <span id="final_marks_display"><?php 
                    if ($hasOffense) {
                        echo 'NE';
                    } else {
                        echo number_format($result->final_marks ?? 0, 2);
                    }
                ?></span><br>
                Total Percentage: <span id="total_percentage_display"><?php 
                    if ($hasOffense) {
                        echo 'NE';
                    } else {
                        $total_percentage = ($result->assessment_marks ?? 0) * (($examPercentages['assessment_percentage'] ?? 0) / 100) + ($result->final_exam_marks ?? 0) * (($examPercentages['final_exam_percentage'] ?? 0) / 100);
                        echo number_format($total_percentage, 2) . '%';
                    }
                ?></span>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn-submit">Update Result</button>
                <a href="index.php?action=exam_results&filter_exam=<?php echo (int)($result->exam_id ?? 0); ?>" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function validateAndUpdate(input, min, max) {
    let value = parseFloat(input.value) || 0;
    
    // Clamp value between min and max
    if (value < min) {
        value = min;
        input.value = min;
    } else if (value > max) {
        value = max;
        input.value = max;
    }
    
    // Update the input value if it was clamped
    if (input.value !== '' && parseFloat(input.value) !== value) {
        input.value = value;
    }
}

// Get percentages from exam (stored in hidden fields or from exam data)
const assessmentPercent = <?php echo $examPercentages['assessment_percentage'] ?? 0; ?>;
const finalExamPercent = <?php echo $examPercentages['final_exam_percentage'] ?? 0; ?>;

function calculateTotals() {
    const assessmentMarks = parseFloat(document.getElementById('assessment_marks').value) || 0;
    const finalExamMarks = parseFloat(document.getElementById('final_exam_marks').value) || 0;
    
    // Ensure values are within 0-100 range
    const clampedAssessment = Math.max(0, Math.min(100, assessmentMarks));
    const clampedFinalExam = Math.max(0, Math.min(100, finalExamMarks));
    
    // Calculate percentage contributions using exam percentages
    const assessmentPercentageMarks = (clampedAssessment * assessmentPercent) / 100;
    const finalExamPercentageMarks = (clampedFinalExam * finalExamPercent) / 100;
    
    // Final marks = sum of both marks
    const finalMarks = clampedAssessment + clampedFinalExam;
    
    // Total percentage = sum of percentage contributions
    const totalPercentage = assessmentPercentageMarks + finalExamPercentageMarks;
    
    // Update displays
    document.getElementById('assessment_percentage_marks_display').textContent = assessmentPercentageMarks.toFixed(2);
    document.getElementById('final_exam_percentage_marks_display').textContent = finalExamPercentageMarks.toFixed(2);
    document.getElementById('final_marks_display').textContent = finalMarks.toFixed(2);
    document.getElementById('total_percentage_display').textContent = totalPercentage.toFixed(2) + '%';
}

// Handle eligibility change
function handleEligibilityChange() {
    const eligibilitySelect = document.getElementById('eligibility');
    const hasOffense = eligibilitySelect.value === 'not_eligible' || document.getElementById('student_offense').value !== '';
    
    // Update styling for not eligible
    if (eligibilitySelect.value === 'not_eligible') {
        eligibilitySelect.style.borderColor = '#dc3545';
        eligibilitySelect.style.color = '#dc3545';
        eligibilitySelect.style.fontWeight = '600';
    } else {
        eligibilitySelect.style.borderColor = '';
        eligibilitySelect.style.color = '';
        eligibilitySelect.style.fontWeight = '';
    }
    
    updateMarksFields(hasOffense);
}

// Handle offense change
function handleOffenseChange() {
    const offenseSelect = document.getElementById('student_offense');
    const eligibilitySelect = document.getElementById('eligibility');
    const hasOffense = offenseSelect.value !== '' || eligibilitySelect.value === 'not_eligible';
    
    // If offense selected, automatically set eligibility to not_eligible
    if (offenseSelect.value !== '') {
        eligibilitySelect.value = 'not_eligible';
        eligibilitySelect.style.borderColor = '#dc3545';
        eligibilitySelect.style.color = '#dc3545';
        eligibilitySelect.style.fontWeight = '600';
    }
    
    // Update styling for offense
    if (offenseSelect.value !== '') {
        offenseSelect.style.borderColor = '#dc3545';
        offenseSelect.style.color = '#dc3545';
        offenseSelect.style.fontWeight = '600';
    } else {
        offenseSelect.style.borderColor = '';
        offenseSelect.style.color = '';
        offenseSelect.style.fontWeight = '';
    }
    
    updateMarksFields(hasOffense);
}

// Update marks fields based on offense/eligibility status
function updateMarksFields(hasOffense) {
    const assessmentInput = document.getElementById('assessment_marks');
    const finalExamInput = document.getElementById('final_exam_marks');
    
    if (hasOffense) {
        // Set to NE and make readonly
        assessmentInput.type = 'text';
        assessmentInput.value = 'NE';
        assessmentInput.readOnly = true;
        assessmentInput.style.background = '#f5f5f5';
        assessmentInput.style.cursor = 'not-allowed';
        assessmentInput.removeAttribute('required');
        
        finalExamInput.type = 'text';
        finalExamInput.value = 'NE';
        finalExamInput.readOnly = true;
        finalExamInput.style.background = '#f5f5f5';
        finalExamInput.style.cursor = 'not-allowed';
        finalExamInput.removeAttribute('required');
        
        // Update displays
        document.getElementById('assessment_percentage_marks_display').textContent = 'NE';
        document.getElementById('final_exam_percentage_marks_display').textContent = 'NE';
        document.getElementById('final_marks_display').textContent = 'NE';
        document.getElementById('total_percentage_display').textContent = 'NE';
    } else {
        // Enable inputs and set to current value or 0
        const currentAssessment = assessmentInput.value === 'NE' ? '0' : assessmentInput.value;
        const currentFinalExam = finalExamInput.value === 'NE' ? '0' : finalExamInput.value;
        
        assessmentInput.type = 'number';
        assessmentInput.value = currentAssessment;
        assessmentInput.readOnly = false;
        assessmentInput.style.background = '';
        assessmentInput.style.cursor = '';
        assessmentInput.required = true;
        assessmentInput.setAttribute('oninput', 'validateAndUpdate(this, 0, 100); calculateTotals();');
        
        finalExamInput.type = 'number';
        finalExamInput.value = currentFinalExam;
        finalExamInput.readOnly = false;
        finalExamInput.style.background = '';
        finalExamInput.style.cursor = '';
        finalExamInput.required = true;
        finalExamInput.setAttribute('oninput', 'validateAndUpdate(this, 0, 100); calculateTotals();');
        
        calculateTotals();
    }
}

// Initial styling check
document.addEventListener('DOMContentLoaded', function() {
    const eligibilitySelect = document.getElementById('eligibility');
    const offenseSelect = document.getElementById('student_offense');
    
    // Apply styling if not eligible
    if (eligibilitySelect.value === 'not_eligible') {
        eligibilitySelect.style.borderColor = '#dc3545';
        eligibilitySelect.style.color = '#dc3545';
        eligibilitySelect.style.fontWeight = '600';
    }
    
    // Apply styling if offense exists
    if (offenseSelect.value !== '') {
        offenseSelect.style.borderColor = '#dc3545';
        offenseSelect.style.color = '#dc3545';
        offenseSelect.style.fontWeight = '600';
    }
});

// Initial calculation
if (document.getElementById('student_offense').value === '') {
    calculateTotals();
}
</script>
