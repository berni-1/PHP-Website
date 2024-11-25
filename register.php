<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    session_start();

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        header("Location: login.php");
        exit();
    }

    $username = $_SESSION['username'];
    $role = $_SESSION['role'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        include('db.php');
        
        $username = $conn->real_escape_string($_POST['username']);
        $password = $_POST['password'];
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $role = $_POST['rang'];

        $checkUserSql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($checkUserSql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Benutzername bereits vergeben.";
        } else {
            $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $passwordHash, $role);

            if ($stmt->execute()) {
                $success = "Neue Benutzer erfolgreich registriert.";
            } else {
                $error = "Fehler: $conn->error.";
            }
        }
        
        $stmt->close();
        $conn->close();
    }
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login-System - Registrierung</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="icon" type="image/x-icon" href="https://berniiii.isfucking.pro/EonvYr.ico">
</head>
<body>
    <?php include('modules/navbar.php'); ?>
    <div class="container">
        <?php if (isset($error)) { ?>
            <div class="message top">
                <?php echo $error; ?>
                <span class="close" onclick="closeMessage(this)">×</span>
            </div>
        <?php } ?>
        <?php if (isset($success)) { ?>
            <div class="message success top">
                <?php echo $success; ?>
                <span class="close" onclick="closeMessage(this)">×</span>
            </div>
        <?php } ?>
        <form action="register.php" method="POST">
            <h3>Registrierung</h3>
            <div class="input-container">
                <input type="text" name="username" placeholder="Benutzername eingeben" required/>
            </div>
            <div class="input-container">
                <input type="password" name="password" placeholder="Passwort eingeben" required/>
            </div>
            <div class="input-container border-bottom">
                <label for="rang">Benutzer Rang: </label>
                <select name="rang" id="rang">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <input type="submit" value="Registrieren">
            <a href="logout.php">Zurück zum Login</a>
        </form>
    </div>

</body>
</html>
