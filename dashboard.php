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

// Initialize message variables
$error_message = '';
$success_message = '';

// Fetch train schedules from the database
$query = "SELECT train_number, `from`, destination, arrival_time, start_time, ticket_price FROM train_schedule";
$result = $conn->query($query);

// Handle train schedule creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_schedule"]) && $is_admin == 1) {
    $train_number = $_POST["train_number"];
    $from = $_POST["from"];
    $destination = $_POST["destination"];
    $arrival_time = $_POST["arrival_time"];
    $start_time = $_POST["start_time"];
    $ticket_price = $_POST["ticket_price"];

    // Ensure time format is HH:MM (append :00 for seconds)
    $arrival_time = $arrival_time . ":00";
    $start_time = $start_time . ":00";

    // Insert the new train schedule into the database
    $insertQuery = "INSERT INTO train_schedule (train_number, `from`, destination, arrival_time, start_time, ticket_price) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssssss", $train_number, $from, $destination, $arrival_time, $start_time, $ticket_price);

    if ($stmt->execute()) {
        // Redirect to the same page after success to avoid form resubmission
        header("Location: dashboard.php");
        exit();
    } else {
        $error_message = "Failed to add train schedule. Please try again.";
    }
    $stmt->close();
}

// Handle ticket reservation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reserve_ticket"])) {
    $train_number = $_POST["train_number"];
    $seat_number = $_POST["seat_number"];

    // Check if the seat number already exists
    $checkQuery = "SELECT * FROM ticket_reservations WHERE train_number = ? AND seat_number = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $train_number, $seat_number);
    $stmt->execute();
    $checkResult = $stmt->get_result();

    if ($checkResult->num_rows > 0) {
        $error_message = "This seat has already been reserved.";
    } else {
        // Get the current timestamp
        $reservation_timestamp = date("Y-m-d H:i:s");

        // Insert the reservation into the database with the current timestamp
        $insertQuery = "INSERT INTO ticket_reservations (train_number, seat_number, reserved_by, reservation_timestamp) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("siss", $train_number, $seat_number, $username, $reservation_timestamp);

        if ($stmt->execute()) {
            // Display custom success message
            $success_message = "Успешно резервирахте билет с номер №" . htmlspecialchars($seat_number) . ". Моля потвърдете билета на касата на БДЖ. Лек път!";
        } else {
            $error_message = "Failed to reserve the ticket. Please try again.";
        }
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Табло</title>
    <link rel="stylesheet" href="assets/dashboard.css">
    <style>
        /* Стил за модалния прозорец */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Приятно пътуване, <?php echo htmlspecialchars($username); ?>!</h1>

    <!-- Таблица с разписания на влакове -->
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Номер на влака</th>
                <th>От</th>
                <th>До</th>
                <th>Час на пристигане</th>
                <th>Час на тръгване</th>
                <th>Цена на билет</th>
                <th>Резервирай билет</th>
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
                    echo "<td>" . htmlspecialchars(date('H:i', strtotime($row['arrival_time']))) . "</td>";
                    echo "<td>" . htmlspecialchars(date('H:i', strtotime($row['start_time']))) . "</td>";
                    echo "<td>" . htmlspecialchars($row['ticket_price']) . "</td>";
                    echo "<td><button onclick='openModal(\"" . htmlspecialchars($row['train_number']) . "\", \"" . htmlspecialchars($row['ticket_price']) . "\")'>Резервирай билет</button></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>Няма налични разписания на влакове.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Админ панел за добавяне на ново разписание на влакове -->
    <?php if ($is_admin == 1): ?>
        <h2>Админ панел: Добавяне на ново разписание на влак</h2>
        <form action="" method="POST">
            <label for="train_number">Номер на влака:</label>
            <input type="text" id="train_number" name="train_number" required><br>

            <label for="from">От:</label>
            <input type="text" id="from" name="from" required><br>

            <label for="destination">До:</label>
            <input type="text" id="destination" name="destination" required><br>

            <label for="arrival_time">Час на пристигане:</label>
            <input type="text" id="arrival_time" name="arrival_time" placeholder="ЧЧ:ММ" required><br>

            <label for="start_time">Час на тръгване:</label>
            <input type="text" id="start_time" name="start_time" placeholder="ЧЧ:ММ" required><br>

            <label for="ticket_price">Цена на билет:</label>
            <input type="number" id="ticket_price" name="ticket_price" required><br>

            <input type="submit" name="create_schedule" value="Добави разписание">
        </form>
    <?php endif; ?>

    <!-- Модален прозорец за резервиране на билет -->
    <div id="ticketModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Резервиране на билет</h2>
            <form action="" method="POST">
                <input type="hidden" id="modal_train_number" name="train_number">
                <label for="seat_number">Номер на седалка:</label>
                <input type="number" id="seat_number" name="seat_number" min="1" max="100" required><br>
                <p>Местата са общо 100, изберете някое от тях.</p>
                <input type="submit" name="reserve_ticket" value="Резервирай билет">
            </form>
        </div>
    </div>

    <script>
        function openModal(trainNumber, ticketPrice) {
            document.getElementById("ticketModal").style.display = "block";
            document.getElementById("modal_train_number").value = trainNumber;
        }

        function closeModal() {
            document.getElementById("ticketModal").style.display = "none";
        }

        // Показване на съобщения от PHP в JavaScript alert
        <?php if (!empty($error_message)): ?>
            alert("<?php echo addslashes($error_message); ?>");
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            alert("<?php echo addslashes($success_message); ?>");
        <?php endif; ?>
    </script>
</body>
</html>
