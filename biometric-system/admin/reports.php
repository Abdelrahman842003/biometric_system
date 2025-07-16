<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AttendanceLog.php';
require_once __DIR__ . '/../models/Machine.php';
require_once __DIR__ . '/../models/User.php';

$attendanceModel = new AttendanceLog();
$machineModel = new Machine();
$userModel = new User();

// Handle export requests
if (isset($_POST['action']) && $_POST['action'] === 'export') {
    $filters = [
        'machine_id' => $_POST['machine_id'] ?? '',
        'user_id' => $_POST['user_id'] ?? '',
        'date_from' => $_POST['date_from'] ?? '',
        'date_to' => $_POST['date_to'] ?? '',
        'log_type' => $_POST['log_type'] ?? ''
    ];
    
    $filename = $attendanceModel->exportToCsv($filters);
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize(__DIR__ . '/../exports/' . $filename));
    readfile(__DIR__ . '/../exports/' . $filename);
    exit;
}

// Get data for filters
$machines = $machineModel->getAll();
$users = $userModel->getAll();

// Get report data
$dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

$filters = [
    'machine_id' => $_GET['machine_id'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
    'log_type' => $_GET['log_type'] ?? ''
];

$stats = $attendanceModel->getStats($filters);
$dailyStats = $attendanceModel->getDailyStats($dateTo);
$recentLogs = $attendanceModel->getAll(array_merge($filters, ['limit' => 100]));
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقارير - نظام البايومترك</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">
            <i class="fas fa-fingerprint"></i>
            نظام البايومترك
        </a>
        
        <div class="navbar-collapse">
            <ul class="navbar-nav">
                <li><a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    لوحة التحكم
                </a></li>
                <li><a href="machines.php">
                    <i class="fas fa-desktop"></i>
                    إدارة الأجهزة
                </a></li>
                <li><a href="users.php">
                    <i class="fas fa-users"></i>
                    إدارة المستخدمين
                </a></li>
                <li><a href="attendance.php">
                    <i class="fas fa-clock"></i>
                    سجل الحضور
                </a></li>
                <li><a href="commands.php">
                    <i class="fas fa-terminal"></i>
                    الأوامر
                </a></li>
                <li><a href="reports.php" class="active">
                    <i class="fas fa-chart-bar"></i>
                    التقارير
                </a></li>
                <li><a href="settings.php">
                    <i class="fas fa-cog"></i>
                    الإعدادات
                </a></li>
            </ul>
            
            <div class="navbar-user">
                <i class="fas fa-user-circle"></i>
                <span>المدير</span>
                <a href="logout.php" style="color: rgba(255, 255, 255, 0.8); margin-right: 1rem;">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
            
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Main Content -->
        <main class="main-content">
            <div class="main-header">
                <div>
                    <h1 class="page-title">التقارير والإحصائيات</h1>
                    <p class="page-subtitle">تقارير شاملة لحضور الموظفين والأجهزة</p>
                </div>
            </div>

            <!-- Report Filters -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">فلترة التقارير</h3>
                </div>
                <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                    <div class="form-group">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control" value="<?= $dateFrom ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control" value="<?= $dateTo ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">الجهاز</label>
                        <select name="machine_id" class="form-control">
                            <option value="">جميع الأجهزة</option>
                            <?php foreach ($machines as $machine): ?>
                                <option value="<?= $machine['id'] ?>" <?= ($_GET['machine_id'] ?? '') == $machine['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($machine['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">المستخدم</label>
                        <select name="user_id" class="form-control">
                            <option value="">جميع المستخدمين</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= ($_GET['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">نوع السجل</label>
                        <select name="log_type" class="form-control">
                            <option value="">جميع الأنواع</option>
                            <option value="check_in" <?= ($_GET['log_type'] ?? '') == 'check_in' ? 'selected' : '' ?>>دخول</option>
                            <option value="check_out" <?= ($_GET['log_type'] ?? '') == 'check_out' ? 'selected' : '' ?>>خروج</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            تطبيق الفلترة
                        </button>
                    </div>
                </form>
            </div>

            <!-- Summary Statistics -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            إجمالي السجلات
                        </h3>
                    </div>
                    <div style="font-size: 2rem; font-weight: bold; color: white;">
                        <?= number_format($stats['total_logs'] ?? 0) ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users"></i>
                            المستخدمين النشطين
                        </h3>
                    </div>
                    <div style="font-size: 2rem; font-weight: bold; color: var(--success);">
                        <?= number_format($stats['unique_users'] ?? 0) ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-sign-in-alt"></i>
                            عمليات الدخول
                        </h3>
                    </div>
                    <div style="font-size: 2rem; font-weight: bold; color: var(--primary);">
                        <?= number_format($stats['check_ins'] ?? 0) ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-sign-out-alt"></i>
                            عمليات الخروج
                        </h3>
                    </div>
                    <div style="font-size: 2rem; font-weight: bold; color: var(--warning);">
                        <?= number_format($stats['check_outs'] ?? 0) ?>
                    </div>
                </div>
            </div>

            <!-- Daily Statistics -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">إحصائيات اليوم (<?= $dateTo ?>)</h3>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>الجهاز</th>
                                <th>إجمالي السجلات</th>
                                <th>المستخدمين</th>
                                <th>عمليات الدخول</th>
                                <th>عمليات الخروج</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dailyStats as $stat): ?>
                                <tr>
                                    <td><?= htmlspecialchars($stat['machine_name']) ?></td>
                                    <td><?= number_format($stat['total_logs']) ?></td>
                                    <td><?= number_format($stat['unique_users']) ?></td>
                                    <td><?= number_format($stat['check_ins']) ?></td>
                                    <td><?= number_format($stat['check_outs']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Export Section -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تصدير التقارير</h3>
                </div>
                <form method="POST" style="display: flex; gap: 1rem; align-items: end;">
                    <input type="hidden" name="action" value="export">
                    <input type="hidden" name="machine_id" value="<?= $_GET['machine_id'] ?? '' ?>">
                    <input type="hidden" name="user_id" value="<?= $_GET['user_id'] ?? '' ?>">
                    <input type="hidden" name="date_from" value="<?= $dateFrom ?>">
                    <input type="hidden" name="date_to" value="<?= $dateTo ?>">
                    <input type="hidden" name="log_type" value="<?= $_GET['log_type'] ?? '' ?>">
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-download"></i>
                        تصدير إلى CSV
                    </button>
                    
                    <button type="button" class="btn btn-outline" onclick="printReport()">
                        <i class="fas fa-print"></i>
                        طباعة التقرير
                    </button>
                </form>
            </div>

            <!-- Recent Logs Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">السجلات الحديثة (آخر 100 سجل)</h3>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>الوقت</th>
                                <th>المستخدم</th>
                                <th>الجهاز</th>
                                <th>النوع</th>
                                <th>طريقة التحقق</th>
                                <th>درجة الحرارة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLogs as $log): ?>
                                <tr>
                                    <td><?= date('Y-m-d H:i:s', strtotime($log['log_time'])) ?></td>
                                    <td><?= htmlspecialchars($log['user_name'] ?? $log['user_id']) ?></td>
                                    <td><?= htmlspecialchars($log['machine_name'] ?? 'غير محدد') ?></td>
                                    <td>
                                        <span class="status <?= $log['log_type'] == 'check_in' ? 'online' : 'pending' ?>">
                                            <?= $log['log_type'] == 'check_in' ? 'دخول' : 'خروج' ?>
                                        </span>
                                    </td>
                                    <td><?= $log['verify_type'] == 'fingerprint' ? 'بصمة' : ($log['verify_type'] == 'face' ? 'وجه' : $log['verify_type']) ?></td>
                                    <td><?= $log['temperature'] ? $log['temperature'] . '°C' : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function printReport() {
            window.print();
        }

        // Auto-refresh every 2 minutes
        setInterval(() => {
            location.reload();
        }, 120000);

        // Mobile menu toggle
        function toggleMobileMenu() {
            const navbar = document.querySelector('.navbar-nav');
            navbar.classList.toggle('mobile-show');
        }
    </script>
</body>
</html>
