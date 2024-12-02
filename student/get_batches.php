<?php
include('../includes/config.php');

if ($_POST['courseId']) {
    $courseId = intval($_POST['courseId']);
    $sql = "SELECT * FROM batch WHERE cid = :courseId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    echo "<option value=''>Select Batch</option>";
    if ($query->rowCount() > 0) {
        foreach ($results as $result) {
            echo "<option value='" . htmlentities($result->batch_no) . "'>" . htmlentities($result->batch_no) . "</option>";
        }
    }
}
?>
