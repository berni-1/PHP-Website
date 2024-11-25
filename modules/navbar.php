<div class="navbar">
    <div class="left">
        <a href="index.php">
            <button class="value <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                <i class="ph ph-house"></i>Home
            </button>
        </a>
    </div>

    <?php if ($role == 'admin') { ?>
        <a href="register.php">
            <button class="value <?php echo (basename($_SERVER['PHP_SELF']) == 'register.php') ? 'active' : ''; ?>">
                <i class="ph ph-users"></i>Add User
            </button>
        </a>
    <?php } ?>

    <div class="dropdown">
        <button class="value">
            <img src="avatar.gif" alt="" class="avatar">
            <?php echo $username; ?>
        </button>
        <div class="dropdown-content">
            <a href="settings.php" class="settings-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>">Settings</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dropdown = document.querySelector('.dropdown');
        const dropdownButton = dropdown.querySelector('.value');

        dropdownButton.addEventListener('click', () => {
            dropdown.classList.toggle('open');
        });

        document.addEventListener('click', (event) => {
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('open');
            }
        });
    });
</script>