
<?php
include '../config/db.php';

$sql = "
    SELECT
        r.Hostel_ID,
        r.Room_No,
        r.Floor,
        r.Capacity,
        h.Hostel_Name,
        (
            SELECT COUNT(*)
            FROM allocation a
            WHERE a.Hostel_ID = r.Hostel_ID
              AND a.Room_No = r.Room_No
              AND a.Status = 'Active'
        ) AS Occupied
    FROM room r
    JOIN hostel h ON r.Hostel_ID = h.Hostel_ID
    ORDER BY r.Hostel_ID, r.Room_No
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$rooms = [];
$totalRooms = 0;
$totalCapacity = 0;
$totalOccupied = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $row['Occupied'] = (int)$row['Occupied'];
    $row['Capacity'] = (int)$row['Capacity'];
    $row['Available'] = $row['Capacity'] - $row['Occupied'];

    $rooms[] = $row;
    $totalRooms++;
    $totalCapacity += $row['Capacity'];
    $totalOccupied += $row['Occupied'];
}

$totalAvailable = $totalCapacity - $totalOccupied;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Room Management | Hostel Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    background: #f4f6fb;
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #1e293b;
}

.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    background: #172554;
    padding: 25px 15px;
}

.brand {
    color: white;
    font-size: 23px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 38px;
}

.brand i { color: #60a5fa; }

.menu-label {
    color: #94a3b8;
    font-size: 11px;
    letter-spacing: 1.5px;
    margin: 20px 12px 12px;
}

.sidebar a {
    display: block;
    color: #cbd5e1;
    text-decoration: none;
    padding: 13px 15px;
    margin-bottom: 7px;
    border-radius: 8px;
    font-size: 14px;
}

.sidebar a i { width: 25px; }

.sidebar a:hover,
.sidebar a.active {
    background: #2563eb;
    color: white;
}

.main-content {
    margin-left: 250px;
    min-height: 100vh;
}

.topbar {
    height: 75px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 35px;
    box-shadow: 0 2px 10px rgba(0,0,0,.04);
}

.topbar h5 { margin: 0; font-weight: 600; }

.avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #dbeafe;
    color: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
}

.page-content { padding: 35px; }

.page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}

.page-heading h2 {
    font-size: 25px;
    font-weight: 700;
}

.page-heading p {
    color: #64748b;
    margin: 0;
}

.btn-primary {
    background: #2563eb;
    border: none;
    padding: 11px 18px;
    border-radius: 8px;
}

.stats-card, .table-card {
    background: white;
    border-radius: 12px;
    padding: 23px;
    box-shadow: 0 3px 12px rgba(0,0,0,.04);
}

.stats-card {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.stats-icon {
    width: 48px;
    height: 48px;
    background: #dbeafe;
    color: #2563eb;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.stats-card h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}

.stats-card p {
    margin: 3px 0 0;
    color: #64748b;
    font-size: 13px;
}

.table {
    vertical-align: middle;
    white-space: nowrap;
    font-size: 13px;
}

.table thead th {
    background: #172554;
    color: white;
    padding: 15px;
    border: none;
}

.table tbody td { padding: 14px 15px; }

.room-number {
    color: #2563eb;
    background: #eff6ff;
    padding: 6px 10px;
    border-radius: 6px;
    font-weight: 600;
}

.available {
    background: #dcfce7;
    color: #15803d;
    padding: 6px 10px;
    border-radius: 20px;
}

.full {
    background: #fee2e2;
    color: #b91c1c;
    padding: 6px 10px;
    border-radius: 20px;
}

.search-box {
    max-width: 300px;
}

.search-box input {
    padding: 10px 13px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    width: 100%;
}

@media(max-width:768px) {
    .sidebar {
        width: 70px;
        padding: 20px 8px;
    }

    .brand { font-size: 0; }
    .brand i { font-size: 22px; }
    .menu-label { display: none; }

    .sidebar a {
        font-size: 0;
        text-align: center;
        padding: 14px 5px;
    }

    .sidebar a i { font-size: 17px; }

    .main-content { margin-left: 70px; }
    .page-content { padding: 20px 12px; }
    .topbar { padding: 0 15px; }
}
</style>
</head>

<body>

<aside class="sidebar">

    <div class="brand">
        <i class="fa-solid fa-building-user"></i>
        Hostel Admin
    </div>

    <p class="menu-label">MAIN MENU</p>

    <a href="../index.php">
        <i class="fa-solid fa-chart-pie"></i> Dashboard
    </a>

    <a href="../students/view.php">
        <i class="fa-solid fa-users"></i> Students
    </a>

    <a href="../hostels/view.php">
        <i class="fa-solid fa-building"></i> Hostels
    </a>

    <a href="view.php" class="active">
        <i class="fa-solid fa-door-open"></i> Rooms
    </a>

    <a href="../allocations/view.php">
        <i class="fa-solid fa-bed"></i> Allocations
    </a>

    <a href="../payments/view.php">
        <i class="fa-solid fa-wallet"></i> Payments
    </a>

</aside>

<main class="main-content">

    <nav class="topbar">
        <h5>Room Management</h5>

        <div class="d-flex align-items-center gap-2">
            <div class="avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <span class="fw-semibold">Administrator</span>
        </div>
    </nav>

    <section class="page-content">

        <div class="page-heading">
            <div>
                <h2>Rooms</h2>
                <p>Manage hostel rooms and availability.</p>
            </div>

            <a href="add.php" class="btn btn-primary">
                <i class="fa-solid fa-plus me-2"></i>
                Add Room
            </a>
        </div>

        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <div>
                        <h3><?php echo $totalRooms; ?></h3>
                        <p>Total Rooms</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fa-solid fa-bed"></i>
                    </div>
                    <div>
                        <h3><?php echo $totalOccupied; ?></h3>
                        <p>Occupied Beds</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                    <div>
                        <h3><?php echo $totalAvailable; ?></h3>
                        <p>Available Beds</p>
                    </div>
                </div>
            </div>

        </div>

        <div class="table-card">

            <div class="d-flex justify-content-between align-items-center
                        flex-wrap gap-3 mb-4">

                <h5 class="fw-bold mb-0">
                    <i class="fa-solid fa-list me-2 text-primary"></i>
                    Room Records
                </h5>

                <div class="search-box">
                    <input type="text" id="searchRoom"
                           placeholder="Search rooms..."
                           onkeyup="searchRooms()">
                </div>

            </div>

            <div class="table-responsive">

                <table class="table table-hover" id="roomTable">

                    <thead>
                        <tr>
                            <th>Hostel</th>
                            <th>Room No.</th>
                            <th>Floor</th>
                            <th>Capacity</th>
                            <th>Occupied</th>
                            <th>Available</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($totalRooms > 0): ?>

                        <?php foreach ($rooms as $row): ?>

                            <?php
                                $isFull = $row['Available'] <= 0;
                            ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($row['Hostel_Name']); ?>
                                </td>

                                <td>
                                    <span class="room-number">
                                        <?php echo $row['Room_No']; ?>
                                    </span>
                                </td>

                                <td><?php echo $row['Floor']; ?></td>

                                <td><?php echo $row['Capacity']; ?></td>

                                <td><?php echo $row['Occupied']; ?></td>

                                <td><?php echo $row['Available']; ?></td>

                                <td>
                                    <?php if ($isFull): ?>
                                        <span class="full">Full</span>
                                    <?php else: ?>
                                        <span class="available">Available</span>
                                    <?php endif; ?>
                                </td>

                                <td>

                                    <a href="edit.php?hostel_id=<?php echo $row['Hostel_ID']; ?>&room_no=<?php echo $row['Room_No']; ?>"
                                       class="btn btn-sm btn-warning mb-1">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>

                                    <form action="delete.php" method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete this room?');">

                                        <input type="hidden" name="hostel_id"
                                               value="<?php echo $row['Hostel_ID']; ?>">

                                        <input type="hidden" name="room_no"
                                               value="<?php echo $row['Room_No']; ?>">

                                        <button type="submit"
                                                class="btn btn-sm btn-danger mb-1">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="8" class="text-center py-4">
                                No rooms registered yet.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

                <p id="noResults" class="text-center text-muted"
                   style="display:none;">
                    No matching rooms found.
                </p>

            </div>

        </div>

    </section>

</main>

<script>
function searchRooms() {
    let input = document.getElementById("searchRoom")
        .value.toLowerCase();

    let rows = document.querySelectorAll("#roomTable tbody tr");
    let count = 0;

    rows.forEach(row => {
        let match = row.innerText.toLowerCase().includes(input);
        row.style.display = match ? "" : "none";

        if (match) count++;
    });

    document.getElementById("noResults").style.display =
        count === 0 ? "block" : "none";
}
</script>

</body>
</html>
