<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
    .main-content { margin-left: 280px; margin-top: 70px; padding: 30px; background: white; min-height: calc(100vh - 70px); overflow: auto; }
    .filter-container { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    .form-row { display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap; }
    .form-group { flex: 1; min-width: 200px; }
    .form-group label { display: block; margin-bottom: 6px; font-weight: 500; }
    .form-group select { width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px; }
    .form-group select:disabled { background: #f5f5f5; cursor: not-allowed; }
    .students-list-container { background: white; border: 2px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-top: 20px; }
    .students-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; max-height: 400px; overflow-y: auto; }
    .student-card { border: 2px solid #e0e0e0; border-radius: 8px; padding: 15px; cursor: pointer; transition: all 0.3s ease; }
    .student-card:hover { border-color: #667eea; background: #f8f9ff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15); }
    .student-card.selected { border-color: #667eea; background: #f0f4ff; }
    .student-card input[type="radio"] { margin-right: 10px; }
    .student-card label { cursor: pointer; display: flex; align-items: center; margin: 0; }
    .student-info { flex: 1; }
    .student-reg { font-weight: 600; color: #333; margin-bottom: 5px; }
    .student-name { color: #666; font-size: 14px; }
    .btn-print { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; margin-top: 15px; text-decoration: none; display: inline-block; }
    .btn-print:disabled { background: #ccc; cursor: not-allowed; }
    .no-students { text-align: center; padding: 40px; color: #999; }
</style>

<div class="main-content">
    <h1 style="margin-bottom: 20px;">Print Transcripts</h1>
    
    <div class="filter-container">
        <div class="form-row">
            <div class="form-group">
                <label for="course_id">Select Course *</label>
                <select id="course_id" name="course_id" required>
                    <option value="">-- Select Course --</option>
                    <?php
                    try {
                        require_once __DIR__ . '/../../models/Course.php';
                        $courseModel = new Course();
                        $courses = $courseModel->getAllCourses();
                        if (!empty($courses)):
                            foreach($courses as $course):
                    ?>
                        <option value="<?php echo $course['id']; ?>">
                            <?php echo htmlspecialchars($course['cname']); ?>
                        </option>
                    <?php 
                            endforeach;
                        else:
                    ?>
                        <option value="">No courses available</option>
                    <?php
                        endif;
                    } catch (Exception $e) {
                    ?>
                        <option value="">Error loading courses</option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="version_id">Select Version *</label>
                <select id="version_id" name="version_id" required disabled>
                    <option value="">-- Select Course First --</option>
                </select>
            </div>
            <div class="form-group">
                <label for="batch_id">Select Batch *</label>
                <select id="batch_id" name="batch_id" required disabled>
                    <option value="">-- Select Version First --</option>
                </select>
            </div>
        </div>
    </div>

    <div class="students-list-container" id="studentsContainer" style="display: none;">
        <h3 style="margin-bottom: 15px;">Select Student</h3>
        <div class="students-list" id="studentsList">
            <div class="no-students">Select course, version, and batch to view students</div>
        </div>
        <a href="#" id="printBtn" class="btn-print" style="display: none;" target="_blank">🖨️ Print Transcript</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('course_id');
    const versionSelect = document.getElementById('version_id');
    const batchSelect = document.getElementById('batch_id');
    const studentsList = document.getElementById('studentsList');
    const studentsContainer = document.getElementById('studentsContainer');
    const printBtn = document.getElementById('printBtn');
    let selectedStudentId = null;

    // Load versions when course is selected
    courseSelect.addEventListener('change', function() {
        const courseId = this.value;
        
        // Reset version and batch
        versionSelect.innerHTML = '<option value="">-- Select Version --</option>';
        versionSelect.disabled = true;
        batchSelect.innerHTML = '<option value="">-- Select Version First --</option>';
        batchSelect.disabled = true;
        studentsContainer.style.display = 'none';
        printBtn.style.display = 'none';
        
        if (courseId) {
            versionSelect.disabled = false;
            versionSelect.innerHTML = '<option value="">Loading versions...</option>';
            
            fetch(`index.php?action=students&sub=getVersions&course_id=${courseId}`)
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
        }
    });

    // Load batches when version is selected
    versionSelect.addEventListener('change', function() {
        const versionId = this.value;
        
        // Reset batch
        batchSelect.innerHTML = '<option value="">-- Select Batch --</option>';
        batchSelect.disabled = true;
        studentsContainer.style.display = 'none';
        printBtn.style.display = 'none';
        
        if (versionId) {
            batchSelect.disabled = false;
            batchSelect.innerHTML = '<option value="">Loading batches...</option>';
            
            fetch(`index.php?action=students&sub=getBatchesByVersion&version_id=${versionId}`)
                .then(response => response.json())
                .then(batches => {
                    batchSelect.innerHTML = '<option value="">-- Select Batch --</option>';
                    batches.forEach(batch => {
                        const option = document.createElement('option');
                        option.value = batch.id;
                        option.textContent = batch.batch_no;
                        batchSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading batches:', error);
                    batchSelect.innerHTML = '<option value="">Error loading batches</option>';
                });
        }
    });

    // Load students when batch is selected
    batchSelect.addEventListener('change', function() {
        const batchId = this.value;
        const versionId = versionSelect.value;
        const courseId = courseSelect.value;
        
        studentsContainer.style.display = 'none';
        printBtn.style.display = 'none';
        selectedStudentId = null;
        
        if (batchId && versionId && courseId) {
            studentsList.innerHTML = '<div class="no-students">Loading students...</div>';
            studentsContainer.style.display = 'block';
            
            fetch(`index.php?action=students&sub=getStudentsByBatch&batch_id=${batchId}&version_id=${versionId}&course_id=${courseId}`)
                .then(response => response.json())
                .then(students => {
                    if (students.length === 0) {
                        studentsList.innerHTML = '<div class="no-students">No students found in this batch</div>';
                    } else {
                        studentsList.innerHTML = students.map(student => `
                            <div class="student-card">
                                <label>
                                    <input type="radio" name="student" value="${student.id}" onchange="selectStudent(${student.id})">
                                    <div class="student-info">
                                        <div class="student-reg">${student.reg_no || ''}</div>
                                        <div class="student-name">${student.fullname || ''}</div>
                                    </div>
                                </label>
                            </div>
                        `).join('');
                    }
                })
                .catch(error => {
                    console.error('Error loading students:', error);
                    studentsList.innerHTML = '<div class="no-students">Error loading students</div>';
                });
        }
    });

    // Function to handle student selection
    window.selectStudent = function(studentId) {
        selectedStudentId = studentId;
        printBtn.style.display = 'inline-block';
        printBtn.href = `index.php?action=exam_results&sub=printTranscriptsView&student_id=${studentId}`;
        
        // Update card styling
        document.querySelectorAll('.student-card').forEach(card => {
            card.classList.remove('selected');
        });
        event.target.closest('.student-card').classList.add('selected');
    };
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

