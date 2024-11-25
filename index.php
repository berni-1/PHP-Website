<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    include('db.php');

    $username = $_SESSION['username'];
    $role = $_SESSION['role'];
    $wetterort = NULL;

    $wetter_enable = isset($_SESSION['enable_wetter']) ? $_SESSION['enable_wetter'] : 1;

    if (!isset($_SESSION['wetterort'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT wetterort FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($db_wetterort);
        $stmt->fetch();
        $stmt->close();

        if (!empty($db_wetterort)) {
            $_SESSION['wetterort'] = $db_wetterort;
            $wetterort = $db_wetterort;
        } else {
            $wetterort = NULL;
        }
    } else {
        $wetterort = $_SESSION['wetterort'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wetter-ort'])) {
        $ort = $_POST['wetter-ort'];
        $user_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("UPDATE users SET wetterort = ? WHERE id = ?");
        $stmt->bind_param("si", $ort, $user_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['wetterort'] = $ort;
        $wetterort = $ort;
        $success = "Wetter Ort erfolgreich auf $wetterort geändert";
    }
?>

<!DOCTYPE html>
<html lang="de">
<?php
    $page_title = 'Dashboard';
    include('modules/head.php');
?>
<body>

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

    <script>
        setTimeout(function() {
            $('.message').fadeOut();
        }, 3000);

        function closeMessage(element) {
            $(element).closest('.message').fadeOut();
        }
    </script>

    <?php include('modules/navbar.php'); ?>

    <div class="container">
        <h1>Startseite</h1>
        <h3>Willkommen, <?php echo $username; ?>!</h3>
        <?php if ($wetter_enable) { ?>
            <?php if ($wetterort == NULL) { ?>
                <form method="POST">
                    <p>Kein Ort für Wetterdaten angegeben (<?php echo $wetterort; ?>)</p>
                    <div class="input-container border-bottom">
                        <i class="ph ph-caret-right"></i>
                        <input type="text" name="wetter-ort" placeholder="Ort Eingeben" required/>
                    </div>
                    <input type="submit" value="Ort ändern">
                </form>
            <?php } else { ?>
                <div class="weather-container">
                    <div class="weather-card">
                        <div class="weather-header">
                            <h2>Wetter in <span id="city-name">Stadt</span></h2>
                            <p id="date-time">Datum & Uhrzeit</p>
                        </div>
                        <div class="weather-main">
                            <img id="weather-icon" src="https://openweathermap.org/img/wn/02d@2x.png" alt="Wetter Icon" class="weather-icon">
                            <p id="temperature">20°C</p>
                            <p id="weather-description">Klarer Himmel</p>
                        </div>
                        <div class="weather-footer">
                            <p>Wind: <span id="wind-speed">10 km/h</span></p>
                            <p>Feuchtigkeit: <span id="humidity">50%</span></p>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>

    <script>
        const weather_location = "<?php echo $wetterort; ?>";
        const api_url = `https://api.openweathermap.org/data/2.5/weather?q=${weather_location}&units=metric&appid=19682941898454643a4c24cb8173606e`;

        jQuery.ajax({
            url: api_url,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.info(data);

                document.getElementById('city-name').textContent = data.name;
                document.getElementById('date-time').textContent = new Date().toLocaleString();
                const currentTemp = Math.round(data.main.temp);
                document.getElementById('temperature').textContent = `${currentTemp}°C`;
                const weatherDesc = data.weather[0].description;
                document.getElementById('weather-description').textContent = weatherDesc;
                const windSpeed = Math.round(data.wind.speed);
                document.getElementById('wind-speed').textContent = `${windSpeed} km/h`;
                const humidity = data.main.humidity;
                document.getElementById('humidity').textContent = `${humidity}%`;

                const iconCode = data.weather[0].icon;
                const iconUrl = `http://openweathermap.org/img/wn/${iconCode}@2x.png`;
                document.getElementById('weather-icon').src = iconUrl;
            },
            error: function(error) {
                console.error('Fehler beim Abrufen der Wetterdaten:', error);
            }
        });
    </script>

</body>
</html>