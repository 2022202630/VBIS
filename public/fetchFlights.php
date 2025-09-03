<?php
require '../vendor/autoload.php';
use App\Database\DB;

$db = (new DB())->getConnection();

$airline = $_GET['airline'] ?? '';
$departure = $_GET['departure'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1)); // default page 1
$limit = 50;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if($airline){
    $where[] = "airline LIKE ?";
    $params[] = "%$airline%";
}
if($departure){
    $where[] = "departure_airport LIKE ?";
    $params[] = "%$departure%";
}
if($status){
    $where[] = "status = ?";
    $params[] = $status;
}

$sql = "SELECT * FROM flights";
if($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY departure_time DESC LIMIT $limit OFFSET $offset";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM flights";
if($where) $countSql .= " WHERE " . implode(" AND ", $where);
$totalFlights = $db->prepare($countSql);
$totalFlights->execute($params);
$totalFlights = $totalFlights->fetchColumn();

echo json_encode([
    'flights' => $flights,
    'total' => intval($totalFlights),
    'page' => $page,
    'pages' => ceil($totalFlights / $limit)
]);
