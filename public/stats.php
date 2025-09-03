<?php
require '../vendor/autoload.php';
use App\Database\DB;

session_start();
$db = (new DB())->getConnection();

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$airlineQuery = $db->query("SELECT airline, COUNT(*) as count FROM flights GROUP BY airline");
$flightsPerAirline = $airlineQuery->fetchAll(PDO::FETCH_ASSOC);

$statusQuery = $db->query("SELECT status, COUNT(*) as count FROM flights WHERE status IN ('scheduled', 'active', 'landed', 'cancelled') GROUP BY status");
$flightsByStatus = $statusQuery->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<?php include 'navbar.php'; ?>
<title>Flight Statistics</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" href="styles/catppuccin.css">
</head>
<body>
  <div class="container">
    <header>
      <div class="title">
        <span class="badge">VBIS</span>
        <div>
          <h1>Flight Statistics</h1>
          <div class="subtitle">Visual overview of flight data</div>
        </div>
      </div>

      <div style="margin-top:16px; display:flex; gap:12px;">
         <?php if ($isAdmin): ?>
        <div class="buttons">
<form id="importForm" action="importedStats.php" method="POST" enctype="multipart/form-data" style="display:inline;">
    <input id="importFile" type="file" name="importFile" accept=".json,.xml" style="display:none;" required>
    <button id="importBtn" class="import-submit" type="button">Import & View Stats</button>
</form>

    <button class="clear-btn" id="clear" onclick="window.location='exportFlights.php?format=json'">Export JSON</button>
    <button class="clear-btn" id="clear" onclick="window.location='exportFlights.php?format=xml'">Export XML</button>
</div><?php endif; ?>

       </div>
    </header>

    <section class="card">
      <h2>Flights per Airline</h2>
      <canvas id="chartAirline" height="150"></canvas>
    </section>

    <section class="card"style="width: 70%; height: 70%; margin: auto;">
      <h2>Flights by Status</h2>
      <canvas id="chartStatus" height="150"></canvas>
    </section>
  </div>

<script>
const flightsPerAirline = <?= json_encode($flightsPerAirline) ?>;
const flightsByStatus = <?= json_encode($flightsByStatus) ?>;

// bar
const ctxAirline = document.getElementById('chartAirline').getContext('2d');
new Chart(ctxAirline, {
  type: 'bar',
  data: {
    labels: flightsPerAirline.map(f => f.airline),
    datasets: [{
      label: 'Number of Flights',
      data: flightsPerAirline.map(f => f.count),
      backgroundColor: '#c6a0f6'
    }]
  },
  options: {
   plugins: {
            legend: {
                labels: {
                    color: '#cad3f5'
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: '#cad3f5'
                }
            },
            y: {
                ticks: {
                    color: '#cad3f5',
                    font: {
                        size: 12
                    }
                }
            }
        }
    }
});

// pie
const ctxStatus = document.getElementById('chartStatus').getContext('2d');
new Chart(ctxStatus, {
  type: 'pie',
  data: {
    labels: flightsByStatus.map(f => f.status),
    datasets: [{
      data: flightsByStatus.map(f => f.count),
      backgroundColor: ['#eed49f','#a6da95','#8aadf4','#ed8796'] // scheduled, active, landed, cancelled
    }]
  },
  options: {
    plugins: { legend: { position: 'bottom', labels: { color: '#cad3f5' } } }
  }
});
document.addEventListener("DOMContentLoaded", () => {
    const fileInput = document.getElementById("importFile");
    const importBtn = document.getElementById("importBtn");
    const form = document.getElementById("importForm");

    importBtn.addEventListener("click", () => {
        fileInput.click();
    });

    fileInput.addEventListener("change", () => {
        if (fileInput.files.length > 0) {
            form.submit();
        }
    });
});

</script>
</body>
</html>
