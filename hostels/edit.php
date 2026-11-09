
<?php
include '../config/db.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("Invalid hostel ID");
}

$id = (int)$_GET['id'];
$error = "";

// Fetch hostel details
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM hostel WHERE Hostel_ID = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$hostel = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$hostel) {
    die("Hostel not found");
}

// Update hostel
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['hostel_name']);
    $location = trim($_POST['location']);
    $gender = $_POST['gender'];
    $totalRooms = (int)$_POST['total_rooms'];

    if ($name === "" || $location === "" || $totalRooms < 1) {
        $error = "Please enter valid hostel details.";
    } else {

        // Check existing room count
        $check = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS total
             FROM room WHERE Hostel_ID = ?"
        );

        mysqli_stmt_bind_param($check, "i", $id);
        mysqli_stmt_execute($check);

        $roomResult = mysqli_stmt_get_result($check);
        $roomData = mysqli_fetch_assoc($roomResult);
        mysqli_stmt_close($check);

        if ($totalRooms < (int)$roomData['total']) {
            $error = "Total rooms cannot be less than the existing registered rooms (" .
                     (int)$roomData['total'] . ").";
        } else {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE hostel
                 SET Hostel_Name = ?,
                     Location = ?,
                     Gender = ?,
                     Total_Rooms = ?
                 WHERE Hostel_ID = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssii",
                $name,
                $location,
                $gender,
                $totalRooms,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: view.php?updated=1");
                exit();
            } else {
                $error = "Update failed: " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        }
    }

    // Preserve submitted values
    $hostel['Hostel_Name'] = $name;
    $hostel['Location'] = $location;
    $hostel['Gender'] = $gender;
    $hostel['Total_Rooms'] = $totalRooms;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Hostel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f4f6fb;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .container {
            max-width: 850px;
            margin-top: 45px;
        }

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,.05);
        }

        .form-label {
            font-weight: 600;
        }

        .form-control, .form-select {
            padding: 11px;
            border-radius: 8px;
        }
    </style>
</head>

<body>

<div class="container">

    <h2 class="fw-bold mb-2">Edit Hostel</h2>
    <p class="text-muted mb-4">Update hostel information.</p>

    <div class="form-card">

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Hostel Name</label>
                    <input type="text" name="hostel_name"
                        class="form-control" maxlength="100" required
                        value="<?php echo htmlspecialchars($hostel['Hostel_Name']); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Location</label>
                    <input type="text" name="location"
                        class="form-control" maxlength="150" required
                        value="<?php echo htmlspecialchars($hostel['Location']); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="Male"
                            <?php echo $hostel['Gender'] === 'Male' ? 'selected' : ''; ?>>
                            Boys Hostel
                        </option>

                        <option value="Female"
                            <?php echo $hostel['Gender'] === 'Female' ? 'selected' : ''; ?>>
                            Girls Hostel
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Total Rooms</label>
                    <input type="number" name="total_rooms"
                        class="form-control" min="1" required
                        value="<?php echo (int)$hostel['Total_Rooms']; ?>">
                </div>

            </div>

            <hr class="my-4">

            <button type="submit" class="btn btn-primary">
                Save Changes
            </button>

            <a href="view.php" class="btn btn-secondary">
                Cancel
            </a>

        </form>

    </div>

</div>

</body>
</html>
