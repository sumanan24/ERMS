<?php
include('../includes/config.php');
if (isset($_POST['courseId'])) {
    $courseId = $_POST['courseId'];
    $sql = "SELECT * FROM module WHERE cid = :courseId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    foreach ($results as $result) {
        echo '<tr>
                <td><input type="hidden" name="module[]" value="' . $result->id . '">' . $result->mname . '</td>
                <td><input type="date" name="date[]" class="form-control" required></td>
                <td><input type="time" name="time[]" class="form-control" required></td>
              </tr>';
    }
}
?>
