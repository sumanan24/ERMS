<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
    .main-content { margin-left:280px; margin-top:70px; padding:30px; background:white; min-height:calc(100vh - 70px); overflow:auto; }
    .form-container { max-width:600px; background:white; padding:30px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
    .form-group { margin-bottom:18px; }
    .form-group label { display:block; margin-bottom:6px; color:#333; font-weight:500; }
    .form-group input, .form-group select { width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; }
    .btn-submit { padding:12px 24px; background:#667eea; color:white; border:none; border-radius:8px; font-weight:600; }
    .btn-cancel { padding:12px 24px; background:#6c757d; color:white; text-decoration:none; border-radius:8px; font-weight:600; margin-left:8px; }
</style>

<div class="main-content">
    <div class="form-container">
        <h1 style="margin-bottom: 20px;">Create Exam Schedule</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=exams&sub=create">
            <div class="form-group">
                <label for="exam_date">Exam Date *</label>
                <input type="date" id="exam_date" name="exam_date" required value="<?php echo htmlspecialchars($_POST['exam_date'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="time_slot">Time Slot * (e.g., 01.00-4.00 or 9.00-12.00)</label>
                <input type="text" id="time_slot" name="time_slot" required placeholder="01.00-4.00" value="<?php echo htmlspecialchars($_POST['time_slot'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="course_id">Course *</label>
                <select id="course_id" name="course_id" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo (($_POST['course_id'] ?? '')==$course['id'])?'selected':''; ?>>
                            <?php echo htmlspecialchars($course['cname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="module_id">Module *</label>
                <select id="module_id" name="module_id" required disabled>
                    <option value="">-- Select Course First --</option>
                </select>
            </div>
            <div class="form-group">
                <label for="location">Location * (e.g., hall 01, ICT LAB 01)</label>
                <input type="text" id="location" name="location" required placeholder="hall 01" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="assessment_percentage">Assessment Percentage *</label>
                <input type="number" id="assessment_percentage" name="assessment_percentage" step="0.01" min="0" max="100" 
                       required placeholder="e.g., 30" value="<?php echo htmlspecialchars($_POST['assessment_percentage'] ?? ''); ?>" 
                       oninput="validatePercentages()">
                <small style="color: #666; margin-top: 5px; display: block;">Assessment % (must total 100 with Final Exam %)</small>
            </div>
            <div class="form-group">
                <label for="final_exam_percentage">Final Exam Percentage *</label>
                <input type="number" id="final_exam_percentage" name="final_exam_percentage" step="0.01" min="0" max="100" 
                       required placeholder="e.g., 70" value="<?php echo htmlspecialchars($_POST['final_exam_percentage'] ?? ''); ?>" 
                       oninput="validatePercentages()">
                <small style="color: #666; margin-top: 5px; display: block;">Final Exam % (must total 100 with Assessment %)</small>
                <div id="percentage-warning" style="display:none; color:#dc3545; margin-top:5px; font-size:12px;">⚠ Percentages must total 100!</div>
                <div id="percentage-success" style="display:none; color:#28a745; margin-top:5px; font-size:12px;">✓ Percentages total 100</div>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn-submit">Create</button>
                <a href="index.php?action=exams" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Store initial values from POST data (if form was submitted with errors)
const initialCourseId = document.getElementById('course_id').value;
const initialModuleId = '<?php echo htmlspecialchars($_POST['module_id'] ?? '', ENT_QUOTES); ?>';

// Function to load modules for a course
function loadModules(courseId, selectedModuleId = null) {
    const moduleSelect = document.getElementById('module_id');
    if (courseId) {
        moduleSelect.disabled = false;
        moduleSelect.innerHTML = '<option value="">Loading modules...</option>';
        fetch('index.php?action=exams&sub=getModules&course_id=' + courseId)
            .then(response => response.json())
            .then(modules => {
                moduleSelect.innerHTML = '<option value="">-- Select Module --</option>';
                modules.forEach(module => {
                    const option = document.createElement('option');
                    option.value = module.id;
                    option.textContent = module.mcode + ' - ' + module.mname;
                    if (selectedModuleId && module.id == selectedModuleId) {
                        option.selected = true;
                    }
                    moduleSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading modules:', error);
                moduleSelect.innerHTML = '<option value="">Error loading modules</option>';
            });
    } else {
        moduleSelect.disabled = true;
        moduleSelect.innerHTML = '<option value="">-- Select Course First --</option>';
    }
}

// Load initial modules if course is already selected (e.g., after form validation errors)
if (initialCourseId) {
    loadModules(initialCourseId, initialModuleId);
}

// Update modules dropdown when course changes
document.getElementById('course_id')?.addEventListener('change', function() {
    const courseId = this.value;
    loadModules(courseId);
});

// Validate that percentages total 100
function validatePercentages() {
    const assessment = parseFloat(document.getElementById('assessment_percentage').value) || 0;
    const finalExam = parseFloat(document.getElementById('final_exam_percentage').value) || 0;
    const total = assessment + finalExam;
    const warning = document.getElementById('percentage-warning');
    const success = document.getElementById('percentage-success');
    
    if (assessment > 0 || finalExam > 0) {
        if (Math.abs(total - 100) > 0.01) {
            warning.style.display = 'block';
            success.style.display = 'none';
        } else {
            warning.style.display = 'none';
            success.style.display = 'block';
        }
    } else {
        warning.style.display = 'none';
        success.style.display = 'none';
    }
}
</script>
