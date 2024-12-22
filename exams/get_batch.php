<?php
include('../includes/config.php');

if (isset($_POST['course_id'])) {
    $courseId = $_POST['course_id'];

    // Fetch batches based on the selected course
    $batchSql = "SELECT * FROM batch WHERE cid = :courseId";
    $batchQuery = $dbh->prepare($batchSql);
    $batchQuery->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $batchQuery->execute();
    $batches = $batchQuery->fetchAll(PDO::FETCH_OBJ);

    if (count($batches) > 0) {
        foreach ($batches as $batch) {
            echo "<option value='" . htmlentities($batch->batch_no) . "'>" . htmlentities($batch->batch_no) . "</option>";
        }
    } else {
        echo "<option value=''>No batches found</option>";
    }
}
?>
