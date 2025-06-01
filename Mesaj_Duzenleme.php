<?php
require_once 'config.php';
session_start();

// Yetki kontrolü
if (!isset($_SESSION['admin_id'])) {
    header('Location: Yonetici_Giris.php');
    exit();
}

// ID kontrolü
if (!isset($_GET['id'])) {
    die("ID yok!");
}
$id = (int)$_GET['id'];

// POST ile güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = (int)$_POST['sender_id'];
    $title     = trim($_POST['title']);
    $content   = trim($_POST['content']);

    $stmt = $db->prepare("UPDATE messages SET sender_id = ?, title = ?, content = ? WHERE id = ?");
    $stmt->execute([$sender_id, $title, $content, $id]);

    header("Location: Yonetici_Panel.php?tab=messages&msg=Mesaj güncellendi");
    exit();
}

// Mevcut mesaj verisini çek
$stmt = $db->prepare("SELECT * FROM messages WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    die("Mesaj bulunamadı!");
}

// Kullanıcı listesini çek
$users = $db->query("SELECT id, name FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mesaj Düzenle - Yönetici Paneli</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" />
  <style>
    * {
      margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif;
    }
    body {
      background: #f8f8f8; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px;
    }
    .edit-container {
      width: 100%; max-width: 500px; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden;
    }
    .edit-header {
      background: linear-gradient(135deg, #b21f1f, #8a1919); color: #fff; text-align: center; padding: 25px 20px; position: relative;
    }
    .edit-header i {
      font-size: 2.5rem; margin-bottom: 10px; animation: pulse-red 2s infinite;
    }
    @keyframes pulse-red {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    .edit-header h1 {
      font-size: 1.7rem; margin-bottom: 5px;
    }
    .edit-header p {
      font-size: 0.9rem; opacity: 0.8;
    }
    .edit-form {
      padding: 30px 20px;
    }
    .form-group {
      margin-bottom: 20px; position: relative;
    }
    .form-group label {
      display: block; margin-bottom: 6px; font-weight: 500; color: #333;
    }
    .input-icon {
      position: relative;
    }
    .input-icon i {
      position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999;
    }
    .form-control {
      width: 100%; padding: 12px 14px 12px 40px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease; background-color: #fff;
    }
    .form-control:focus {
      outline: none; border-color: #b21f1f; box-shadow: 0 0 0 3px rgba(178, 31, 31, 0.1);
    }
    select.form-control {
      background: url('data:image/svg+xml;charset=UTF-8,<svg fill="%23999" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat calc(100% - 12px) center; background-size: 16px;
    }
    textarea.form-control {
      resize: vertical;
    }
    .btn-save {
      width: 100%; padding: 12px; background: linear-gradient(to right, #b21f1f, #8a1919); border: none; border-radius: 8px; color: #fff; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.3s ease; margin-top: 10px;
    }
    .btn-save:hover {
      background: linear-gradient(to right, #8a1919, #6e1313);
    }
    .back-button {
      display: block; margin: 20px auto 0 auto; text-align: center;
    }
    .back-button a {
      text-decoration: none;
    }
    .back-button button {
      padding: 10px 18px; background: #fff; border: 2px solid #b21f1f; border-radius: 8px; color: #b21f1f; font-size: 0.95rem; cursor: pointer; transition: background 0.3s ease, color 0.3s ease;
    }
    .back-button button:hover {
      background: #b21f1f; color: #fff;
    }
    .footer-note {
      text-align: center; margin-top: 15px; font-size: 0.85rem; color: #666;
    }
  </style>
</head>
<body>
  <div class="edit-container">
    <div class="edit-header">
      <i class="fas fa-envelope-open-text"></i>
      <h1>Mesaj Düzenle</h1>
      <p>Yönetici Mesaj Yönetimi</p>
    </div>
    <div class="edit-form">
      <form method="POST" action="">
        <div class="form-group">
          <label for="sender_id">Gönderen</label>
          <div class="input-icon">
            <i class="fas fa-user"></i>
            <select id="sender_id" name="sender_id" class="form-control" required>
              <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>" <?= $row['sender_id'] == $u['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($u['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="title">Başlık</label>
          <div class="input-icon">
            <i class="fas fa-heading"></i>
            <input type="text" id="title" name="title" class="form-control" placeholder="Başlık girin" value="<?= htmlspecialchars($row['title']) ?>" required />
          </div>
        </div>

        <div class="form-group">
          <label for="content">Mesaj</label>
          <div class="input-icon">
            <i class="fas fa-comment-dots"></i>
            <textarea id="content" name="content" class="form-control" rows="5" placeholder="Mesaj içeriği girin" required><?= htmlspecialchars($row['content']) ?></textarea>
          </div>
        </div>

        <button type="submit" class="btn-save">Kaydet</button>
      </form>

      <div class="back-button">
        <a href="Yonetici_Panel.php?tab=messages">
          <button type="button"><i class="fas fa-arrow-left"></i> Geri Dön</button>
        </a>
      </div>

      <div class="footer-note">
        &copy; <?= date('Y') ?> Yönetici Mesaj Yönetimi
      </div>
    </div>
  </div>
</body>
</html>
