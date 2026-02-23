<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
    .main-content { margin-left:280px; margin-top:70px; padding:30px; background:white; min-height:calc(100vh - 70px); overflow:auto; }
    .form-container { max-width:1000px; background:white; padding:30px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
    .form-group { margin-bottom:18px; }
    .form-group label { display:block; margin-bottom:6px; color:#333; font-weight:500; }
    .form-group select, .form-group input { width:100%; padding:10px 12px; border:2px solid #e0e0e0; border-radius:8px; }
    .btn-primary { padding:10px 20px; background:#667eea; color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer; text-decoration:none; }
    .btn-cancel { padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:8px; font-weight:600; }
    table { width:100%; border-collapse:collapse; margin-top:20px; }
    th, td { padding:10px; border-bottom:1px solid #f0f0f0; text-align:left; }
    thead { background:#f8f9fa; }
</style>

<div class="main-content">
    <div class="form-container">
        <h1 style="margin-bottom: 20px;">Exam Schedule by Course & Version</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <!-- Step 1: Select Course & Version -->
        <form method="GET" action="index.php" style="margin-bottom:20px;">
            <input type="hidden" name="action" value="exams">
            <input type="hidden" name="sub" value="schedule">
            <div class="row" style="display:flex; gap:15px; flex-wrap:wrap;">
                <div class="form-group" style="flex:1; min-width:220px;">
                    <label for="course_id">Course *</label>
                    <select id="course_id" name="course_id" required>
                        <option value="">-- Select Course --</option>
                        <?php foreach($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>" <?php echo ($selectedCourse == $course['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['cname']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="flex:1; min-width:220px;">
                    <label for="version_id">Course Version *</label>
                    <select id="version_id" name="version_id" required>
                        <option value="">-- Select Course First --</option>
                        <?php if (!empty($versions)): ?>
                            <?php foreach($versions as $version): ?>
                                <option value="<?php echo $version['id']; ?>" <?php echo ($selectedVersion == $version['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($version['version_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group" style="flex:1; min-width:220px;">
                    <label for="batch_id">Batch *</label>
                    <select id="batch_id" name="batch_id" required <?php echo empty($selectedVersion) ? 'disabled' : ''; ?>>
                        <option value=""><?php echo empty($selectedVersion) ? '-- Select Version First --' : '-- Select Batch --'; ?></option>
                        <?php if (!empty($batches)): ?>
                            <?php foreach($batches as $batch): ?>
                                <option value="<?php echo $batch['id']; ?>" <?php echo (isset($_GET['batch_id']) && $_GET['batch_id'] == $batch['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($batch['batch_no']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group" style="flex:1; min-width:220px;">
                    <label for="semester">Semester *</label>
                    <select id="semester" name="semester" required <?php echo empty($selectedVersion) ? 'disabled' : ''; ?>>
                        <option value=""><?php echo empty($selectedVersion) ? '-- Select Version First --' : '-- Select Semester --'; ?></option>
                        <?php if (!empty($semesters)): ?>
                            <?php foreach($semesters as $sem): ?>
                                <option value="<?php echo htmlspecialchars($sem, ENT_QUOTES); ?>" <?php echo (isset($_GET['semester']) && $_GET['semester'] === $sem) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sem); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group" style="align-self:flex-end;">
                    <button type="submit" class="btn-primary">Load Modules</button>
                </div>
            </div>
        </form>

        <div id="schedule-table-container">
        <?php if (!empty($modules)): ?>
            <!-- Step 2: Set exam date/time/location per module -->
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
                                    <div id="warning-<?php echo $moduleId; ?>" style="display:none; color:#dc3545; font-size:10px; margin-top:2px;">⚠</div>
                                    <div id="success-<?php echo $moduleId; ?>" style="display:none; color:#28a745; font-size:10px; margin-top:2px;">✓</div>
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
        <?php elseif (!empty($selectedCourse) && !empty($selectedVersion) && $selectedSemester !== ''): ?>
            <p>No modules found for this course, version and semester.</p>
        <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Validate percentages for each module
function validatePercentages(moduleId) {
    const assessment = parseFloat(document.querySelector(`input.assessment-percentage[data-module="${moduleId}"]`).value) || 0;
    const finalExam = parseFloat(document.querySelector(`input.final-exam-percentage[data-module="${moduleId}"]`).value) || 0;
    const total = assessment + finalExam;
    const totalSpan = document.getElementById(`total-${moduleId}`);
    const warning = document.getElementById(`warning-${moduleId}`);
    const success = document.getElementById(`success-${moduleId}`);
    
    totalSpan.textContent = total.toFixed(2) + '%';
    
    if (assessment > 0 || finalExam > 0) {
        if (Math.abs(total - 100) > 0.01) {
            warning.style.display = 'block';
            success.style.display = 'none';
            totalSpan.style.color = '#dc3545';
        } else {
            warning.style.display = 'none';
            success.style.display = 'block';
            totalSpan.style.color = '#28a745';
        }
    } else {
        warning.style.display = 'none';
        success.style.display = 'none';
        totalSpan.style.color = '#666';
    }
}

// Initialize percentages validation on page load
document.addEventListener('DOMContentLoaded', function() {
    const moduleInputs = document.querySelectorAll('.assessment-percentage, .final-exam-percentage');
    moduleInputs.forEach(input => {
        const moduleId = input.getAttribute('data-module');
        if (moduleId) {
            validatePercentages(moduleId);
        }
    });
});

// Dynamic load of versions when course changes (for GET form)
const courseSelect = document.getElementById('course_id');
const versionSelect = document.getElementById('version_id');
const batchSelect = document.getElementById('batch_id');
const semesterSelect = document.getElementById('semester');

if (courseSelect) {
    courseSelect.addEventListener('change', function() {
        const courseId = this.value;
        if (!courseId) {
            versionSelect.innerHTML = '<option value="">-- Select Course First --</option>';
            batchSelect.innerHTML = '<option value="">-- Select Version First --</option>';
            batchSelect.disabled = true;
            if (semesterSelect) { semesterSelect.innerHTML = '<option value="">-- Select Version First --</option>'; semesterSelect.disabled = true; }
            return;
        }

        versionSelect.innerHTML = '<option value="">Loading versions...</option>';
        batchSelect.disabled = true;
        batchSelect.innerHTML = '<option value="">-- Select Version First --</option>';
        if (semesterSelect) { semesterSelect.disabled = true; semesterSelect.innerHTML = '<option value="">-- Select Version First --</option>'; }
        
        fetch('index.php?action=exams&sub=getVersions&course_id=' + courseId)
            .then(response => response.json())
            .then(versions => {
                versionSelect.innerHTML = '<option value="">-- Select Version --</option>';
                versions.forEach(version => {
                    const option = document.createElement('option');
                    option.value = version.id;
                    option.textContent = version.version_name;
                    versionSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading versions:', error);
                versionSelect.innerHTML = '<option value="">Error loading versions</option>';
            });
    });
}

// Dynamic load of batches and semesters when version changes
if (versionSelect) {
    versionSelect.addEventListener('change', function() {
        const versionId = this.value;
        if (!versionId) {
            batchSelect.innerHTML = '<option value="">-- Select Version First --</option>';
            batchSelect.disabled = true;
            if (semesterSelect) { semesterSelect.innerHTML = '<option value="">-- Select Version First --</option>'; semesterSelect.disabled = true; }
            return;
        }

        batchSelect.disabled = false;
        batchSelect.innerHTML = '<option value="">Loading batches...</option>';
        if (semesterSelect) { semesterSelect.disabled = false; semesterSelect.innerHTML = '<option value="">Loading semesters...</option>'; }
        
        Promise.all([
            fetch('index.php?action=students&sub=getBatchesByVersion&version_id=' + versionId).then(r => r.json()),
            fetch('index.php?action=exams&sub=getSemesters&version_id=' + versionId).then(r => r.json())
        ]).then(([batches, semesters]) => {
            batchSelect.innerHTML = '<option value="">-- Select Batch --</option>';
            batches.forEach(batch => {
                const opt = document.createElement('option');
                opt.value = batch.id;
                opt.textContent = batch.batch_no;
                batchSelect.appendChild(opt);
            });
            if (semesterSelect) {
                semesterSelect.innerHTML = '<option value="">-- Select Semester --</option>';
                (semesters || []).forEach(sem => {
                    const opt = document.createElement('option');
                    opt.value = sem;
                    opt.textContent = sem;
                    semesterSelect.appendChild(opt);
                });
            }
        }).catch(error => {
            console.error('Error loading batches/semesters:', error);
            batchSelect.innerHTML = '<option value="">Error loading</option>';
            if (semesterSelect) semesterSelect.innerHTML = '<option value="">Error loading</option>';
        });
    });
}

// Load schedule table via GET (no page refresh)
function loadScheduleTable() {
    const courseId = courseSelect && courseSelect.value;
    const versionId = versionSelect && versionSelect.value;
    const batchId = batchSelect && batchSelect.value;
    const semester = semesterSelect && semesterSelect.value;
    if (!courseId || !versionId || !semester) return;
    const container = document.getElementById('schedule-table-container');
    if (!container) return;
    const params = new URLSearchParams({
        action: 'exams',
        sub: 'getScheduleTable',
        course_id: courseId,
        version_id: versionId,
        semester: semester
    });
    if (batchId) params.set('batch_id', batchId);
    container.innerHTML = '<p style="padding:20px; color:#666;">Loading...</p>';
    fetch('index.php?' + params.toString())
        .then(function(r) { return r.text(); })
        .then(function(html) {
            container.innerHTML = html;
            document.querySelectorAll('.assessment-percentage, .final-exam-percentage').forEach(function(input) {
                const mid = input.getAttribute('data-module');
                if (mid) validatePercentages(parseInt(mid, 10));
            });
            if (typeof history !== 'undefined' && history.replaceState) {
                const url = 'index.php?action=exams&sub=schedule&course_id=' + courseId + '&version_id=' + versionId + (batchId ? '&batch_id=' + batchId : '') + '&semester=' + encodeURIComponent(semester);
                history.replaceState(null, '', url);
            }
        })
        .catch(function(err) {
            container.innerHTML = '<p style="padding:20px; color:#dc3545;">Error loading table.</p>';
            console.error(err);
        });
}

// When semester or batch changes, load table via GET (no refresh)
if (semesterSelect) {
    semesterSelect.addEventListener('change', function() {
        if (courseSelect && courseSelect.value && versionSelect && versionSelect.value && this.value) loadScheduleTable();
    });
}
if (batchSelect) {
    batchSelect.addEventListener('change', function() {
        if (courseSelect && courseSelect.value && versionSelect && versionSelect.value && semesterSelect && semesterSelect.value) loadScheduleTable();
    });
}

// "Load Modules" button: load table via GET without full page refresh
var loadForm = document.querySelector('form[method="GET"]');
if (loadForm) {
    loadForm.addEventListener('submit', function(e) {
        const courseId = courseSelect && courseSelect.value;
        const versionId = versionSelect && versionSelect.value;
        const semester = semesterSelect && semesterSelect.value;
        if (courseId && versionId && semester) {
            e.preventDefault();
            loadScheduleTable();
        }
    });
}
</script>
