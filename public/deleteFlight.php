<?php
require '../vendor/autoload.php';
use App\Database\DB;
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){
    echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
}

$db = (new DB())->getConnection();
$data = json_decode(file_get_contents('php://input'), true);

$stmt = $db->prepare("DELETE FROM flights WHERE id=?");
try {
    $stmt->execute([$data['id']]);
    echo json_encode(['success'=>true]);
}catch(Exception $e){
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
