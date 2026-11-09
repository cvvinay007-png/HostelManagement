
<?php
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: view.php");
    exit();
}

if (!isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    die("Invalid student ID");
}

$id = (int) $_POST['id'];

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM student WHERE Student_ID = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: view.php?deleted=1");
    exit();
}

$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

die("Unable to delete student. They may have existing room allocations. " .
    htmlspecialchars($error));
?>
