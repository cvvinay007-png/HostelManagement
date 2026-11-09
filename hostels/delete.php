
<?php
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: view.php");
    exit();
}

if (!isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    die("Invalid hostel ID");
}

$id = (int)$_POST['id'];

// Check whether hostel exists
$stmt = mysqli_prepare(
    $conn,
    "SELECT Hostel_ID FROM hostel WHERE Hostel_ID = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$hostel = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$hostel) {
    die("Hostel not found.");
}

// Check whether rooms exist
$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM room WHERE Hostel_ID = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ((int)$data['total'] > 0) {
    die("Cannot delete this hostel because rooms are registered under it. Remove or reassign the related records first.");
}

// Delete hostel
$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM hostel WHERE Hostel_ID = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: view.php?deleted=1");
    exit();
}

$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

die("Deletion failed: " . htmlspecialchars($error));
?>
