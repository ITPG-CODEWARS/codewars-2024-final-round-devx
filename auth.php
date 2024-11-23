<?php
// Стартиране на сесия
session_start();

// Включване на конфигурационния файл за базата данни
include 'config.php';

// Обработка на регистрация
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    // Проверка дали потребителското име или имейл вече съществуват
    $checkQuery = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Потребителското име или имейл вече съществуват. Моля, опитайте отново.');</script>";
    } else {
        // Въвеждане на нов потребител в базата данни
        $insertQuery = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            // Настройване на сесия за потребителския ID и потребителско име, след което пренасочване
            $_SESSION["user_id"] = $stmt->insert_id;  // Настройване на потребителския ID за сесията
            $_SESSION["username"] = $username;
            session_write_close();  // Уверяване, че сесията е запазена
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Регистрацията не беше успешна. Моля, опитайте отново.');</script>";
        }
    }
    $stmt->close();
}

// Обработка на влизане
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST["register"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Проверка дали потребителят съществува
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Проверка на паролата
        if (password_verify($password, $user['password'])) {
            // Настройване на сесия за потребителски ID и потребителско име, след което пренасочване
            $_SESSION["user_id"] = $user['id'];  // Предполага се, че 'id' е основният ключ в таблицата users
            $_SESSION["username"] = $username;
            session_write_close();  // Уверяване, че сесията е запазена
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Невалидно потребителско име или парола. Моля, опитайте отново.');</script>";
        }
    } else {
        echo "<script>alert('Невалидно потребителско име или парола. Моля, опитайте отново.');</script>";
    }
    $stmt->close();
}

// Затваряне на връзката с базата данни
$conn->close();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Вход</title>
    <link rel="stylesheet" type="text/css" href="assets/auth.css" />
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <div class="forms-container">
            <div class="signin-signup">
                <form action="" method="POST" class="sign-in-form">
                    <h2 class="title">Вход</h2>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Потребителско име" required />
                    </div>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Парола" required />
                    </div>
                    <input type="submit" value="Вход" class="btn solid" />
                </form>

                <form action="" method="POST" class="sign-up-form">
                    <h2 class="title">Регистрация</h2>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Потребителско име" required />
                    </div>
                    <div class="input-field">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Имейл" required />
                    </div>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Парола" required />
                    </div>
                    <input type="submit" name="register" value="Регистрация" class="btn solid" />
                </form>
            </div>
        </div>
        
        <!-- Панели за превключване между формите за вход и регистрация -->
        <div class="panels-container">
            <div class="panel left-panel">
                <div class="content">
                    <h3>Нов тук?</h3>
                    <br>
                    <button class="btn transparent" id="sign-up-btn">Регистрация</button>
                </div>
                <img src="assets/images/log.svg" class="image" alt="">
            </div>

            <div class="panel right-panel">
                <div class="content">
                    <h3>Един от нас?</h3>
                    <br>
                    <button class="btn transparent" id="sign-in-btn">Вход</button>
                </div>
                <img src="assets/images/register.svg" class="image" alt="">
            </div>
        </div>
    </div>

    <script src="assets/auth.js"></script>
</body>
</html>
