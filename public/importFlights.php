<?php
require '../vendor/autoload.php';
use App\Database\DB;
use App\Api\FlightApi;

header('Content-Type: application/json');

$db = (new DB())->getConnection();
$api = new FlightApi();
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Access denied";
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
$count = $input['count'] ?? 30;

try {
    $flights = $api->fetchFlights($count);

    $stmt = $db->prepare("INSERT INTO flights 
        (flight_number, airline, departure_airport, departure_time, arrival_airport, arrival_time, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    $imported = 0;
    foreach ($flights as $flight) {
        $stmt->execute([
            $flight['flight']['iata'] ?? 'N/A',
            $flight['airline']['name'] ?? 'Unknown',
            $flight['departure']['airport'] ?? 'Unknown',
            $flight['departure']['scheduled'] ?? null,
            $flight['arrival']['airport'] ?? 'Unknown',
            $flight['arrival']['scheduled'] ?? null,
            $flight['flight_status'] ?? 'N/A'
        ]);
        $imported++;
    }

    echo json_encode(['success'=>true, 'imported'=>$imported]);
} catch(Exception $e){
    echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
}
