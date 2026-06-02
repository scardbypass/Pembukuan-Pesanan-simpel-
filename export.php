<?php
require "config.php";
must_login();

$type = $_GET['type'] ?? 'excel';
$scope = $_GET['scope'] ?? 'mine';
$user_id = $_GET['user_id'] ?? 'all';

$where = "WHERE o.deleted_at IS NULL";
$params = [];

if(!is_admin()){
    $where .= " AND o.user_id=?";
    $params[] = $_SESSION['user']['id'];
} else {
    if($scope === 'user' && $user_id !== 'all'){
        $where .= " AND o.user_id=?";
        $params[] = (int)$user_id;
    }
}

$stmt = $pdo->prepare("
SELECT o.*, u.full_name, u.username, p.name product_name
FROM orders o
JOIN users u ON u.id=o.user_id
JOIN products p ON p.id=o.product_id
$where
ORDER BY o.id DESC
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$title = is_admin() ? "Riwayat Pesanan Admin" : "Riwayat Pesanan ".$_SESSION['user']['full_name'];

if($type === 'excel'){
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=riwayat-pesanan-".date('Ymd-His').".xls");
    echo "\xEF\xBB\xBF";
    echo "<table border='1'>";
    echo "<tr><th colspan='10'>$title</th></tr>";
    echo "<tr>
        <th>Tanggal</th><th>User</th><th>Pemesan</th><th>HP</th><th>Alamat</th>
        <th>Barang</th><th>Qty</th><th>Ongkir</th><th>Catatan</th><th>Bayar</th><th>Total</th>
    </tr>";
    foreach($orders as $o){
        echo "<tr>";
        echo "<td>".htmlspecialchars($o['created_at'])."</td>";
        echo "<td>".htmlspecialchars($o['full_name'])."</td>";
        echo "<td>".htmlspecialchars($o['buyer_name'])."</td>";
        echo "<td>".htmlspecialchars($o['phone'])."</td>";
        echo "<td>".htmlspecialchars($o['address'])."</td>";
        echo "<td>".htmlspecialchars($o['product_name'])."</td>";
        echo "<td>".(int)$o['qty']."</td>";
        echo "<td>".(int)$o['shipping']."</td>";
        echo "<td>".htmlspecialchars($o['note'] ?: '-')."</td>";
        echo "<td>".htmlspecialchars($o['payment'])."</td>";
        echo "<td>".(int)$o['total']."</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit;
}

if($type === 'pdf'){
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title) ?></title>
<style>
body{font-family:Arial,sans-serif;color:#111}
h2{margin-bottom:4px}
.small{color:#666;font-size:12px;margin-bottom:15px}
table{width:100%;border-collapse:collapse;font-size:12px}
th,td{border:1px solid #ddd;padding:7px;text-align:left;vertical-align:top}
th{background:#f3f4f6}
.total{margin-top:15px;font-weight:bold}
@media print{button{display:none}body{margin:0}}
</style>
</head>
<body>
<button onclick="window.print()">Print / Save PDF</button>
<h2><?= htmlspecialchars($title) ?></h2>
<div class="small">Dicetak: <?= date('d-m-Y H:i') ?></div>
<table>
<tr>
    <th>Tanggal</th><th>User</th><th>Pemesan</th><th>HP</th><th>Alamat</th>
    <th>Barang</th><th>Qty</th><th>Ongkir</th><th>Catatan</th><th>Bayar</th><th>Total</th>
</tr>
<?php $grand=0; foreach($orders as $o): $grand += (int)$o['total']; ?>
<tr>
    <td><?= htmlspecialchars($o['created_at']) ?></td>
    <td><?= htmlspecialchars($o['full_name']) ?></td>
    <td><?= htmlspecialchars($o['buyer_name']) ?></td>
    <td><?= htmlspecialchars($o['phone']) ?></td>
    <td><?= htmlspecialchars($o['address']) ?></td>
    <td><?= htmlspecialchars($o['product_name']) ?></td>
    <td><?= (int)$o['qty'] ?></td>
    <td><?= rupiah($o['shipping']) ?></td>
    <td><?= $o['note'] ? htmlspecialchars($o['note']) : '-' ?></td>
    <td><?= htmlspecialchars($o['payment']) ?></td>
    <td><?= rupiah($o['total']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<div class="total">Grand Total: <?= rupiah($grand) ?></div>
<script>window.onload=function(){ setTimeout(()=>window.print(),500); }</script>
</body>
</html>
<?php
    exit;
}

header("Location: ".(is_admin() ? "admin.php" : "user.php"));
exit;
?>