<?php
require_once 'db/db_connect.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name']);
    $surname      = trim($_POST['surname']);
    $email        = trim($_POST['email']);
    $phone        = trim($_POST['phone']);
    $degree       = trim($_POST['degree']);
    $university   = trim($_POST['university']);
    $faculty      = trim($_POST['faculty']);
    $graduation   = trim($_POST['graduation_year']);
    $letter       = trim($_POST['letter']);
    $password     = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // CV dosyası yükleme
    $cv_path = '';
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            $cv_name = uniqid('cv_') . '.pdf';
            $cv_dir = __DIR__ . '/uploads/cvs/';
            if (!is_dir($cv_dir)) mkdir($cv_dir, 0777, true);
            $cv_path = 'uploads/cvs/' . $cv_name;
            move_uploaded_file($_FILES['cv']['tmp_name'], $cv_dir . $cv_name);
        } else {
            $error = "CV sadece PDF formatında olmalıdır.";
        }
    } else {
        $error = "CV yüklenmeli ve PDF olmalıdır.";
    }

    if (!$error) {
        $sql = "INSERT INTO teacher_applications 
        (name, surname, email, phone, degree, university, faculty, graduation_year, letter, cv_path, password) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssssss', $name, $surname, $email, $phone, $degree, $university, $faculty, $graduation, $letter, $cv_path, $password);
        if ($stmt->execute()) {
            $success = "Başvurunuz alınmıştır, yönetici onayından sonra bilgilendirileceksiniz.";
        } else {
            $error = "Hata: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Öğretmen Başvuru Formu</title>
</head>
<body>
<h2>Öğretmen Başvuru Formu</h2>
<?php if ($success): ?>
    <div style="color:green;"><?= $success ?></div>
<?php elseif ($error): ?>
    <div style="color:red;"><?= $error ?></div>
<?php endif; ?>
<form method="post" enctype="multipart/form-data">
    <b>Kişisel Bilgiler</b><br>
    Ad: <input type="text" name="name" required> <br>
    Soyad: <input type="text" name="surname" required> <br>
    E-posta: <input type="email" name="email" required> <br>
    Telefon: <input type="text" name="phone" required> <br>
    Şifre: <input type="password" name="password" required> <br>
    <b>Akademik Bilgi</b><br>
    Mezuniyet Derecesi: 
    <select name="degree" required>
        <option value="Lisans">Lisans</option>
        <option value="Yüksek Lisans">Yüksek Lisans</option>
        <option value="Doktora">Doktora</option>
    </select><br>
    Üniversite: <input type="text" name="university" required> <br>
    Fakülte: <input type="text" name="faculty" required> <br>
    Mezuniyet Yılı: <input type="number" name="graduation_year" min="1950" max="2100" required> <br>
    <b>CV (PDF) Yükleyin:</b> <input type="file" name="cv" accept="application/pdf" required><br>
    <b>Niyet Mektubu / Açıklama</b><br>
    <textarea name="letter" rows="5" cols="60" placeholder="Neden başvurmak istiyorsunuz?" required></textarea><br>
    <input type="checkbox" name="accept" required> <label>Bilgilerimin doğruluğunu onaylıyorum ve gizlilik politikasını okudum.</label><br>
    <button type="submit">Başvur</button>
</form>
</body>
</html>