<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar">
<div class="nav-left">
    <a href="index.php" class="nav-logo">VBIS Project</a>
    <a href="flights.php" class="nav-link">Flights</a>
    <a href="stats.php" class="nav-link">Statistics</a>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="importedStats.php" class="nav-link">Import / Export</a>
    <?php endif; ?>
</div>


    <div class="nav-right">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="login.php" class="nav-btn">Login</a>
            <a href="register.php" class="nav-btn">Register</a>
        <?php else: ?>
            <span class="nav-user">Logged in as: <?= htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="nav-btn danger">Logout</a>
        <?php endif; ?>
    </div>
</nav>
