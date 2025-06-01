<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Yoklamaları Gör</title>
  <link rel="stylesheet" href="assets/css/style.css?v=2">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f7fa;
      margin: 0;
    }
    .topbar {
      background-color: #1f3c88;
      color: #fff;
      padding: 12px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .topbar .title {
      font-size: 24px;
      font-weight: bold;
    }
    .topbar .back-btn {
      background-color: #e67e22;
      color: #fff;
      padding: 8px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }
    .topbar .back-btn:hover {
      background-color: #d35400;
    }
    .container {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      padding: 24px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    h2 {
      color: #1f3c88;
      text-align: center;
      margin-bottom: 20px;
    }
    form {
      display: flex;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
    }
    .form-group {
      flex: 1;
      min-width: 200px;
    }
    .form-group label {
      font-weight: bold;
      color: #333;
      display: block;
      margin-bottom: 8px;
    }
    .form-group select {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
      background: #fafafa;
      transition: border-color 0.3s ease;
    }
    .form-group select:focus {
      border-color: #1f3c88;
      outline: none;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
    }
    table, th, td {
      border: 1px solid #ddd;
      text-align: center;
    }
    th {
      background-color: #f4f7fa;
      color: #1f3c88;
      padding: 12px;
    }
    td {
      padding: 12px;
      font-size: 16px;
    }
    .status-present {
      color: green;
      font-weight: bold;
    }
    .status-absent {
      color: red;
      font-weight: bold;
    }
    .msg, .error {
      text-align: center;
      font-weight: bold;
      margin: 20px 0;
      font-size: 18px;
    }
    .msg { color: green; }
    .error { color: red; }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      text-decoration: none;
      color: #1f3c88;
      font-weight: bold;
    }
    .back-link:hover {
      color: #e67e22;
    }
  </style>
</head>
<body>

<!-- Üst Bar -->
<div class="topbar">
  <div class="title">Yoklamaları Gör</div>
  <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
</div>

<div class="container">
  <h2>Yoklama Kayıtları</h2>

  <!-- Ders ve Tarih Seçimi -->
  <form method="get">
    <div class="form-group">
      <label for="course_id">Ders:</label>
      <select name="course_id" id="course_id" onchange="this.form.submit()">
        <?php foreach ($courses as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $c['id'] == $course_id ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['course_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="date">Tarih:</label>
      <select name="date" id="date" onchange="this.form.submit()">
        <?php foreach ($dates as $d): ?>
          <option value="<?= $d ?>" <?= $d == $date ? 'selected' : '' ?>>
            <?= $d ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>

  <!-- Yoklama Kayıtları -->
 <?php if (!empty($records) && $records->num_rows > 0): ?>

    <table>
      <tr><th>Öğrenci</th><th>Durum</th></tr>
      <?php while ($r = $records->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td class="<?= $r['status'] == 'present' ? 'status-present' : 'status-absent' ?>">
          <?= $r['status'] == 'present' ? '✅ Gelmiş' : '❌ Gelmemiş' ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p class="error">Bu tarih için yoklama alınmamış.</p>
  <?php endif; ?>
</div>

</body>
</html>
