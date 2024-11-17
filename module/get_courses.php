<?php
include('../includes/config.php');
if (isset($_POST['department_id'])) {
    $department_id = $_POST['department_id'];
    
    // Query to fetch courses based on the selected department
    $sql = "SELECT id, cname FROM course WHERE did = :department_id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':department_id', $department_id, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Return results in JSON format
    echo json_encode($results);
}
?>
