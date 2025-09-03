<?php
require '../vendor/autoload.php';

use App\Database\DB;

session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$db = (new DB())->getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "All fields are required.";
    } else {
        $stmt = $db->prepare("SELECT id, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $error = "Invalid username or password.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'];

            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login</title>
<style>
<?php include 'styles/catppuccin.css'; ?>
.form-container{
  max-width:400px;margin:50px auto;padding:20px;
  background:var(--surface0);
  border:1px solid rgba(198,160,246,.2);
  border-radius:var(--radius);
  box-shadow:var(--shadow);
}
h1{text-align:center;margin-bottom:20px;color:var(--lavender)}
label{display:block;margin-bottom:6px;color:var(--subtext)}
input{
  width:100%;padding:10px;border-radius:12px;
  border:1px solid rgba(198,160,246,.15);
  background:var(--mantle);color:var(--text);
  margin-bottom:14px;
}
input:focus{
  border-color:var(--mauve);
  box-shadow:var(--ring);
}
button{
  width:100%;padding:10px;
  background:linear-gradient(135deg, rgba(198,160,246,.25), rgba(183,189,248,.15));
  border:1px solid rgba(198,160,246,.4);
  color:var(--mauve);border-radius:12px;font-weight:700;
  cursor:pointer;transition:.2s;
}
button:hover{filter:brightness(1.08)}
.error{color:var(--red);text-align:center;margin-bottom:10px}
.switch{text-align:center;margin-top:10px;color:var(--subtext)}
.switch a{color:var(--mauve);text-decoration:none;font-weight:bold}
.switch a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="form-container">
  <h1>Login</h1>
  <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
  <form method="POST">
    <label for="username">Username</label>
    <input type="text" name="username" id="username" required>

    <label for="password">Password</label>
    <input type="password" name="password" id="password" required>

    <button type="submit">Login</button>
  </form>
  <div class="switch">
    Don’t have an account? <a href="register.php">Register</a>
  </div>
</div>
</body>
</html>
