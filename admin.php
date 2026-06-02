<?php
require "config.php";
must_login();
if(!is_admin()){ header("Location: user.php"); exit; }

if(isset($_POST['add_product'])){
  $stmt=$pdo->prepare("INSERT INTO products (name,price) VALUES (?,?)");
  $stmt->execute([$_POST['name'],(int)$_POST['price']]); header("Location: admin.php"); exit;
}
if(isset($_POST['edit_product'])){
  $stmt=$pdo->prepare("UPDATE products SET name=?, price=? WHERE id=?");
  $stmt->execute([$_POST['name'],(int)$_POST['price'],(int)$_POST['product_id']]); header("Location: admin.php"); exit;
}
if(isset($_GET['delete_product'])){
  $stmt=$pdo->prepare("UPDATE products SET status='deleted' WHERE id=?");
  $stmt->execute([(int)$_GET['delete_product']]); header("Location: admin.php"); exit;
}
if(isset($_POST['add_user'])){
  $stmt=$pdo->prepare("INSERT INTO users (username,full_name,role) VALUES (?,?,?)");
  $stmt->execute([$_POST['username'],$_POST['full_name'],$_POST['role']]); header("Location: admin.php"); exit;
}
if(isset($_POST['acc_user'])){
  $stmt=$pdo->prepare("UPDATE orders SET acc_status=1 WHERE user_id=? AND deleted_at IS NULL");
  $stmt->execute([(int)$_POST['user_id']]); header("Location: admin.php"); exit;
}
if(isset($_POST['acc_all'])){
  $pdo->query("UPDATE orders SET acc_status=1 WHERE deleted_at IS NULL"); header("Location: admin.php"); exit;
}

if(isset($_POST['delete_user_orders'])){
  $stmt=$pdo->prepare("UPDATE orders SET deleted_at=NOW() WHERE user_id=? AND deleted_at IS NULL");
  $stmt->execute([(int)$_POST['user_id']]); header("Location: admin.php"); exit;
}
if(isset($_POST['delete_all_orders'])){
  $pdo->query("UPDATE orders SET deleted_at=NOW() WHERE deleted_at IS NULL"); header("Location: admin.php"); exit;
}


$products=$pdo->query("SELECT * FROM products WHERE status='active' ORDER BY id DESC")->fetchAll();
$users=$pdo->query("SELECT * FROM users ORDER BY role ASC, full_name ASC")->fetchAll();
$summary=$pdo->query("SELECT u.id,u.full_name,u.username,COUNT(o.id) total_order,COALESCE(SUM(o.total),0) total_money,MIN(o.acc_status) min_acc FROM users u LEFT JOIN orders o ON o.user_id=u.id AND o.deleted_at IS NULL WHERE u.role='user' GROUP BY u.id ORDER BY u.full_name ASC")->fetchAll();
$filter=$_GET['user']??'all'; $where="WHERE o.deleted_at IS NULL"; $params=[];
if($filter!=='all'){ $where.=" AND o.user_id=?"; $params[]=(int)$filter; }
$stmt=$pdo->prepare("SELECT o.*,u.full_name,p.name product_name FROM orders o JOIN users u ON u.id=o.user_id JOIN products p ON p.id=o.product_id $where ORDER BY o.id DESC");
$stmt->execute($params); $orders=$stmt->fetchAll();
$totalAll=$pdo->query("SELECT COALESCE(SUM(total),0) t FROM orders WHERE deleted_at IS NULL")->fetch()['t'];
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin</title><link rel="stylesheet" href="style.css"></head>
<body><header><div class="wrap top"><div><div class="brand">🛡️ Admin Panel</div><div class="muted">Admin hanya ACC total uang user</div></div><a class="btn gray small" href="logout.php">Logout</a></div></header>
<div class="wrap"><div class="grid">
<div class="card c4"><h2>Produk</h2><form method="post"><input name="name" placeholder="Nama produk" required><input name="price" type="number" placeholder="Harga" required><button name="add_product">Tambah Produk</button></form>
<?php foreach($products as $p): ?>
  <form method="post" style="background:rgba(255,255,255,.55);border:1px solid var(--line);border-radius:18px;padding:10px;margin:10px 0">
    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
    <label>Nama</label>
    <input name="name" value="<?= htmlspecialchars($p['name']) ?>" required>
    <label>Harga</label>
    <input name="price" type="number" value="<?= $p['price'] ?>" required>
    <button class="small green" name="edit_product">Simpan Edit</button>
    <a class="btn red small" onclick="return confirm('Hapus produk?')" href="?delete_product=<?= $p['id'] ?>">Hapus</a>
  </form>
<?php endforeach; ?></div>

<div class="card c4"><h2>User</h2><form method="post"><input name="username" placeholder="username login" required><input name="full_name" placeholder="Nama lengkap" required><select name="role"><option value="user">User</option><option value="admin">Admin</option></select><button name="add_user">Tambah User</button></form>
<?php foreach($users as $u): ?><p>👤 <b><?= htmlspecialchars($u['username']) ?></b> - <?= htmlspecialchars($u['role']) ?></p><?php endforeach; ?></div>

<div class="card c4"><h2>Dashboard</h2>
<div class="stat">Total Semua<b><?= rupiah($totalAll) ?></b></div><br>
<form method="post"><button class="green" name="acc_all">ACC Semua User</button></form>
<p><a class="btn green small" href="export.php?type=excel&scope=all">Export Excel All</a> <a class="btn red small" target="_blank" href="export.php?type=pdf&scope=all">Export PDF All</a></p>
<form method="post" onsubmit="return confirm('Yakin hapus SEMUA pesanan? Data akan masuk deleted_at.');"><button class="red" name="delete_all_orders">Hapus Semua Pesanan</button></form>
</div>

<div class="card c12"><h2>ACC Pembukuan Per User</h2><div class="scroll"><table><tr><th>User</th><th>Pesanan</th><th>Total Uang</th><th>Status</th><th>Aksi</th></tr>
<?php foreach($summary as $s): $ok=$s['total_order']>0 && (int)$s['min_acc']===1; ?><tr>
<td><?= htmlspecialchars($s['full_name']) ?><br><small>@<?= htmlspecialchars($s['username']) ?></small></td><td><?= $s['total_order'] ?></td><td><b><?= rupiah($s['total_money']) ?></b></td><td><?= $ok?'<span class="badge ok">Lunas</span>':'<span class="badge no">Utang</span>' ?></td><td>
<form method="post" style="display:inline"><input type="hidden" name="user_id" value="<?= $s['id'] ?>"><button class="green small" name="acc_user">Sudah Pay</button></form>
<a class="btn green small" href="export.php?type=excel&scope=user&user_id=<?= $s['id'] ?>">Excel</a>
<a class="btn red small" target="_blank" href="export.php?type=pdf&scope=user&user_id=<?= $s['id'] ?>">PDF</a>
<form method="post" style="display:inline" onsubmit="return confirm('Hapus semua pesanan user ini?')"><input type="hidden" name="user_id" value="<?= $s['id'] ?>"><button class="red small" name="delete_user_orders">Hapus Pesanan</button></form>
</td>
</tr><?php endforeach; ?></table></div></div>

<div class="card c12"><h2>Riwayat Semua Pesanan</h2><p><a class="btn green small" href="export.php?type=excel&scope=all">Export Excel</a> <a class="btn red small" target="_blank" href="export.php?type=pdf&scope=all">Export PDF</a></p><form method="get"><select name="user" onchange="this.form.submit()"><option value="all">Semua User</option><?php foreach($summary as $u): ?><option value="<?= $u['id'] ?>" <?= $filter==$u['id']?'selected':'' ?>><?= htmlspecialchars($u['full_name']) ?></option><?php endforeach; ?></select></form>
<div class="scroll"><table><tr><th>Tanggal</th><th>User</th><th>Pemesan</th><th>Barang</th><th>Catatan</th><th>Bayar</th><th>Kirim</th><th>Total</th></tr>
<?php foreach($orders as $o): ?><tr><td><?= $o['created_at'] ?></td><td><?= htmlspecialchars($o['full_name']) ?></td><td><?= htmlspecialchars($o['buyer_name']) ?><br><small><?= htmlspecialchars($o['address']) ?></small></td><td><?= htmlspecialchars($o['product_name']) ?> x<?= $o['qty'] ?><br><small>Ongkir <?= rupiah($o['shipping']) ?></small></td><td><?= $o['note'] ? htmlspecialchars($o['note']) : '-' ?></td><td><?= htmlspecialchars($o['payment']) ?></td><td><?= htmlspecialchars($o['delivery']) ?></td><td><b><?= rupiah($o['total']) ?></b></td></tr><?php endforeach; ?>
</table></div></div></div></div>
<footer class="copyright">
    © 2026 SCARD-PROJECT. All Rights Reserved.
</footer>
</body></html>
