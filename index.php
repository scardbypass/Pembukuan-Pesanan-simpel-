<?php
require "config.php";
if(isset($_POST['login'])){
  $username = trim($_POST['username']);
  $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? AND status='active' LIMIT 1");
  $stmt->execute([$username]);
  $user = $stmt->fetch();
  if($user){
    $_SESSION['user'] = $user;
    header("Location: ".($user['role']==='admin'?'admin.php':'user.php'));
    exit;
  } else $error = "Username tidak ditemukan / akun nonaktif.";
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login</title><link rel="stylesheet" href="style.css"></head>
<body><header><div class="wrap"><div class="brand">ðŸ“¦ Pembukuan Pesanan</div><div class="muted">By:SCARD-PROJECT.</div></div></header>
<div class="wrap"><div class="card login"><h2>Login</h2>
<?php if(isset($error)): ?><p class="badge no"><?= $error ?></p><?php endif; ?>
<form method="post"><label>Username</label><input name="username" placeholder="Siapa saya" required><button name="login">Masuk</button></form>
<p class="muted">Akses Khusus</p></div></div>
<footer class="copyright">
    © 2026 SCARD-PROJECT. All Rights Reserved.
</footer>
</body></html>
