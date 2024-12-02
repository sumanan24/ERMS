<?php
include('../includes/config.php');
if (isset($_POST['deptId'])) {
    $deptId = $_POST['deptId'];
    $sql = "SELECT * FROM course WHERE did = :deptId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':deptId', $deptId, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    echo '<option value="">Select Course</option>';
    foreach ($results as $result) {
        echo '<option value="' . htmlentities($result->id) . '">' . htmlentities($result->cname) . '</option>';
    }
}
?>
