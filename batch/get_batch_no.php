<?php
include('../includes/config.php');

if (isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];

    // Get the maximum batch_no for the selected course
    $sql = "SELECT MAX(batch_no) FROM batch WHERE cid = :course_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    $max_batch_no = $stmt->fetchColumn();

    echo $max_batch_no ? $max_batch_no : 0;
}
?>
