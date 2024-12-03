<?php
require_once 'auth.php';
require_once '../database.php';

$query_today = "SELECT COUNT(*) AS count FROM registrations WHERE DATE(created_at) = CURDATE()";
$query_last_7 = "SELECT COUNT(*) AS count FROM registrations WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$query_prev_7 = "SELECT COUNT(*) AS count FROM registrations WHERE created_at BETWEEN DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$query_this_month = "SELECT COUNT(*) AS count FROM registrations WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
$query_prev_month = "SELECT COUNT(*) AS count FROM registrations WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(CURDATE())";
$query_month_before = "SELECT COUNT(*) AS count FROM registrations WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 2 MONTH)) AND YEAR(created_at) = YEAR(CURDATE())";

$stats = [
    'today' => $conn->query($query_today)->fetch_assoc()['count'],
    'last_7_days' => $conn->query($query_last_7)->fetch_assoc()['count'],
    'prev_7_days' => $conn->query($query_prev_7)->fetch_assoc()['count'],
    'this_month' => $conn->query($query_this_month)->fetch_assoc()['count'],
    'prev_month' => $conn->query($query_prev_month)->fetch_assoc()['count'],
    'month_before' => $conn->query($query_month_before)->fetch_assoc()['count']
];

// Fetch registrants data
$query = "SELECT id, email, first_name, last_name, postal_code, how_did_you_hear, is_realtor, created_at FROM registrations ORDER BY created_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Admin Dashboard</h1>
    <p><a href="logout.php">Logout</a></p>
    <div class="stats">
        <h2>Registration Statistics</h2>
        <ul>
            <li>Today: <?php echo $stats['today']; ?></li>
            <li>Last 7 Days: <?php echo $stats['last_7_days']; ?></li>
            <li>Previous 7 Days: <?php echo $stats['prev_7_days']; ?></li>
            <li>This Month: <?php echo $stats['this_month']; ?></li>
            <li>Previous Month: <?php echo $stats['prev_month']; ?></li>
            <li>Month Before Last: <?php echo $stats['month_before']; ?></li>
        </ul>
    </div>

    <div class="registrants">
        <h2>Registrant Details</h2>
        <p><a href="download.php" class="download-link">Download CSV</a></p>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Postal Code</th>
                    <th>How Did You Hear</th>
                    <th>Realtor</th>
                    <th>Registered At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['postal_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['how_did_you_hear']); ?></td>
                        <td><?php echo $row['is_realtor'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>