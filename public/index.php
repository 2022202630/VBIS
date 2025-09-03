<?php
require '../vendor/autoload.php';
use App\Database\DB;

include 'navbar.php';

$db = (new DB())->getConnection();

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$totalFlights = $db->query("SELECT COUNT(*) FROM flights")->fetchColumn();
$activeFlights = $db->query("SELECT COUNT(*) FROM flights WHERE status='active'")->fetchColumn();
$scheduledFlights = $db->query("SELECT COUNT(*) FROM flights WHERE status='scheduled'")->fetchColumn();
$cancelledFlights = $db->query("SELECT COUNT(*) FROM flights WHERE status='cancelled'")->fetchColumn();
$landedFlights = $db->query("SELECT COUNT(*) FROM flights WHERE status='landed'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VBIS Flights</title>
    <link rel="stylesheet" href="styles/catppuccin.css">
</head>
<body>
<div class="container">
    <div class="card" style="text-align: center; padding: 30px;">
        <h1>VBIS Project: Plane Statistics</h1>
        <p class="subtitle">View flight statistics, import flights from Aviastionstack's API and import/export.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-title">Total Flights</span>
            <span class="stat-number"><?php echo $totalFlights; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-title">Active Flights</span>
            <span class="stat-number" style="color: var(--green);"><?php echo $activeFlights; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-title">Scheduled Flights</span>
            <span class="stat-number" style="color: var(--yellow);"><?php echo $scheduledFlights; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-title">Cancelled Flights</span>
            <span class="stat-number" style="color: var(--red);"><?php echo $cancelledFlights; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-title">Landed Flights</span>
            <span class="stat-number" style="color: var(--blue);"><?php echo $landedFlights; ?></span>
        </div>
    </div>

    <div class="quick-links">
        <a href="flights.php" class="nav-btn">View Flights</a>
        <a href="stats.php" class="nav-btn">View Statistics</a>
        <?php if ($isAdmin): ?><a href="importedStats.php" class="nav-btn">Import / Export</a><?php endif; ?>
    </div>
</div>
</body>
</html>
