
<?php
include "../config/db.php";

$error = "";

// Fetch all hostels for dropdown
$hostelQuery = mysqli_query(
    $conn,
    "SELECT Hostel_ID, Hostel_Name, Total_Rooms
     FROM hostel
     ORDER BY Hostel_Name"
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $hostel_id = filter_input(INPUT_POST, "hostel_id", FILTER_VALIDATE_INT);
    $room_no = filter_input(INPUT_POST, "room_no", FILTER_VALIDATE_INT);
    $floor = filter_input(INPUT_POST, "floor", FILTER_VALIDATE_INT);
    $capacity = filter_input(INPUT_POST, "capacity", FILTER_VALIDATE_INT);

    // Basic validation
    if (
        !$hostel_id ||
        !$room_no ||
        $room_no < 1 ||
        $floor === false ||
        $floor === null ||
        $floor < 0 ||
        !$capacity ||
        $capacity < 1
    ) {
        $error = "Please enter valid room details.";
    } else {

        // Check whether hostel exists
        $checkHostel = mysqli_prepare(
            $conn,
            "SELECT Total_Rooms FROM hostel WHERE Hostel_ID = ?"
        );

        mysqli_stmt_bind_param($checkHostel, "i", $hostel_id);
        mysqli_stmt_execute($checkHostel);

        $hostelResult = mysqli_stmt_get_result($checkHostel);
        $hostel = mysqli_fetch_assoc($hostelResult);

        mysqli_stmt_close($checkHostel);

        if (!$hostel) {
            $error = "Selected hostel does not exist.";
        } else {

            // Check existing number of rooms in this hostel
            $countQuery = mysqli_prepare(
                $conn,
                "SELECT COUNT(*) AS room_count
                 FROM room
                 WHERE Hostel_ID = ?"
            );

            mysqli_stmt_bind_param($countQuery, "i", $hostel_id);
            mysqli_stmt_execute($countQuery);

            $countResult = mysqli_stmt_get_result($countQuery);
            $countData = mysqli_fetch_assoc($countResult);

            mysqli_stmt_close($countQuery);

            if ($countData["room_count"] >= $hostel["Total_Rooms"]) {

                $error = "This hostel has reached its maximum number of rooms.";

            } else {

                // Insert room
                $insertQuery = mysqli_prepare(
                    $conn,
                    "INSERT INTO room (Hostel_ID, Room_No, Floor, Capacity)
                     VALUES (?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $insertQuery,
                    "iiii",
                    $hostel_id,
                    $room_no,
                    $floor,
                    $capacity
                );

                if (mysqli_stmt_execute($insertQuery)) {

                    mysqli_stmt_close($insertQuery);

                    header("Location: view.php?added=1");
                    exit();

                } else {

                    if (mysqli_stmt_errno($insertQuery) == 1062) {
                        $error = "This room number already exists in the selected hostel.";
                    } else {
                        $error = "Error adding room: " . mysqli_stmt_error($insertQuery);
                    }

                    mysqli_stmt_close($insertQuery);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Room | Hostel Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .form-container {
            max-width: 650px;
            margin: 60px auto;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .page-title {
            color: #17365d;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
        }

        .btn-primary {
            background: #17365d;
            border: none;
        }

        .btn-primary:hover {
            background: #0d2745;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="form-container">

        <h3 class="page-title">Add New Room</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <!-- Select Hostel -->
            <div class="mb-3">
                <label class="form-label">Select Hostel</label>

                <select name="hostel_id" class="form-select" required>
                    <option value="">-- Select Hostel --</option>

                    <?php
                    mysqli_data_seek($hostelQuery, 0);

                    while ($row = mysqli_fetch_assoc($hostelQuery)):
                    ?>

                        <option value="<?php echo $row['Hostel_ID']; ?>"
                            <?php
                            if (
                                isset($_POST['hostel_id']) &&
                                $_POST['hostel_id'] == $row['Hostel_ID']
                            ) {
                                echo "selected";
                            }
                            ?>>
                            <?php echo htmlspecialchars($row['Hostel_Name']); ?>
                        </option>

                    <?php endwhile; ?>

                </select>
            </div>

            <!-- Room Number -->
            <div class="mb-3">
                <label class="form-label">Room Number</label>

                <input
                    type="number"
                    name="room_no"
                    class="form-control"
                    min="1"
                    required
                    value="<?php echo htmlspecialchars($_POST['room_no'] ?? ''); ?>"
                    placeholder="Enter room number"
                >
            </div>

            <!-- Floor -->
            <div class="mb-3">
                <label class="form-label">Floor</label>

                <input
                    type="number"
                    name="floor"
                    class="form-control"
                    min="0"
                    required
                    value="<?php echo htmlspecialchars($_POST['floor'] ?? ''); ?>"
                    placeholder="Enter floor number"
                >
            </div>

            <!-- Capacity -->
            <div class="mb-4">
                <label class="form-label">Room Capacity</label>

                <input
                    type="number"
                    name="capacity"
                    class="form-control"
                    min="1"
                    required
                    value="<?php echo htmlspecialchars($_POST['capacity'] ?? ''); ?>"
                    placeholder="Enter number of beds"
                >
            </div>

            <button type="submit" class="btn btn-primary px-4">
                Add Room
            </button>

            <a href="view.php" class="btn btn-secondary px-4">
                Cancel
            </a>

        </form>

    </div>

</div>

</body>
</html>
