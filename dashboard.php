<?php
session_start();
include 'config.php';

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Retrieve the username and admin status from the session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Fetch admin status
$query = "SELECT is_admin FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$is_admin = $user['is_admin'] ?? 0;

// Fetch train schedules from the database
$query = "SELECT train_number, `from`, destination, arrival_time, start_time FROM train_schedule";
$result = $conn->query($query);

// Handle train schedule creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_schedule"]) && $is_admin == 1) {
    $train_number = $_POST["train_number"];
    $from = $_POST["from"];
    $destination = $_POST["destination"];
    $arrival_time = $_POST["arrival_time"];
    $start_time = $_POST["start_time"]; // New field for start time

    // Ensure time format is HH:MM (append :00 for seconds)
    $arrival_time = $arrival_time . ":00";
    $start_time = $start_time . ":00";

    // Insert the new train schedule into the database
    $insertQuery = "INSERT INTO train_schedule (train_number, `from`, destination, arrival_time, start_time) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("sssss", $train_number, $from, $destination, $arrival_time, $start_time);

    if ($stmt->execute()) {
        // Redirect to the same page after success to avoid form resubmission
        header("Location: dashboard.php");
        exit();
    } else {
        $error_message = "Failed to add train schedule. Please try again.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/dashboard.css"> <!-- Optional: Custom CSS file for styling -->
</head>
<body>
    <h1>Приятно пътуване, <?php echo htmlspecialchars($username); ?>!</h1>

    <!-- Train schedule table -->
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Train Number</th>
                <th>From</th>
                <th>Destination</th>
                <th>Arrival Time</th>
                <th>Start Time</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['train_number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['from']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['destination']) . "</td>";
                    echo "<td>" . htmlspecialchars(date('H:i', strtotime($row['arrival_time']))) . "</td>"; // Format time to HH:MM
                    echo "<td>" . htmlspecialchars(date('H:i', strtotime($row['start_time']))) . "</td>"; // Format time to HH:MM
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No train schedules available.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Admin-only section for adding new train schedules -->
    <?php if ($is_admin == 1): ?>
        <h2>Admin Panel: Add New Train Schedule</h2>
        <form action="" method="POST">
            <label for="train_number">Train Number:</label>
            <input type="text" id="train_number" name="train_number" required><br>

            <label for="from">From:</label>
            <input type="text" id="from" name="from" required><br>

            <label for="destination">Destination:</label>
            <input type="text" id="destination" name="destination" required><br>

            <label for="arrival_time">Arrival Time:</label>
            <input type="text" id="arrival_time" name="arrival_time" placeholder="HH:MM" required><br>

            <label for="start_time">Start Time:</label>
            <input type="text" id="start_time" name="start_time" placeholder="HH:MM" required><br>

            <input type="submit" name="create_schedule" value="Add Schedule">
        </form>
        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>
    <?php endif; ?>

    <a href="logout.php">Logout</a> <!-- Logout link for user to end session -->

    <?php
    // Close database connection
    $conn->close();
    ?>
</body>
</html>
