<?php
include('../includes/config.php');

if (isset($_POST['courseId']) && isset($_POST['semester'])) {
    $courseId = $_POST['courseId'];
    $semester = $_POST['semester'];

    $sql = "SELECT * FROM module WHERE cid = :courseId AND semester = :semester";
    $query = $dbh->prepare($sql);
    $query->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $query->bindParam(':semester', $semester, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if ($query->rowCount() > 0) {
        foreach ($results as $result) {
            echo "<tr>
                <td><input type='hidden' name='module[]' value='" . htmlentities($result->id) . "'>" . htmlentities($result->mname) . "</td>
                <td><input type='date' name='date[]' class='form-control' required></td>
                <td><input type='time' name='time[]' class='form-control' required></td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No modules found for this semester.</td></tr>";
    }
}
?>
