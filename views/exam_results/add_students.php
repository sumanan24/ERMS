<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
    .main-content { margin-left:280px; margin-top:70px; padding:30px; background:white; min-height:calc(100vh - 70px); overflow:auto; }
    .form-container { max-width:900px; background:white; padding:30px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
    .form-group { margin-bottom:18px; }
    .form-group label { display:block; margin-bottom:6px; color:#333; font-weight:500; }
    .form-group input, .form-group select { width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; }
    .btn-submit { padding:12px 24px; background:#667eea; color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer; }
    .btn-cancel { padding:12px 24px; background:#6c757d; color:white; text-decoration:none; border-radius:8px; font-weight:600; margin-left:8px; }
    .students-list { max-height:400px; overflow-y:auto; border:1px solid #e0e0e0; border-radius:8px; padding:15px; margin-top:15px; }
    .student-item { padding:10px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; gap:10px; }
    .student-item:last-child { border-bottom:none; }
    .student-item input[type="checkbox"] { width:20px; height:20px; }
    .info-box { background:#f8f9fa; padding:15px; border-radius:8px; margin-bottom:20px; }
    .percentage-warning { color:#dc3545; font-size:12px; margin-top:5px; }
    .percentage-success { color:#28a745; font-size:12px; margin-top:5px; }
</style>

<div class="main-content">
    <div class="form-container">
        <h1 style="margin-bottom: 20px;">Add Students to Exam</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="info-box">
            <strong>Exam Details:</strong><br>
            Date: <?php echo date('d.m.Y', strtotime($exam->exam_date)); ?><br>
            Time: <?php echo htmlspecialchars($exam->time_slot); ?><br>
            Location: <?php echo htmlspecialchars($exam->location); ?>
        </div>
        
        <input type="hidden" id="exam_id" value="<?php echo $exam->id; ?>">

        <form method="POST" action="index.php?action=exam_results&sub=addStudents&exam_id=<?php echo $exam->id; ?>" id="addStudentsForm">
            <div class="form-group">
                <label>Add Students By:</label>
                <div style="display:flex; gap:15px; margin-top:10px;">
                    <label style="display:flex; align-items:center; gap:5px;">
                        <input type="radio" name="add_method" value="batch" checked onchange="toggleMethod()">
                        <span>Batch</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:5px;">
                        <input type="radio" name="add_method" value="individual" onchange="toggleMethod()">
                        <span>Individual</span>
                    </label>
                </div>
            </div>

            <div id="batch-section">
                <div class="form-group">
                    <label for="batch_id">Select Batch *</label>
                    <select id="batch_id" name="batch_id" onchange="loadBatchStudents()">
                        <option value="">-- Select Batch --</option>
                        <?php if (!empty($batches)): ?>
                            <?php foreach($batches as $batch): ?>
                                <option value="<?php echo $batch['id']; ?>">
                                    <?php echo htmlspecialchars($batch['batch_no']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No batches found for this course</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div id="batch-students-list" class="students-list" style="display:none;">
                    <p>Select a batch to load students...</p>
                </div>
            </div>

            <div id="individual-section" style="display:none;">
                <div class="form-group">
                    <label for="student_search">Search Student</label>
                    <input type="text" id="student_search" placeholder="Enter reg no or name..." onkeyup="searchStudents()">
                </div>
                <div id="individual-students-list" class="students-list">
                    <p>Search for students to add individually...</p>
                </div>
            </div>

            <div class="form-group">
                <label for="eligibility">Eligibility</label>
                <select id="eligibility" name="eligibility">
                    <option value="eligible">Eligible</option>
                    <option value="not_eligible">Not Eligible</option>
                </select>
            </div>

            <div class="form-group">
                <label for="attempt">Attempt</label>
                <input type="number" id="attempt" name="attempt" value="1" min="1" max="5">
                <small style="color:#666;">Leave as 1 for new students. Higher numbers for repeats.</small>
            </div>

            <div style="display:flex; gap:15px; background:#f8f9fa; padding:15px; border-radius:8px; margin-bottom:15px;">
                <div class="form-group" style="flex:1;">
                    <label>Assessment Percentage (from Exam)</label>
                    <input type="text" value="<?php echo htmlspecialchars($exam->assessment_percentage ?? 0); ?>%" readonly 
                           style="background:#fff; cursor:not-allowed;">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Final Exam Percentage (from Exam)</label>
                    <input type="text" value="<?php echo htmlspecialchars($exam->final_exam_percentage ?? 0); ?>%" readonly 
                           style="background:#fff; cursor:not-allowed;">
                </div>
            </div>
            <small style="color: #666; display: block; margin-bottom: 15px;">Percentages are set in the Exam Schedule and will be used for all students added to this exam.</small>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn-submit">Add Selected Students</button>
                <a href="index.php?action=exam_results&filter_exam=<?php echo $exam->id; ?>" class="btn-cancel">Back to Exams</a>
            </div>
        </form>
    </div>
</div>

<script>
let allStudents = [];

function toggleMethod() {
    const method = document.querySelector('input[name="add_method"]:checked').value;
    document.getElementById('batch-section').style.display = method === 'batch' ? 'block' : 'none';
    document.getElementById('individual-section').style.display = method === 'individual' ? 'block' : 'none';
}

function loadBatchStudents() {
    const batchId = document.getElementById('batch_id').value;
    const examId = document.getElementById('exam_id').value;
    const listDiv = document.getElementById('batch-students-list');
    
    if (!batchId) {
        listDiv.style.display = 'none';
        return;
    }
    
    listDiv.innerHTML = '<p>Loading students...</p>';
    listDiv.style.display = 'block';
    
    fetch('index.php?action=exam_results&sub=getStudentsByBatch&batch_id=' + batchId + '&exam_id=' + examId)
        .then(response => response.json())
        .then(students => {
            allStudents = students;
            renderStudentList(students, 'batch-students-list');
        })
        .catch(error => {
            console.error('Error loading students:', error);
            listDiv.innerHTML = '<p style="color:red;">Error loading students.</p>';
        });
}

function searchStudents() {
    const search = document.getElementById('student_search').value.toLowerCase();
    const examId = document.getElementById('exam_id').value;
    const listDiv = document.getElementById('individual-students-list');
    
    if (search.length < 2) {
        listDiv.innerHTML = '<p>Enter at least 2 characters to search...</p>';
        return;
    }
    
    // Load all students if not loaded, filtering out those already in the exam
    if (allStudents.length === 0) {
        listDiv.innerHTML = '<p>Loading students...</p>';
        fetch('index.php?action=students&sub=getAll&exclude_exam_id=' + examId)
            .then(response => response.json())
            .then(students => {
                allStudents = students;
                filterAndDisplay(search);
            })
            .catch(() => {
                listDiv.innerHTML = '<p style="color:red;">Error loading students.</p>';
            });
    } else {
        filterAndDisplay(search);
    }
}

function filterAndDisplay(search) {
    const listDiv = document.getElementById('individual-students-list');
    const filtered = allStudents.filter(s => 
        (s.reg_no && s.reg_no.toLowerCase().includes(search)) ||
        (s.fullname && s.fullname.toLowerCase().includes(search))
    );
    renderStudentList(filtered, 'individual-students-list');
}

function renderStudentList(students, containerId) {
    const container = document.getElementById(containerId);
    if (students.length === 0) {
        container.innerHTML = '<p>No students found.</p>';
        return;
    }
    
    container.innerHTML = students.map(student => `
        <div class="student-item">
            <input type="checkbox" name="student_ids[]" value="${student.id}" id="student_${student.id}">
            <label for="student_${student.id}" style="flex:1; cursor:pointer;">
                <strong>${student.reg_no || ''}</strong> - ${student.fullname || ''}
            </label>
        </div>
    `).join('');
}

// Form submission validation
document.getElementById('addStudentsForm').addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('input[name="student_ids[]"]:checked');
    if (checked.length === 0) {
        e.preventDefault();
        alert('Please select at least one student.');
        return false;
    }
});
</script>
