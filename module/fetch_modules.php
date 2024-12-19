<?php
include('../includes/config.php');

// Check if courseId is passed as a GET parameter
$courseId = isset($_GET['courseId']) ? $_GET['courseId'] : '';

try {
    // Construct the SQL query based on courseId
    $sql = "SELECT * FROM module";
    if ($courseId != '') {
        $sql .= " WHERE cid = :courseId";
    }

    // Prepare the SQL statement
    $query = $dbh->prepare($sql);

    // Bind the courseId parameter if it exists
    if ($courseId != '') {
        $query->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    }

    // Execute the query
    $query->execute();

    // Fetch results as an array of objects
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    // Prepare the data to return as JSON
    $data = [];
    foreach ($results as $row) {
        $data[] = [
            'id' => $row->id,
            'mcode' => $row->mcode,
            'mname' => $row->mname,
            'credit' => $row->credit
        ];
    }

    // Send JSON-encoded data
    echo json_encode($data);
} catch (PDOException $e) {
    // Return an error message in case of exceptions
    echo json_encode([
        'error' => true,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
