<?php
require '../vendor/autoload.php';
use App\Database\DB;
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){
    echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
}

$db = (new DB())->getConnection();
$data = $_POST;

$stmt = $db->prepare("INSERT INTO flights 
    (flight_number, airline, departure_airport, arrival_airport, departure_time, arrival_time, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");

try {
    $stmt->execute([
        $data['flight_number'],
        $data['airline'],
        $data['departure_airport'],
        $data['arrival_airport'],
        $data['departure_time'],
        $data['arrival_time'],
        $data['status']
    ]);
    echo json_encode(['success'=>true]);
}catch(Exception $e){
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
