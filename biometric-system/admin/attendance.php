<?php
require_once __DIR__ . '/../auth.php';
Auth::requireLogin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AttendanceLog.php';
require_once __DIR__ . '/../models/Machine.php';
require_once __DIR__ . '/../models/User.php';

$attendanceModel = new AttendanceLog();
$machineModel = new Machine();
$userModel = new User();

// Handle AJAX requests for manual attendance
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Log all POST data for debugging
    error_log("POST request received: " . print_r($_POST, true));
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'manual_attendance') {
        try {
            // Validate required fields
            if (empty($_POST['user_id']) || empty($_POST['machine_id']) || empty($_POST['log_type'])) {
                $missing = [];
                if (empty($_POST['user_id'])) $missing[] = 'user_id';
                if (empty($_POST['machine_id'])) $missing[] = 'machine_id';
                if (empty($_POST['log_type'])) $missing[] = 'log_type';
                
                error_log("Missing required fields: " . implode(', ', $missing));
                echo json_encode(['success' => false, 'message' => 'جميع الحقول مطلوبة: ' . implode(', ', $missing)]);
                exit;
            }
            
            // Check if user exists
            $user = $userModel->getByUserId($_POST['user_id']);
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
                exit;
            }
            
            // Check if machine exists
            $machine = $machineModel->getById($_POST['machine_id']);
            if (!$machine) {
                echo json_encode(['success' => false, 'message' => 'الجهاز غير موجود']);
                exit;
            }
            
            // Create attendance log
            $data = [
                'machine_id' => $_POST['machine_id'],
                'user_id' => $_POST['user_id'],
                'log_time' => $_POST['log_time'] ?: date('Y-m-d H:i:s'),
                'log_type' => $_POST['log_type'],
                'raw_data' => json_encode([
                    'manual_entry' => true,
                    'admin_user' => 'admin', // يمكن تحسينها لاحقاً لتسجيل المدير الفعلي
                    'notes' => $_POST['notes'] ?? ''
                ])
            ];
            
            $result = $attendanceModel->create($data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم تسجيل الحضور بنجاح']);
            } else {
                // Get detailed error information
                $errorInfo = $attendanceModel->getLastError();
                error_log("Attendance creation failed: " . print_r($errorInfo, true));
                echo json_encode(['success' => false, 'message' => 'فشل في تسجيل الحضور: ' . $errorInfo]);
            }
        } catch (Exception $e) {
            error_log("Manual attendance error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'حدث خطأ في تسجيل الحضور: ' . $e->getMessage()]);
        }
        exit;
    } else {
        error_log("Unknown action: " . $action);
        echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
        exit;
    }
}

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
        
        <div style="display: flex; align-items: center; gap: 1rem;">
            <span style="color: rgba(255,255,255,0.8); font-size: 0.9rem;">
                <i class="fas fa-user-shield"></i>
                مرحباً <?= Auth::getAdminName() ?>
            </span>
            <a href="logout.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;" 
               onclick="return confirm('هل تريد تسجيل الخروج؟')">
                <i class="fas fa-sign-out-alt"></i>
                خروج
            </a>
        </div>
        
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
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-primary" onclick="showManualAttendanceModal()">
                        <i class="fas fa-user-plus"></i>
                        تسجيل حضور يدوي
                    </button>
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
                                <th>طريقة التحقق</th>
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
                                    <?php
                                    $log_types = [
                                        'check_in' => ['text' => '🟢 حضور', 'class' => 'online'],
                                        'check_out' => ['text' => '🔴 انصراف', 'class' => 'offline'],
                                        'break_out' => ['text' => '🟡 استراحة', 'class' => 'warning'],
                                        'break_in' => ['text' => '🟢 عودة', 'class' => 'online']
                                    ];
                                    $type_info = $log_types[$log['log_type']] ?? ['text' => $log['log_type'], 'class' => 'warning'];
                                    ?>
                                    <span class="status <?= $type_info['class'] ?>">
                                        <?= $type_info['text'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $verify_icons = [
                                        'fingerprint' => ['icon' => 'fas fa-fingerprint', 'text' => 'بصمة الإصبع', 'color' => '#3498db'],
                                        'face' => ['icon' => 'fas fa-user-circle', 'text' => 'الوجه', 'color' => '#e74c3c'],
                                        'password' => ['icon' => 'fas fa-key', 'text' => 'يدوي', 'color' => '#9b59b6'],
                                        'fingerprint_face' => ['icon' => 'fas fa-fingerprint', 'text' => 'بصمة + وجه', 'color' => '#1abc9c']
                                    ];
                                    $verify_info = $verify_icons[$log['verify_type']] ?? ['icon' => 'fas fa-question', 'text' => $log['verify_type'], 'color' => '#95a5a6'];
                                    ?>
                                    <span style="color: <?= $verify_info['color'] ?>; display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="<?= $verify_info['icon'] ?>"></i>
                                        <?= $verify_info['text'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Manual Attendance Modal -->
    <div class="modal" id="manualAttendanceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">تسجيل حضور يدوي</h3>
                <button class="close-btn" onclick="closeManualAttendanceModal()">&times;</button>
            </div>
            <form id="manualAttendanceForm">
                <div class="form-group">
                    <label class="form-label" for="userSelect">المستخدم <span style="color: var(--danger)">*</span></label>
                    <select class="form-control" id="userSelect" name="user_id" required>
                        <option value="">اختر المستخدم</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= htmlspecialchars($user['user_id']) ?>">
                                <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['user_id']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="machineSelect">الجهاز <span style="color: var(--danger)">*</span></label>
                    <select class="form-control" id="machineSelect" name="machine_id" required>
                        <option value="">اختر الجهاز</option>
                        <?php foreach ($machines as $machine): ?>
                            <option value="<?= $machine['id'] ?>">
                                <?= htmlspecialchars($machine['name']) ?> - <?= htmlspecialchars($machine['location']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="logType">نوع التسجيل <span style="color: var(--danger)">*</span></label>
                    <select class="form-control" id="logType" name="log_type" required>
                        <option value="check_in">🟢 حضور</option>
                        <option value="check_out">🔴 انصراف</option>
                        <option value="break_out">🟡 خروج لاستراحة</option>
                        <option value="break_in">🟢 عودة من استراحة</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="logTime">وقت التسجيل</label>
                    <input type="datetime-local" class="form-control" id="logTime" name="log_time" 
                           value="<?= date('Y-m-d\TH:i') ?>">
                    <small style="color: rgba(255,255,255,0.7);">اتركه فارغاً لاستخدام الوقت الحالي</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="notes">ملاحظات</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                              placeholder="ملاحظات إضافية (اختياري)"></textarea>
                </div>
                
                <div class="d-flex gap-2 justify-content-between">
                    <button type="button" class="btn btn-outline" onclick="closeManualAttendanceModal()">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        تسجيل الحضور
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showManualAttendanceModal() {
            document.getElementById('manualAttendanceModal').classList.add('show');
        }

        function closeManualAttendanceModal() {
            document.getElementById('manualAttendanceModal').classList.remove('show');
            document.getElementById('manualAttendanceForm').reset();
            // Reset datetime to current time
            document.getElementById('logTime').value = new Date().toISOString().slice(0, 16);
        }

        // Handle manual attendance form submission
        document.getElementById('manualAttendanceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'manual_attendance');

            // Debug: Log form data
            console.log('Form data being sent:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }

            fetch('attendance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.text(); // Change to text first to see raw response
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    alert(data.message);
                    if (data.success) {
                        closeManualAttendanceModal();
                        location.reload();
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    alert('خطأ في معالجة الاستجابة من الخادم');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('حدث خطأ في تسجيل الحضور: ' + error.message);
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('show');
            }
        });

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
