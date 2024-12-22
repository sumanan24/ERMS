<?php
include('../includes/config.php');
if (isset($_POST['courseId'])) {
    $courseId = $_POST['courseId'];
    $sql = "SELECT batch.id, batch.batch_no 
            FROM batch
            WHERE batch.cid = :courseId ";
    $query = $dbh->prepare($sql);
    $query->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    echo '<option value="">Select Batch</option>';
    foreach ($results as $result) {
        echo '<option value="' . htmlentities($result->id) . '">' . htmlentities($result->batch_no) . '</option>';
    }
}
?>
