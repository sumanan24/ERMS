<?php
include('../includes/config.php');

if ($_POST['deptId']) {
    $deptId = intval($_POST['deptId']);
    $sql = "SELECT * FROM course WHERE did = :deptId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':deptId', $deptId, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    echo "<option value=''>Select Course</option>";
    if ($query->rowCount() > 0) {
        foreach ($results as $result) {
            echo "<option value='" . htmlentities($result->id) . "'>" . htmlentities($result->cname) . "</option>";
        }
    }
}
?>
