<?php
// Partial: schedule table only (for AJAX load). Expects: $modules, $existingExams, $selectedCourse, $selectedVersion, $selectedBatch, $selectedSemester
if (!empty($modules)): ?>
<form method="POST" action="index.php?action=exams&sub=schedule">
    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($selectedCourse); ?>">
    <input type="hidden" name="version_id" value="<?php echo htmlspecialchars($selectedVersion); ?>">
    <input type="hidden" name="batch_id" value="<?php echo htmlspecialchars($selectedBatch ?? ''); ?>">
    <input type="hidden" name="semester" value="<?php echo htmlspecialchars($selectedSemester ?? ''); ?>">

    <table style="width:100%;">
        <thead>
            <tr>
                <th style="width:15%;">Module</th>
                <th style="width:12%;">Exam Date</th>
                <th style="width:12%;">Time Slot</th>
                <th style="width:15%;">Location</th>
                <th style="width:12%;">Assessment %</th>
                <th style="width:12%;">Final Exam %</th>
                <th style="width:10%;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($modules as $module):
                $moduleId = $module['id'];
                $exam = $existingExams[$moduleId] ?? null;
            ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($module['mcode']); ?></strong><br>
                        <small><?php echo htmlspecialchars($module['mname']); ?></small>
                    </td>
                    <td>
                        <input type="date" name="modules[<?php echo $moduleId; ?>][exam_date]"
                               value="<?php echo htmlspecialchars($exam['exam_date'] ?? ''); ?>"
                               style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </td>
                    <td>
                        <input type="text" name="modules[<?php echo $moduleId; ?>][time_slot]"
                               placeholder="01.00-4.00"
                               value="<?php echo htmlspecialchars($exam['time_slot'] ?? ''); ?>"
                               style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </td>
                    <td>
                        <input type="text" name="modules[<?php echo $moduleId; ?>][location]"
                               placeholder="hall 01"
                               value="<?php echo htmlspecialchars($exam['location'] ?? ''); ?>"
                               style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </td>
                    <td>
                        <input type="number" name="modules[<?php echo $moduleId; ?>][assessment_percentage]"
                               step="0.01" min="0" max="100"
                               placeholder="30"
                               value="<?php echo htmlspecialchars($exam['assessment_percentage'] ?? ''); ?>"
                               class="assessment-percentage"
                               data-module="<?php echo $moduleId; ?>"
                               oninput="validatePercentages(<?php echo $moduleId; ?>)"
                               style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </td>
                    <td>
                        <input type="number" name="modules[<?php echo $moduleId; ?>][final_exam_percentage]"
                               step="0.01" min="0" max="100"
                               placeholder="70"
                               value="<?php echo htmlspecialchars($exam['final_exam_percentage'] ?? ''); ?>"
                               class="final-exam-percentage"
                               data-module="<?php echo $moduleId; ?>"
                               oninput="validatePercentages(<?php echo $moduleId; ?>)"
                               style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </td>
                    <td>
                        <span id="total-<?php echo $moduleId; ?>" style="font-weight:bold; color:#666;">-</span>
                        <div id="warning-<?php echo $moduleId; ?>" style="display:none; color:#dc3545; font-size:10px; margin-top:2px;">&#9888;</div>
                        <div id="success-<?php echo $moduleId; ?>" style="display:none; color:#28a745; font-size:10px; margin-top:2px;">&#10003;</div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top:20px; display:flex; gap:10px;">
        <button type="submit" class="btn-primary">Save Schedule</button>
        <a href="index.php?action=exams" class="btn-cancel">Back to List</a>
    </div>
</form>
<?php else: ?>
<p>No modules found for this course, version and semester.</p>
<?php endif; ?>
