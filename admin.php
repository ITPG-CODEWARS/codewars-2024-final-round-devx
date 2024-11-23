<?php
session_start();
include 'config.php';

// Check if user is authenticated and is admin
if (!isset($_SESSION['user_id']) ) {
    header("Location: dashboard.php"); // Redirect to dashboard if not admin
    exit();
}

// Initialize message variables
$error_message = '';
$success_message = '';

// Handle train schedule creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_schedule"])) {
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
        $success_message = "Train schedule added successfully!";
    } else {
        $error_message = "Failed to add train schedule. Please try again.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Train Scheduler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card-columns {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            gap: 20px;
        }

        .card {
            flex: 1;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
            height: 280px;
        }

        .card-header {
            background-color: #495057;
            color: white;
            padding: 12px 15px;
        }

        .card-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .card-body {
            background-color: #f8f9fa;
            padding: 15px;
        }

        .form-label {
            font-size: 14px;
            margin-bottom: 4px;
        }

        .form-control {
            font-size: 14px;
            padding: 6px 12px;
        }

        .btn {
            padding: 6px 12px;
            font-size: 14px;
        }

        .row {
            margin-bottom: 12px;
        }

        .message-chat-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        #send-messages {
            flex: 1;
            background-color: #ffffff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }

        #chat-system {
            flex: 2;
            background-color: #ffffff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }

        .chat-box {
            height: 250px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            padding: 10px;
            background-color: #f8f9fa;
            margin-bottom: 10px;
        }

        .chat-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-bottom: 10px;
            resize: none;
        }

        .message {
            padding: 8px;
            margin: 5px 0;
            background-color: #ffffff;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.html">Project Name</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.html">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.html">Contact</a>
                    </li>
                    <li class="nav-item fw-bold">
                        <a class="nav-link" href="admin.HT">Admin Panel</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12">
                <section class="card-columns">
                    <div class="card">
                        <div class="card-header">
                            <h5>Create New Train</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row">
                                    <div class="col-6">
                                        <label for="train_number" class="form-label">Train Name</label>
                                        <input type="text" class="form-control" id="trainName">
                                    </div>
                                    <div class="col-6">
                                        <label for="destination" class="form-label">Destination</label>
                                        <select class="form-control" id="destination">
                                            <option value="Sofia">Sofia</option>
                                            <option value="Plovdiv">Plovdiv</option>
                                            <option value="Varna">Varna</option>
                                            <option value="Burgas">Burgas</option>
                                            <option value="Ruse">Ruse</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <label for="firstTrainTime" class="form-label">First Train Time</label>
                                        <input type="time" class="form-control" id="firstTrainTime">
                                    </div>
                                    <div class="col-6">
                                        <label for="frequency" class="form-label">Frequency (min)</label>
                                        <input type="number" class="form-control" id="frequency">
                                    </div>
                                </div>
                                <input type="submit" name="create_schedule" value="Добави разписание">
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5>Create New Stop</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row">
                                    <div class="col-6">
                                        <label for="stopName" class="form-label">Stop Name</label>
                                        <select class="form-control" id="stopName">
                                            <option value="Sofia">Sofia</option>
                                            <option value="Plovdiv">Plovdiv</option>
                                            <option value="Varna">Varna</option>
                                            <option value="Burgas">Burgas</option>
                                            <option value="Ruse">Ruse</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label for="newStopName" class="form-label">Add New Stop</label>
                                        <input type="text" class="form-control" id="newStopName">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary mt-3">Add Stop</button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5>Create New Track</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row">
                                    <div class="col-6">
                                        <label for="trackName" class="form-label">Track Name</label>
                                        <input type="text" class="form-control" id="trackName">
                                    </div>
                                    <div class="col-6">
                                        <label for="trackLength" class="form-label">Length (km)</label>
                                        <input type="number" class="form-control" id="trackLength">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Add Track</button>
                            </form>
                        </div>
                    </div>
                </section>

                <section class="message-chat-container">
                    <div id="send-messages">
                        <h2 class="h5 mb-3">Send Messages to Users</h2>
                        <form>
                            <div class="mb-3">
                                <label for="messageContent" class="form-label">Message Content</label>
                                <textarea class="form-control" id="messageContent" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Send Message</button>
                        </form>
                    </div>

                    <div id="chat-system">
                        <h2 class="h5 mb-3">Worker Chat System</h2>
                        <div class="chat-box">
                            <div class="message">Welcome to the real-time chat!</div>
                            <div class="message">How can I assist you today?</div>
                        </div>
                        <textarea class="chat-input" rows="2" placeholder="Type a message..."></textarea>
                        <button class="btn btn-primary w-100">Send</button>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>