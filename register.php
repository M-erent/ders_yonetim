<?php
// register.php
require_once 'db/db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];

    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $name, $email, $password, $role);
    if ($stmt->execute()) {
        header('Location: login.php');
        exit();
    } else {
        $error = 'Kayıt sırasında hata: ' . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Kayıt Ol</title>
  <link rel="stylesheet" href="assets/css/style.css">
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
  width: 100%;
  padding: 12px 40px 12px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
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

  <h2>Kayıt Ol</h2>
  <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
  <form method="post">
    İsim:     <input type="text"    name="name"     required><br><br>
    Email:    <input type="email"   name="email"    required><br><br>
    Şifre:    <input type="password"name="password" required><br><br>
    Rol:      <select name="role">
                <option value="Öğrenci">Öğrenci</option>
                
              </select><br><br>
    <button type="submit">Kayıt Ol</button>
  </form>
  <p>Öğretmen olmak istiyor musun? <a href="teacher_apply.php">Başvur</a></p>
  <p>Zaten hesabın var mı? <a href="login.php">Giriş Yap</a></p>
</body>
</html>
