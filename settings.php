<?php
    session_start();
    require_once 'db.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $username = $_SESSION['username'];
    $role = $_SESSION['role'];
    $wetterort = $_SESSION['wetterort'];
    $user_id = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_username'])) {
        $new_username = trim($_POST['new_username']);

        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->bind_param("s", $new_username);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $error = "Dieser Benutzername ist bereits vergeben.";
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->bind_param("si", $new_username, $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['username'] = $new_username;
            $success = "Benutzername erfolgreich geändert.";

            session_destroy();
            header("Location: login.php");
            exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $error = "Die neuen Passwörter stimmen nicht überein.";
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($stored_password);
            $stmt->fetch();
            $stmt->close();

            if (!password_verify($current_password, $stored_password)) {
                $error = "Das aktuelle Passwort ist falsch.";
            } else {
                $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);

                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_new_password, $user_id);
                $stmt->execute();
                $stmt->close();

                $success = "Passwort erfolgreich geändert.";

                session_destroy();
                header("Location: login.php");
                exit();
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_location'])) {
        $new_location = $_POST['new_location'];

        $stmt = $conn->prepare("UPDATE users SET wetterort = ? WHERE id = ?");
        $stmt->bind_param("si", $new_location, $user_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['wetterort'] = $new_location;
        $success = "Wetter - Ort erfolgreich auf $new_location geändert.";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enable_wetter'])) {
        $enable_wetter = isset($_POST['enable_wetter']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE users SET enable_wetter = ? WHERE id = ?");
        $stmt->bind_param("ii", $enable_wetter, $user_id);
        $stmt->execute();
        $stmt->close();

        $success = $enable_wetter ? "Wetterfunktion aktiviert." : "Wetterfunktion deaktiviert.";
    }

    $stmt = $conn->prepare("SELECT enable_wetter FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($enable_wetter);
    $stmt->fetch();
    $stmt->close();
?>

<!DOCTYPE html>
<html lang="de">
<?php
    $page_title = 'Settings';
    include('modules/head.php');
?>
<body>
    <div class="navbar">
        <div class="left">
            <a href="index.php">
                <button class="value">
                    <i class="ph ph-house"></i>Home
                </button>
            </a>
        </div>

        <?php if ($role == 'admin') { ?>
            <a href="commands.php">
                <button class="value">
                    <i class="ph ph-terminal"></i>Commands
                </button>
            </a>
        <?php } ?>

        <div class="dropdown">
            <button class="value active">
                <img src="avatar.gif" alt="" class="avatar">
                <?php echo $username; ?>
            </button>
            <div class="dropdown-content">
                <a href="settings.php" class="settings-btn">Settings</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($error)) { ?>
            <div class="message">
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

        <details>
            <summary><h2><i class="ph ph-sort-ascending"></i>Benutzer einstellungen</h2></summary>
            <details>
                <summary class="inner"><h3><i class="ph ph-arrow-bend-down-right"></i>Benutzernamen ändern</h3></summary>
                <form method="POST">
                    <div class="input-container border-bottom">
                        <i class="ph ph-caret-right"></i>
                        <input type="text" name="new_username" placeholder="Neuer Benutzername" required/>
                    </div>
                    <input type="submit" value="Benutzernamen ändern">
                </form>
            </details>

            <details>
                <summary class="inner"><h3><i class="ph ph-arrow-bend-down-right"></i>Passwort ändern</h3></summary>
                <form method="POST">
                    <div class="input-container border-bottom">
                        <i class="ph ph-caret-right"></i>
                        <input type="password" name="current_password" placeholder="Altes Passwort" required/>
                    </div>
                    <div class="input-container">
                        <i class="ph ph-caret-right"></i>
                        <input type="password" name="new_password" placeholder="Neues Passwort" required/>
                    </div>
                    <div class="input-container border-bottom">
                        <i class="ph ph-caret-right"></i>
                        <input type="password" name="confirm_password" placeholder="Neues Passwort wiederholen" required/>
                    </div>
                    <input type="submit" value="Passwort ändern">
                </form>
            </details>
        </details>

        <details>
            <summary><h2><i class="ph ph-sort-ascending"></i>Wetter einstellungen</h2></summary>
            <details>
                <summary class="inner"><h3><i class="ph ph-arrow-bend-down-right"></i>Wetter - Ort ändern</h3></summary>
                <form method="POST">
                    <div class="input-container border-bottom">
                        <i class="ph ph-caret-right"></i>
                        <input type="text" name="new_location" placeholder="Neuer Ort" required/>
                    </div>
                    <input type="submit" value="Ort ändern">
                </form>
            </details>
        </details>
    </div>    
</body>
</html>
