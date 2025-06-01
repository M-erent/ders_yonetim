<?php
// student_schedule.php
require_once 'includes/auth_check.php';
require_once 'db/db_connect.php';

if ($_SESSION['role'] !== 'Öğrenci') {
    header('Location: Gosterge_Paneli.php');
    exit();
}

// 1. Veritabanından, öğrencinin seçtiği dersleri (gün ve saat bilgisiyle) çekiyoruz
$sql = "
  SELECT 
    c.course_name, 
    COALESCE(u.name, '-') AS teacher_name,
    c.day_of_week,
    c.start_time
  FROM student_courses sc
  JOIN courses c ON sc.course_id = c.id
  LEFT JOIN users u ON c.teacher_id = u.id
  WHERE sc.student_id = ?
  ORDER BY 
    FIELD(c.day_of_week, 'Pazartesi','Salı','Çarşamba','Perşembe','Cuma'),
    c.start_time
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();

// 2. Haftanın günleri
$daysOfWeek = ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma'];

// 3. Saat dilimleri (09:00'dan başlayıp 50'er dakikalık dersler; son 14:00-14:50 dilimi)
$timeSlots = [
    '09:00', '09:50',
    '10:40', '11:30',
    '12:20', '13:10',
    '14:00'
];

// 4. Seçilen dersleri, günlerine göre gruplandırıyoruz
$weeklySchedule = [];
foreach ($daysOfWeek as $d) {
    // Her güne, zaman slotlarına bakmak için boş bir alt dizi
    $weeklySchedule[$d] = [];
}
while ($row = $res->fetch_assoc()) {
    $day = $row['day_of_week'];
    if (in_array($day, $daysOfWeek)) {
        $weeklySchedule[$day][] = [
            'course_name'  => $row['course_name'],
            'teacher_name' => $row['teacher_name'],
            'start_time'   => substr($row['start_time'], 0, 5) // “HH:MM” olarak alıyoruz
        ];
    }
}

// 5. Haftanın herhangi bir gününde ders var mı kontrolü
$hasAnyCourse = false;
foreach ($weeklySchedule as $day => $courses) {
    if (!empty($courses)) {
        $hasAnyCourse = true;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Ders Programım</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- FontAwesome (ikonlar için) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    /* ---------------------------- */
    /* Reset & Temel Ayarlar        */
    /* ---------------------------- */
    * {
      margin: 0; padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background: #f4f7fa;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      color: #333;
    }
    a { text-decoration: none; color: inherit; }

    /* ---------------------------- */
    /* Üst Bar (Header)             */
    /* ---------------------------- */
    .topbar {
      background-color: #1f3c88;
      color: #fff;
      padding: 12px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .topbar .title {
      font-size: 1.4rem;
      font-weight: bold;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .topbar .title .fa-calendar-alt {
      font-size: 1.5rem;
      color: #e74c3c;
    }
    .topbar .back-btn {
      background: #e67e22;
      color: #fff;
      padding: 8px 16px;
      border-radius: 6px;
      font-weight: 500;
      transition: background 0.3s;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .topbar .back-btn:hover {
      background: #d35400;
    }

    /* ---------------------------- */
    /* Ana İçerik (Container)       */
    /* ---------------------------- */
    .container {
      flex: 1;
      max-width: 900px;
      width: 100%;
      margin: 24px auto;
      background: #fff;
      padding: 24px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
    h2 {
      color: #1f3c88;
      text-align: center;
      margin-bottom: 20px;
      font-size: 1.6rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    h2 .fa-table {
      color: #e74c3c;
      font-size: 1.4rem;
    }

    /* ---------------------------- */
    /* Saat Slotları Tablosu        */
    /* ---------------------------- */
    .schedule-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 16px;
    }
    .schedule-table th,
    .schedule-table td {
      border: 1px solid #e0e0e0;
      padding: 12px 10px;
      text-align: left;
      vertical-align: top;
      font-size: 0.95rem;
    }
    .schedule-table th {
      background: #f2f5fd;
      color: #1f3c88;
      font-weight: 600;
      text-align: center;
    }
    .schedule-table tr:hover td {
      background: #f9faff;
    }
    .time-slot {
      background: #eef4ff;
      font-weight: 500;
      text-align: center;
      color: #1f3c88;
    }
    .course-item {
      margin-bottom: 8px;
    }
    .course-item b {
      color: #1f3c88;
      font-size: 1rem;
    }
    .course-item small {
      display: block;
      color: #555;
      font-size: 0.9rem;
      margin-top: 2px;
    }
    .no-courses {
      text-align: center;
      color: #666;
      margin-top: 40px;
      font-size: 1rem;
    }

    /* ---------------------------- */
    /* Responsive Ayarlar            */
    /* ---------------------------- */
    @media (max-width: 768px) {
      .schedule-table th,
      .schedule-table td {
        font-size: 0.85rem;
        padding: 8px 6px;
      }
      h2 {
        font-size: 1.4rem;
      }
      .topbar .title {
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>

  <!-- Üst Bar -->
  <div class="topbar">
    <div class="title">
      <i class="fas fa-calendar-alt"></i> Ders Programım
    </div>
    <a href="Gosterge_Paneli.php" class="back-btn">
      <i class="fas fa-arrow-left"></i> Geri
    </a>
  </div>

  <!-- Ana İçerik -->
  <div class="container">
    <h2><i class="fas fa-table"></i> Haftalık Ders Programı</h2>

    <?php if (!$hasAnyCourse): ?>
      <p class="no-courses">Henüz hiçbir derse kayıtlı değilsiniz.</p>
    <?php else: ?>
      <table class="schedule-table">
        <thead>
          <tr>
            <th>Saat</th>
            <?php foreach ($daysOfWeek as $day): ?>
              <th><?= htmlspecialchars($day) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($timeSlots as $slot): ?>
            <tr>
              <!-- Saat sütunu -->
              <td class="time-slot"><?= htmlspecialchars($slot) ?></td>

              <!-- Her güne ait ilgili slotta dersi varsa yazdır -->
              <?php foreach ($daysOfWeek as $day): ?>
                <td>
                  <?php
                  $found = false;
                  foreach ($weeklySchedule[$day] as $course) {
                      if ($course['start_time'] === $slot) {
                          $found = true;
                          echo '<div class="course-item">';
                          echo '<b>' . htmlspecialchars($course['course_name']) . '</b>';
                          echo '<small>Öğr.: ' . htmlspecialchars($course['teacher_name']) . '</small>';
                          echo '</div>';
                          break;
                      }
                  }
                  if (!$found) {
                      // Boş hücre ise hiçbir şey yazmıyoruz
                  }
                  ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</body>
</html>
