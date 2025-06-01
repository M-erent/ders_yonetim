<?php
session_start();
require_once 'db/db_connect.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin_users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($admin = $res->fetch_assoc()) {
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            header('Location: Yonetici_Panel.php');
            exit();
        } else {
            $error = 'Şifre yanlış!';
        }
    } else {
        $error = 'Kullanıcı bulunamadı!';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Yönetici Girişi – Öğrenci Öğretmen Yönetim Sistemi</title>

  <!-- FontAwesome (ikonlar için) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>

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
      background: linear-gradient(135deg, #8a1919, #b21f1f, #e74c3c);
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
      background: #b21f1f;
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
    .topbar .logo .fa-user-shield {
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
      background: #b21f1f;
      color: #fff;
      text-align: center;
      padding: 30px 20px;
      position: relative;
    }
    .login-header i {
      font-size: 2.5rem;
      margin-bottom: 10px;
      animation: pulse-red 2s infinite;
    }
    @keyframes pulse-red {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    .login-header h2 {
      font-size: 1.6rem;
      margin-bottom: 5px;
      font-weight: 600;
    }
    .login-header p {
      font-size: 0.9rem;
      opacity: 0.8;
      font-weight: 300;
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
      margin-bottom: 6px;
      font-weight: 500;
      color: #333;
    }
    .input-icon {
      position: relative;
    }
    .input-icon i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #aaa;
      font-size: 1rem;
    }
    .form-control {
      width: 100%;
      padding: 12px 14px 12px 40px;
      border: 2px solid #f0e5e5;
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    .form-control:focus {
      outline: none;
      border-color: #e74c3c;
      box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2);
    }

    .btn-submit {
      width: 100%;
      padding: 12px;
      background: #e74c3c;
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s ease;
      margin-top: 10px;
    }
    .btn-submit:hover {
      background: #c0392b;
    }

    .error-message {
      background: #ffe5e5;
      border-left: 4px solid #c62828;
      color: #c62828;
      padding: 10px 12px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-weight: 500;
      display: flex;
      align-items: center;
    }
    .error-message i {
      margin-right: 8px;
    }

    .back-button {
      display: block;
      margin: 20px auto 10px auto;
      text-align: center;
    }
    .back-button a {
      text-decoration: none;
    }
    .back-button button {
      padding: 10px 18px;
      background: #f0f0f0;
      border: 2px solid #ccc;
      border-radius: 8px;
      font-size: 0.95rem;
      cursor: pointer;
      transition: background 0.3s ease, border-color 0.3s ease;
    }
    .back-button button:hover {
      background: #e0e0e0;
      border-color: #999;
    }

    .footer-note {
      text-align: center;
      margin-top: 15px;
      font-size: 0.85rem;
      color: #666;
    }

    /* ---------------------------- */
    /* Responsive Düzenlemeler       */
    /* ---------------------------- */
    @media (max-width: 480px) {
      .login-card {
        width: 100%;
        border-radius: 0;
      }
      .login-header h2 {
        font-size: 1.4rem;
      }
      .form-control {
        padding: 10px 12px 10px 36px;
      }
      .btn-submit {
        padding: 10px;
      }
    }
  </style>
</head>
<body>

  <!-- Üst Bar -->
  <div class="topbar">
    <div class="logo">
      <i class="fas fa-user-shield"></i>
      <span>Yönetici Girişi</span>
    </div>
  </div>

  <!-- Giriş Kartı -->
  <div class="login-container">
    <div class="login-card">
      <!-- Kart Üst Bölüm -->
      <div class="login-header">
        <i class="fas fa-unlock-alt"></i>
        <h2>Yönetici Paneli</h2>
        <p>Lütfen kullanıcı adı ve şifrenizle giriş yapın</p>
      </div>

      <!-- Kart İçerik Bölümü -->
      <div class="login-body">
        <?php if (!empty($error)): ?>
          <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="form-group">
            <label for="username">Kullanıcı Adı</label>
            <div class="input-icon">
              <i class="fas fa-user"></i>
              <input
                type="text"
                id="username"
                name="username"
                class="form-control"
                placeholder="Kullanıcı adınızı girin"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <label for="password">Şifre</label>
            <div class="input-icon">
              <i class="fas fa-lock"></i>
              <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Şifrenizi girin"
                required
              />
            </div>
          </div>

          <button type="submit" class="btn-submit">Giriş Yap</button>
        </form>

        <div class="back-button">
          <a href="Giris.php">
            <button type="button"><i class="fas fa-arrow-left"></i> Geri Dön</button>
          </a>
        </div>

        <div class="footer-note">
          &copy; <?= date('Y') ?> Öğrenci Öğretmen Yönetim Sistemi
        </div>
      </div>
    </div>
  </div>
</body>
</html>
