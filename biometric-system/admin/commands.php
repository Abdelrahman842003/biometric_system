<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Command.php';
require_once __DIR__ . '/../models/Machine.php';

$commandModel = new Command();
$machineModel = new Machine();

// Handle AJAX requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_command':
            $result = $commandModel->create([
                'machine_id' => $_POST['machine_id'],
                'command_type' => $_POST['command_type'],
                'parameters' => $_POST['parameters'] ?? '',
                'priority' => $_POST['priority'] ?? 'normal'
            ]);
            
            echo json_encode([
                'status' => $result ? 'success' : 'error',
                'message' => $result ? 'تم إنشاء الأمر بنجاح' : 'فشل في إنشاء الأمر'
            ]);
            exit;
            
        case 'delete_command':
            $result = $commandModel->delete($_POST['command_id']);
            echo json_encode([
                'status' => $result ? 'success' : 'error',
                'message' => $result ? 'تم حذف الأمر بنجاح' : 'فشل في حذف الأمر'
            ]);
            exit;
    }
}

// Get commands with filters
$filters = [
    'machine_id' => $_GET['machine_id'] ?? '',
    'status' => $_GET['status'] ?? '',
    'limit' => 50
];

$commands = $commandModel->getAll($filters);
$machines = $machineModel->getAll();
$stats = $commandModel->getStats();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الأوامر - نظام البايومترك</title>
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
            <h2 class="menu-title">إدارة الأوامر</h2>
            <p class="menu-subtitle">تنفيذ ومراقبة أوامر الأجهزة</p>
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
                <a href="attendance.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span class="nav-text">سجل الحضور</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="commands.php" class="nav-link active">
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
                    <h1 class="page-title">إدارة الأوامر</h1>
                    <p class="page-subtitle">إدارة أوامر الأجهزة والمهام المرسلة</p>
                </div>
                <button class="btn btn-primary" onclick="showCreateCommandModal()">
                    <i class="fas fa-plus"></i>
                    إنشاء أمر جديد
                </button>
            </div>

            <!-- Stats Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock text-warning"></i>
                            في الانتظار
                        </h3>
                    </div>
                    <div style="font-size: 2rem; font-weight: bold; color: var(--warning);">
                        <?= $stats['pending'] ?? 0 ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-check-circle text-success"></i>
                            مكتملة
                        </h3>
                    </div>
                    <div style="font-size: 2rem; font-weight: bold; color: var(--success);">
                        <?= $stats['completed'] ?? 0 ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-times-circle text-danger"></i>
                            فاشلة
                        </h3>
                    </div>
                    <div style="font-size: 2rem; font-weight: bold; color: var(--danger);">
                        <?= $stats['failed'] ?? 0 ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            المجموع
                        </h3>
                    </div>
                    <div style="font-size: 2rem; font-weight: bold; color: white;">
                        <?= $stats['total'] ?? 0 ?>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تصفية الأوامر</h3>
                </div>
                <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
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
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-control">
                            <option value="">جميع الحالات</option>
                            <option value="pending" <?= ($_GET['status'] ?? '') == 'pending' ? 'selected' : '' ?>>في الانتظار</option>
                            <option value="processing" <?= ($_GET['status'] ?? '') == 'processing' ? 'selected' : '' ?>>قيد التنفيذ</option>
                            <option value="completed" <?= ($_GET['status'] ?? '') == 'completed' ? 'selected' : '' ?>>مكتملة</option>
                            <option value="failed" <?= ($_GET['status'] ?? '') == 'failed' ? 'selected' : '' ?>>فاشلة</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            تصفية
                        </button>
                    </div>
                </form>
            </div>

            <!-- Commands Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">قائمة الأوامر</h3>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>الجهاز</th>
                                <th>نوع الأمر</th>
                                <th>المعاملات</th>
                                <th>الأولوية</th>
                                <th>الحالة</th>
                                <th>تاريخ الإنشاء</th>
                                <th>آخر تحديث</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commands as $command): ?>
                                <tr>
                                    <td><?= $command['id'] ?></td>
                                    <td><?= htmlspecialchars($command['machine_name'] ?? 'غير محدد') ?></td>
                                    <td>
                                        <span class="status">
                                            <?php
                                            $types = [
                                                'restart' => 'إعادة تشغيل',
                                                'sync_time' => 'مزامنة الوقت',
                                                'get_logs' => 'جلب السجلات',
                                                'upload_user' => 'رفع مستخدم',
                                                'delete_user' => 'حذف مستخدم',
                                                'clear_logs' => 'مسح السجلات'
                                            ];
                                            echo $types[$command['command_type']] ?? $command['command_type'];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($command['parameters'] ?: '-') ?></td>
                                    <td>
                                        <span class="status <?= $command['priority'] == 'high' ? 'offline' : ($command['priority'] == 'low' ? 'pending' : 'online') ?>">
                                            <?php
                                            $priorities = ['high' => 'عالية', 'normal' => 'عادية', 'low' => 'منخفضة'];
                                            echo $priorities[$command['priority']] ?? $command['priority'];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status <?= $command['status'] == 'completed' ? 'online' : ($command['status'] == 'failed' ? 'offline' : 'pending') ?>">
                                            <?php
                                            $statuses = [
                                                'pending' => 'في الانتظار',
                                                'processing' => 'قيد التنفيذ',
                                                'completed' => 'مكتملة',
                                                'failed' => 'فاشلة'
                                            ];
                                            echo $statuses[$command['status']] ?? $command['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?= date('Y-m-d H:i', strtotime($command['created_at'])) ?></td>
                                    <td><?= $command['updated_at'] ? date('Y-m-d H:i', strtotime($command['updated_at'])) : '-' ?></td>
                                    <td>
                                        <?php if ($command['status'] == 'pending'): ?>
                                            <button class="btn btn-danger btn-sm" onclick="deleteCommand(<?= $command['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($command['response']): ?>
                                            <button class="btn btn-outline btn-sm" onclick="showCommandResponse(<?= $command['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                                عرض الرد
                                            </button>
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

    <!-- Create Command Modal -->
    <div id="createCommandModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">إنشاء أمر جديد</h3>
                <button class="close-btn" onclick="hideCreateCommandModal()">&times;</button>
            </div>
            <form id="createCommandForm">
                <div class="form-group">
                    <label class="form-label">الجهاز</label>
                    <select name="machine_id" class="form-control" required>
                        <option value="">اختر الجهاز</option>
                        <?php foreach ($machines as $machine): ?>
                            <option value="<?= $machine['id'] ?>"><?= htmlspecialchars($machine['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">نوع الأمر</label>
                    <select name="command_type" class="form-control" required>
                        <option value="restart">إعادة تشغيل الجهاز</option>
                        <option value="sync_time">مزامنة الوقت</option>
                        <option value="get_logs">جلب السجلات</option>
                        <option value="clear_logs">مسح السجلات</option>
                        <option value="get_users">جلب قائمة المستخدمين</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">المعاملات (اختياري)</label>
                    <textarea name="parameters" class="form-control" rows="3" placeholder="معاملات إضافية بصيغة JSON"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">الأولوية</label>
                    <select name="priority" class="form-control">
                        <option value="normal">عادية</option>
                        <option value="high">عالية</option>
                        <option value="low">منخفضة</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="hideCreateCommandModal()">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إنشاء الأمر</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showCreateCommandModal() {
            document.getElementById('createCommandModal').classList.add('show');
        }

        function hideCreateCommandModal() {
            document.getElementById('createCommandModal').classList.remove('show');
            document.getElementById('createCommandForm').reset();
        }

        function deleteCommand(commandId) {
            if (confirm('هل أنت متأكد من حذف هذا الأمر؟')) {
                const formData = new FormData();
                formData.append('action', 'delete_command');
                formData.append('command_id', commandId);

                fetch('commands.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === 'success') {
                        location.reload();
                    }
                });
            }
        }

        function showCommandResponse(commandId) {
            // Implementation for showing command response
            alert('سيتم تطوير هذه الميزة قريباً');
        }

        // Handle create command form
        document.getElementById('createCommandForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create_command');

            fetch('commands.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    hideCreateCommandModal();
                    location.reload();
                }
            });
        });

        // Auto refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>

    <!-- Advanced Navbar Script -->
    <script src="../assets/js/navbar.js"></script>
</body>
</html>
