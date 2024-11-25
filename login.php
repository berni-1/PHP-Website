<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('db.php');
    
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            
            header("Location: index.php");
        } else {
            $error = "Falsches Passwort!";
        }
    } else {
        $error = "Benutzer nicht gefunden!";
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login-System - Login</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="icon" type="image/x-icon" href="https://berniiii.isfucking.pro/EonvYr.ico">
    <script>
        function closeMessage(element) {
            element.parentElement.style.display = 'none';
        }
    </script>
</head>
<body>

    <div class="container">
        <header>Login</header>
        <?php if (isset($error)) { ?>
            <div class="message top">
                <?php echo $error; ?>
                <span class="close" onclick="closeMessage(this)">×</span>
            </div>
        <?php } ?>
        <?php if (isset($success)) { ?>
            <div class="message success">
                <?php echo $success; ?>
                <span class="close" onclick="closeMessage(this)">×</span>
            </div>
        <?php } ?>
        <form action="login.php" method="POST">
            <div class="input-container">
                <input type="text" name="username" placeholder="Benutzername eingeben" required/>
            </div>
            <div class="input-container border-bottom">
                <input type="password" name="password" placeholder="Passwort eingeben" required/>
            </div>
            <input type="submit" value="Anmelden">
        </form>
    </div>

</body>
</html>
