<?php
require '../vendor/autoload.php';
use App\Database\DB;

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    echo "<h2 style='text-align:center; margin-top:50px; color:#ed8796;'>ADMIN ONLY!!!</h2>";
    exit;
}

$db = (new DB())->getConnection();


if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['importFile'])) {
    header("Location: stats.php");
    exit;
}

$file = $_FILES['importFile']['tmp_name'];
$ext = strtolower(pathinfo($_FILES['importFile']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['json', 'xml'])) {
    die("Invalid format.");
}

$flights = [];

if ($ext === 'json') {
    $data = file_get_contents($file);
    $flights = json_decode($data, true);
} else {
    $xml = simplexml_load_file($file);
    $flights = json_decode(json_encode($xml), true);
}

if (isset($flights['flights'])) {
    $flights = $flights['flights'];
}

$airlineCounts = [];
$statusCounts = [];

foreach ($flights as $flight) {
    $airline = $flight['airline'] ?? 'Unknown';
    $status = $flight['status'] ?? 'Unknown';
    $airlineCounts[$airline] = ($airlineCounts[$airline] ?? 0) + 1;
    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Imported Flight Stats</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="styles/catppuccin.css">
</head>
<body>
<div class="container">
  <h1>Imported Flight Statistics</h1>
  <div class="buttons">
    <button onclick="window.location='stats.php'">Back to Main Stats</button>
  </div>

  <div class="card">
    <h2>Flights per Airline</h2>
    <canvas id="chartAirline" height="150"></canvas>
  </div>

  <div class="card">
    <h2>Flights by Status</h2>
    <canvas id="chartStatus" height="150"></canvas>
  </div>
</div>

<script>
const flightsPerAirline = <?= json_encode(array_map(
    fn($airline,$count)=>['airline'=>$airline,'count'=>$count],
    array_keys($airlineCounts), $airlineCounts
)) ?>;

const flightsByStatus = <?= json_encode(array_map(
    fn($status,$count)=>['status'=>$status,'count'=>$count],
    array_keys($statusCounts), $statusCounts
)) ?>;

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

const ctxStatus = document.getElementById('chartStatus').getContext('2d');
new Chart(ctxStatus, {
  type: 'pie',
  data: {
    labels: flightsByStatus.map(f => f.status),
    datasets: [{
      data: flightsByStatus.map(f => f.count),
      backgroundColor: ['#eed49f','#a6da95','#8aadf4','#ed8796']
    }]
  },
  options: {
    plugins: { legend: { position: 'bottom', labels: { color: 'var(--text)' } } }
  }
});
</script>
</body>
</html>
