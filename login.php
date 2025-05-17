<?php
// login.php
session_start();
require_once __DIR__ . '/db/db_connect.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($user = $res->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role']      = $user['role'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Şifre yanlış!';
        }
    } else {
        $error = 'Böyle bir kullanıcı yok!';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Giriş Yap - Yeni Tema</title>
  <link rel="stylesheet" href="assets/css/style.css?v=1">
</head>
<style>.login-page {
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #1f3c88, #3c8d2f);
  min-height: 100vh;
  margin: 0;
}

.login-page .login-wrapper {
  width: 100%;
  max-width: 400px;
  padding: 20px;
}

.login-page .login-card {
  background: #fff;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.3);
  animation: fadeIn 0.5s ease-out;
}

.login-page .login-card h2 {
  margin-bottom: 20px;
  text-align: center;
  color: #1f3c88;
}

.login-page .error {
  background: #ffe0e0;
  color: #d8000c;
  padding: 12px;
  margin-bottom: 15px;
  border-radius: 4px;
  text-align: center;
}

.login-page .form-group {
  margin-bottom: 15px;
}

.login-page .form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #333;
}

.login-page .form-group input {
  width: 85%;
  padding: 14px 40px 12px 12px;
  border: 1px solid #ccc;
  border-radius: 5px;
  transition: border-color 0.3s;
}

.login-page .form-group input:focus {
  border-color: #1f3c88;
  outline: none;
}

.login-page .password-wrapper {
  position: relative;
}

.login-page .toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  user-select: none;
}

.login-page .btn {
  width: 100%;
  padding: 12px;
  background: #1f3c88;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.3s;
}

.login-page .btn:hover {
  background: #163066;
}

.login-page .signup-link {
  text-align: center;
  margin-top: 15px;
  font-size: 14px;
}

.login-page .signup-link a {
  color: #1f3c88;
  text-decoration: none;
  font-weight: bold;
}

.login-page .signup-link a:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .login-page .login-card {
    padding: 20px;
  }
}</style>
<body class="login-page">
  <div class="login-wrapper">
    <div class="login-card">
      <h2>Giriş Yap</h2>
      <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="post">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="email@example.com"
            required
            autocomplete="username"
          >
        </div>
        <div class="form-group">
          <label for="password">Şifre</label>
          <div class="password-wrapper">
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Şifreniz"
              required
              autocomplete="current-password"
            >
            <span class="toggle-password" onclick="togglePassword()">👁</span>
          </div>
        </div>
        <button type="submit" class="btn">Giriş Yap</button>
      </form>
      <p class="signup-link">
        Hesabın yok mu? <a href="register.php">Kayıt Ol</a>
      </p>
      <p>Admin misin? <a href="admin_login.php">Admin Girişi</a></p>
    </div>
  </div>
  <script>
    function togglePassword() {
      const pw = document.getElementById('password');
      pw.type = pw.type === 'password' ? 'text' : 'password';
    }
  </script>
</body>
</html>
