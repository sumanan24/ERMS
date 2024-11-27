<?php
include('../includes/config.php');

if (isset($_POST['department_id'])) {
    $department_id = $_POST['department_id'];

    // Fetch courses for the selected department
    $sql = "SELECT * FROM course WHERE did = :department_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($courses) {
        echo '<option value="">Select Course</option>';
        foreach ($courses as $course) {
            echo '<option value="' . $course['id'] . '">' . $course['cname'] . '</option>';
        }
    } else {
        echo '<option value="">No courses available</option>';
    }
}
?>
