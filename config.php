<?php
session_start();

$DB_HOST = "localhost";
$DB_NAME = "scac8254_jualankue";
$DB_USER = "scac8254_jualankue";
$DB_PASS = "scac8254_jualankue";

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (PDOException $e) {
  die("Database gagal konek: " . $e->getMessage());
}

function rupiah($n){ return "Rp" . number_format((int)$n,0,",","."); }
function wa_link($phone){
  $n = preg_replace('/\D/','',$phone);
  if(strpos($n,"0")===0) $n = "62".substr($n,1);
  return "https://wa.me/".$n;
}
function must_login(){
  if(!isset($_SESSION['user'])){ header("Location: index.php"); exit; }
}
function is_admin(){ return isset($_SESSION['user']) && $_SESSION['user']['role']==='admin'; }
?>
