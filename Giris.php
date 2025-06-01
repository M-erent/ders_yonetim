<?php
session_start();
require_once __DIR__ . '/db/db_connect.php';

// Hata ayıklama modu
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Veritabanı bağlantısını kontrol et
    if ($conn->connect_error) {
        $error = 'Veritabanı bağlantı hatası: ' . $conn->connect_error;
    } else {
        $sql = "SELECT * FROM users WHERE email=?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            $error = 'SQL hazırlama hatası: ' . $conn->error;
        } else {
            $stmt->bind_param('s', $email);
            $executed = $stmt->execute();

            if (!$executed) {
                $error = 'Sorgu çalıştırma hatası: ' . $stmt->error;
            } else {
                $res = $stmt->get_result();

                if ($res->num_rows === 0) {
                    $error = 'Bu email adresiyle kayıtlı kullanıcı bulunamadı';
                } else {
                    $user = $res->fetch_assoc();

                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id']   = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['role']      = $user['role'];
                        header('Location: Gosterge_Paneli.php');
                        exit();
                    } else {
                        $error = 'Girdiğiniz şifre yanlış';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Giriş Yap – Öğrenci Öğretmen Yönetim Sistemi</title>

  <!-- FontAwesome (ikonlar için) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

  <style>
    /* ---------------------------- */
    /* Reset & Temel Ayarlar        */
    /* ---------------------------- */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background: linear-gradient(135deg, #1a2a6c, #2c3e50, #3498db);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      color: #333;
    }
    a {
      text-decoration: none;
      color: inherit;
    }
    input, button {
      font-family: inherit;
    }

    /* ---------------------------- */
    /* Üst Bar (Header)             */
    /* ---------------------------- */
    .topbar {
      width: 100%;
      height: 60px;
      background: #1f3c88;
      display: flex;
      align-items: center;
      padding: 0 30px;
      color: #fff;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }
    .topbar .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 1.2rem;
      font-weight: 600;
    }
    .topbar .logo .fa-graduation-cap {
      font-size: 1.4rem;
    }
    .topbar .logo span {
      white-space: nowrap;
    }

    /* ---------------------------- */
    /* Giriş Kartı (Container)       */
    /* ---------------------------- */
    .login-container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }
    .login-card {
      width: 400px;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
      transition: transform 0.3s;
    }
    .login-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    /* ---------------------------- */
    /* Kart Üst Bölümü              */
    /* ---------------------------- */
    .login-header {
      background: #1f3c88;
      color: #fff;
      text-align: center;
      padding: 30px 20px;
      position: relative;
    }
    .login-header .logo-icon {
      font-size: 2.5rem;
      margin-bottom: 10px;
      animation: pulse-blue 2s infinite;
    }
    .login-header h2 {
      font-size: 1.8rem;
      font-weight: 600;
      margin-bottom: 5px;
    }
    .login-header p {
      font-size: 1rem;
      font-weight: 300;
      opacity: 0.9;
    }
    @keyframes pulse-blue {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }

    /* ---------------------------- */
    /* Kart İçerik Bölümü            */
    /* ---------------------------- */
    .login-body {
      padding: 30px 25px;
    }
    .form-group {
      margin-bottom: 20px;
      position: relative;
    }
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #2c3e50;
    }
    .input-with-icon {
      position: relative;
    }
    .input-with-icon i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #7f8c8d;
      font-size: 1rem;
    }
    .form-control {
      width: 100%;
      padding: 14px 20px 14px 45px;
      border: 2px solid #e0e7ff;
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    .form-control:focus {
      outline: none;
      border-color: #3498db;
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    }
    .password-toggle {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #7f8c8d;
      transition: color 0.3s;
    }
    .password-toggle:hover {
      color: #1f3c88;
    }

    .btn-login {
      width: 100%;
      background: #3498db;
      color: #fff;
      border: none;
      padding: 14px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
    }
    .btn-login:hover {
      background: #2980b9;
      transform: translateY(-2px);
      box-shadow: 0 7px 15px rgba(0, 0, 0, 0.2);
    }

    .admin-link {
      text-align: center;
      margin-top: 20px;
      color: #7f8c8d;
      font-size: 0.95rem;
    }
    .admin-link a {
      color: #3498db;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
    }
    .admin-link a:hover {
      color: #1a2a6c;
      text-decoration: underline;
    }

    /* ---------------------------- */
    /* Hata Mesajı                  */
    /* ---------------------------- */
    .error-message {
      background-color: #ffebee;
      color: #c62828;
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      text-align: center;
      font-weight: 500;
      border-left: 4px solid #c62828;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .error-message i {
      margin-right: 10px;
    }

    /* ---------------------------- */
    /* Responsive Düzeltmeler       */
    /* ---------------------------- */
    @media (max-width: 480px) {
      .login-card {
        width: 100%;
        border-radius: 0;
      }
      .login-header {
        padding: 25px 15px;
      }
      .login-header h2 {
        font-size: 1.5rem;
      }
      .form-control {
        padding: 12px 15px 12px 40px;
      }
      .btn-login {
        padding: 12px;
      }
    }
  </style>
</head>
<body>

  <!-- Üst Bar -->
  <div class="topbar">
    <div class="logo">
      <i class="fa fa-graduation-cap"></i>
      <span>Öğrenci Öğretmen Yönetim Sistemi</span>
    </div>
  </div>

  <!-- Giriş Kartı -->
  <div class="login-container">
    <div class="login-card">
      <!-- Kart Üst Bölüm -->
      <div class="login-header">
        <div class="logo-icon">
          <i class="fa fa-user-lock"></i>
        </div>
        <h2>Hesabınıza Giriş Yapın</h2>
        <p>Öğrenci veya öğretmen bilgilerinizle giriş yapın</p>
      </div>

      <!-- Kart İçerik Bölümü -->
      <div class="login-body">
        <?php if (!empty($error)): ?>
          <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form method="post">
          <div class="form-group">
            <label for="email">E-posta Adresi</label>
            <div class="input-with-icon">
              <i class="fas fa-envelope"></i>
              <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                placeholder="email@example.com"
                required
                autocomplete="username"
                value="<?= htmlspecialchars($email) ?>"
              >
            </div>
          </div>

          <div class="form-group">
            <label for="password">Şifre</label>
            <div class="input-with-icon">
              <i class="fas fa-lock"></i>
              <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Şifreniz"
                required
                autocomplete="current-password"
              >
              <span class="password-toggle" onclick="togglePassword()">
                <i class="fas fa-eye"></i>
              </span>
            </div>
          </div>

          <button type="submit" class="btn-login">Giriş Yap</button>
        </form>

        <div class="admin-link">
          <p>Yönetici misiniz? <a href="Yonetici_Giris.php">Yönetici Girişi</a></p>
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.querySelector('.password-toggle i');

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
      }
    }

    // Sayfa yüklendiğinde email alanına odaklan
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('email').focus();
    });
  </script>
</body>
</html>
