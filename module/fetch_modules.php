<?php
include('../includes/config.php');

$courseId = isset($_GET['courseId']) ? $_GET['courseId'] : '';

$sql = "SELECT * FROM module";
if ($courseId != '') {
    $sql .= " WHERE cid = (SELECT id FROM course WHERE id = :courseId)";
}

$query = $dbh->prepare($sql);
if ($courseId != '') {
    $query->bindParam(':courseId', $courseId, PDO::PARAM_INT);
}
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

$data = [];
foreach ($results as $row) {
    $data[] = [
        'id' => $row->id,
        'mcode' => $row->mcode,
        'mname' => $row->mname
    ];
}

echo json_encode($data);
?>
