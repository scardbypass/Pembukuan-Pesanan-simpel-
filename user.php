<?php
require "config.php";
must_login();
if(is_admin()){ header("Location: admin.php"); exit; }
$user = $_SESSION['user'];

if(isset($_POST['add_order'])){
  $pid=(int)$_POST['product_id']; $qty=max(1,(int)$_POST['qty']); $ship=max(0,(int)$_POST['shipping']);
  $p=$pdo->prepare("SELECT * FROM products WHERE id=? AND status='active'");
  $p->execute([$pid]); $product=$p->fetch();
  if($product){
    $total=($product['price']*$qty)+$ship;
    $stmt=$pdo->prepare("INSERT INTO orders (user_id,product_id,buyer_name,phone,address,qty,product_price,shipping,total,note) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$user['id'],$pid,$_POST['buyer_name'],$_POST['phone'],$_POST['address'],$qty,$product['price'],$ship,$total,$_POST['note']]);
  }
  header("Location: user.php"); exit;
}

if(isset($_POST['update_status'])){
  $stmt=$pdo->prepare("UPDATE orders SET payment=?, delivery=? WHERE id=? AND user_id=?");
  $stmt->execute([$_POST['payment'],$_POST['delivery'],$_POST['order_id'],$user['id']]);
  header("Location: user.php"); exit;
}

if(isset($_GET['delete'])){
  $stmt=$pdo->prepare("UPDATE orders SET deleted_at=NOW() WHERE id=? AND user_id=?");
  $stmt->execute([(int)$_GET['delete'],$user['id']]);
  header("Location: user.php"); exit;
}

$products=$pdo->query("SELECT * FROM products WHERE status='active' ORDER BY name ASC")->fetchAll();
$stmt=$pdo->prepare("SELECT o.*,p.name product_name FROM orders o JOIN products p ON p.id=o.product_id WHERE o.user_id=? AND o.deleted_at IS NULL ORDER BY o.id DESC");
$stmt->execute([$user['id']]); $orders=$stmt->fetchAll();
$totalMoney=array_sum(array_column($orders,'total')); $totalItems=array_sum(array_column($orders,'qty'));
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>User</title><link rel="stylesheet" href="style.css"></head>
<body><header><div class="wrap top"><div><div class="brand">ðŸ‘¤ <?= htmlspecialchars($user['full_name']) ?></div><div class="muted">Panel user jualan</div></div><a class="btn gray small" href="logout.php">Logout</a></div></header>
<div class="wrap"><div class="grid">
<div class="card c4"><h2>Tambah Pesanan</h2><form method="post">
<label>Produk</label><select name="product_id" id="product" onchange="calc()" required>
<?php foreach($products as $p): ?><option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>"><?= htmlspecialchars($p['name']) ?> - <?= rupiah($p['price']) ?></option><?php endforeach; ?>
</select>
<label>Jumlah</label><input type="number" name="qty" id="qty" value="1" min="1" oninput="calc()">
<label>Ongkir Opsional</label><input type="number" name="shipping" id="shipping" value="0" min="0" oninput="calc()">
<label>Total Otomatis</label><input id="totalPreview" readonly>
<label>Nama Pemesan</label><input name="buyer_name" required>
<label>Nomor HP</label><input name="phone" required>
<label>Alamat</label><input name="address" required>
<label>Catatan</label><input name="note">
<button name="add_order">Simpan Pesanan</button></form></div>

<div class="card c8"><h2>Dashboard Saya</h2><div class="grid">
<div class="stat c4">Pesanan<b><?= count($orders) ?></b></div><div class="stat c4">Barang<b><?= $totalItems ?></b></div><div class="stat c4">Total<b><?= rupiah($totalMoney) ?></b></div>
</div><h3>Pesanan Saya</h3><p><a class="btn green small" href="export.php?type=excel">Export Excel</a> <a class="btn red small" target="_blank" href="export.php?type=pdf">Export PDF</a></p><div class="scroll"><table>
<tr><th>Pemesan</th><th>Barang</th><th>Catatan</th><th>HP</th><th>Pembayaran</th><th>Pengiriman</th><th>Total</th><th>Aksi</th></tr>
<?php foreach($orders as $o): ?><tr>
<td><b><?= htmlspecialchars($o['buyer_name']) ?></b><br><small><?= htmlspecialchars($o['address']) ?></small></td>
<td><?= htmlspecialchars($o['product_name']) ?> x<?= $o['qty'] ?><br><small>Ongkir <?= rupiah($o['shipping']) ?></small></td>
<td><?= $o['note'] ? htmlspecialchars($o['note']) : '-' ?></td>
<td><a target="_blank" href="<?= wa_link($o['phone']) ?>"><?= htmlspecialchars($o['phone']) ?></a></td>
<td><form method="post"><input type="hidden" name="order_id" value="<?= $o['id'] ?>"><select name="payment">
<?php foreach(['Belum Dipilih','Cash','QRIS','Transfer'] as $v): ?><option <?= $o['payment']===$v?'selected':'' ?>><?= $v ?></option><?php endforeach; ?>
</select></td><td><select name="delivery">
<?php foreach(['Belum Dikirim','Sedang Dikirim','Sudah Dikirim'] as $v): ?><option <?= $o['delivery']===$v?'selected':'' ?>><?= $v ?></option><?php endforeach; ?>
</select></td>
<td><b><?= rupiah($o['total']) ?></b><br><?= $o['acc_status']?'<span class="badge ok">ACC</span>':'<span class="badge no">Belum ACC</span>' ?></td>
<td><button class="small" name="update_status">Update</button></form><a class="btn red small" onclick="return confirm('Hapus pesanan?')" href="?delete=<?= $o['id'] ?>">Hapus</a></td>
</tr><?php endforeach; ?></table></div></div></div></div>
<script>
function rupiah(n){return new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",maximumFractionDigits:0}).format(n||0)}
function calc(){let opt=document.querySelector("#product option:checked");let price=parseInt(opt?.dataset.price||0);let qty=parseInt(document.querySelector("#qty").value||0);let ship=parseInt(document.querySelector("#shipping").value||0);document.querySelector("#totalPreview").value=rupiah((price*qty)+ship)}
calc();
</script>
<footer class="copyright">
    © 2026 SCARD-PROJECT. All Rights Reserved.
</footer>
</body></html>
