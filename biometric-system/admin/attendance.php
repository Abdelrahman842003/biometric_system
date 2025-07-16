<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AttendanceLog.php';
require_once __DIR__ . '/../models/Machine.php';
require_once __DIR__ . '/../models/User.php';

$attendanceModel = new AttendanceLog();
$machineModel = new Machine();
$userModel = new User();

// Get filter parameters
$filters = [
    'machine_id' => $_GET['machine_id'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'log_type' => $_GET['log_type'] ?? '',
    'limit' => $_GET['limit'] ?? 50
];

// Get attendance logs
$attendanceLogs = $attendanceModel->getAll($filters);
$machines = $machineModel->getAll();
$users = $userModel->getAll();
$stats = $attendanceModel->getStats($filters);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل الحضور - نظام البايومترك</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navbar Overlay -->
    <div class="navbar-overlay" id="navbarOverlay"></div>
    
    <!-- Advanced Navbar with Modern Toggle -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">
            <i class="fas fa-fingerprint"></i>
            نظام البايومترك
        </a>
        
        <button class="navbar-toggle" id="menuToggle" onclick="toggleNavbar()">
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>
    </nav>
    
    <!-- Floating Central Menu -->
    <div class="navbar-menu" id="navbarMenu">
        <div class="menu-header">
            <h2 class="menu-title">سجل الحضور</h2>
            <p class="menu-subtitle">مراقبة وتتبع حضور الموظفين</p>
        </div>
        
        <ul class="nav-items">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <span class="nav-text">لوحة التحكم</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="machines.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <span class="nav-text">إدارة الأجهزة</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="nav-text">إدارة المستخدمين</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="attendance.php" class="nav-link active">
                    <div class="nav-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span class="nav-text">سجل الحضور</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="commands.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-terminal"></i>
                    </div>
                    <span class="nav-text">الأوامر</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="reports.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <span class="nav-text">التقارير</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="settings.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span class="nav-text">الإعدادات</span>
                </a>
            </li>
        </ul>
        
        <div class="user-section">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="user-details">
                    <h4>المدير العام</h4>
                    <p>مدير النظام</p>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                تسجيل الخروج
            </a>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Main Content -->
        <main class="main-content">
            <div class="main-header">
                <div>
                    <h1 class="page-title">سجل الحضور</h1>
                    <p class="page-subtitle">عرض وتصفية سجلات الحضور والانصراف</p>
                </div>
                <div>
                    <button class="btn btn-success" onclick="exportToCSV()">
                        <i class="fas fa-download"></i>
                        تصدير CSV
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div class="card" style="text-align: center; padding: 1.5rem;">
                    <div style="color: var(--primary); font-size: 2rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-list"></i>
                    </div>
                    <h3 style="color: white; margin-bottom: 0.5rem;"><?= number_format($stats['total_logs']) ?></h3>
                    <p style="color: rgba(255,255,255,0.7);">إجمالي السجلات</p>
                </div>
                
                <div class="card" style="text-align: center; padding: 1.5rem;">
                    <div style="color: var(--success); font-size: 2rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <h3 style="color: white; margin-bottom: 0.5rem;"><?= number_format($stats['check_ins']) ?></h3>
                    <p style="color: rgba(255,255,255,0.7);">الحضور</p>
                </div>
                
                <div class="card" style="text-align: center; padding: 1.5rem;">
                    <div style="color: var(--warning); font-size: 2rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <h3 style="color: white; margin-bottom: 0.5rem;"><?= number_format($stats['check_outs']) ?></h3>
                    <p style="color: rgba(255,255,255,0.7);">الانصراف</p>
                </div>
                
                <div class="card" style="text-align: center; padding: 1.5rem;">
                    <div style="color: var(--secondary); font-size: 2rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 style="color: white; margin-bottom: 0.5rem;"><?= number_format($stats['unique_users']) ?></h3>
                    <p style="color: rgba(255,255,255,0.7);">مستخدمين فريدين</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">البحث والتصفية</h3>
                </div>
                <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">الجهاز</label>
                        <select name="machine_id" class="form-control">
                            <option value="">جميع الأجهزة</option>
                            <?php foreach ($machines as $machine): ?>
                            <option value="<?= $machine['id'] ?>" <?= $filters['machine_id'] == $machine['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($machine['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">نوع السجل</label>
                        <select name="log_type" class="form-control">
                            <option value="">جميع الأنواع</option>
                            <option value="check_in" <?= $filters['log_type'] == 'check_in' ? 'selected' : '' ?>>حضور</option>
                            <option value="check_out" <?= $filters['log_type'] == 'check_out' ? 'selected' : '' ?>>انصراف</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?>">
                    </div>
                    
                    <div class="form-group" style="display: flex; align-items: end; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            بحث
                        </button>
                        <a href="attendance.php" class="btn btn-outline">
                            <i class="fas fa-undo"></i>
                            إعادة تعيين
                        </a>
                    </div>
                </form>
            </div>

            <!-- Attendance Logs Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">سجلات الحضور</h3>
                    <div>
                        <span style="color: rgba(255,255,255,0.7);">
                            عرض <?= count($attendanceLogs) ?> سجل
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>رقم المستخدم</th>
                                <th>اسم المستخدم</th>
                                <th>الجهاز</th>
                                <th>وقت السجل</th>
                                <th>نوع السجل</th>
                                <th>نوع التحقق</th>
                                <th>درجة الحرارة</th>
                                <th>حالة الكمامة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceLogs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['user_id']) ?></td>
                                <td><?= htmlspecialchars($log['user_name'] ?? 'غير معروف') ?></td>
                                <td><?= htmlspecialchars($log['machine_name'] ?? 'غير معروف') ?></td>
                                <td><?= date('Y-m-d H:i:s', strtotime($log['log_time'])) ?></td>
                                <td>
                                    <span class="status <?= $log['log_type'] == 'check_in' ? 'online' : 'warning' ?>">
                                        <?= $log['log_type'] == 'check_in' ? 'حضور' : 'انصراف' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $verify_icons = [
                                        'fingerprint' => 'fas fa-fingerprint',
                                        'face' => 'fas fa-user-circle',
                                        'card' => 'fas fa-credit-card',
                                        'password' => 'fas fa-key'
                                    ];
                                    $icon = $verify_icons[$log['verify_type']] ?? 'fas fa-question';
                                    ?>
                                    <i class="<?= $icon ?>"></i>
                                    <?= ucfirst($log['verify_type']) ?>
                                </td>
                                <td>
                                    <?= $log['temperature'] ? $log['temperature'] . '°C' : '-' ?>
                                </td>
                                <td>
                                    <?php if ($log['mask_status'] !== null): ?>
                                        <span class="status <?= $log['mask_status'] ? 'online' : 'offline' ?>">
                                            <?= $log['mask_status'] ? 'نعم' : 'لا' ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function exportToCSV() {
            const params = new URLSearchParams(window.location.search);
            params.append('export', 'csv');
            window.location.href = '../api/export-attendance.php?' + params.toString();
        }

        // Auto-refresh every 30 seconds
        setInterval(function() {
            // Only refresh if no filters are applied
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.toString() === '') {
                location.reload();
            }
        }, 30000);
    </script>

    <!-- Advanced Navbar Script -->
    <script src="../assets/js/navbar.js"></script>
</body>
</html>
