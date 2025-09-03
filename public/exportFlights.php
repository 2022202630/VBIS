<?php
require '../vendor/autoload.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Access denied";
    exit;
}

use App\Database\DB;

$format = $_GET['format'] ?? 'json';
if (!in_array($format, ['json', 'xml'])) {
    die("Invalid export format.");
}

try {
    $db = (new DB())->getConnection();
    $stmt = $db->query("SELECT * FROM flights ORDER BY id DESC");
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="flights_export.json"');
        echo json_encode(['flights' => $flights], JSON_PRETTY_PRINT);
        exit;
    }
    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="flights_export.xml"');

    $xml = new SimpleXMLElement('<flights/>');
    foreach ($flights as $flight) {
        $flightNode = $xml->addChild('flight');
        foreach ($flight as $key => $value) {
            $flightNode->addChild($key, htmlspecialchars($value));
        }
    }
    echo $xml->asXML();
    exit;

} catch (Exception $e) {
    die("Export failed: " . $e->getMessage());
}
?>
