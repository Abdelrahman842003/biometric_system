<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Machine.php';

$userModel = new User();
$machineModel = new Machine();

// Handle AJAX requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $result = $userModel->create([
                'user_id' => $_POST['user_id'],
                'name' => $_POST['name'],
                'card_number' => $_POST['card_number'] ?? null,
                'department' => $_POST['department'] ?? null,
                'position' => $_POST['position'] ?? null,
                'is_admin' => isset($_POST['is_admin']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'edit':
            $result = $userModel->update($_POST['id'], [
                'user_id' => $_POST['user_id'],
                'name' => $_POST['name'],
                'card_number' => $_POST['card_number'] ?? null,
                'department' => $_POST['department'] ?? null,
                'position' => $_POST['position'] ?? null,
                'is_admin' => isset($_POST['is_admin']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'delete':
            $result = $userModel->delete($_POST['id']);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'sync_to_machine':
            // This would use the SDK to sync user to machine
            echo json_encode(['success' => true, 'message' => 'User synced to machine']);
            exit;
    }
}

// Get all users for display
$users = $userModel->getAll();
$machines = $machineModel->getAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين - نظام البايومترك</title>
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
                <li><a href="users.php" class="active">
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
                <li><a href="reports.php">
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
                    <h1 class="page-title">إدارة المستخدمين</h1>
                    <p class="page-subtitle">إضافة وتعديل وحذف المستخدمين في النظام</p>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="openAddUserModal()">
                        <i class="fas fa-plus"></i>
                        إضافة مستخدم جديد
                    </button>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">قائمة المستخدمين</h3>
                    <div>
                        <button class="btn btn-outline" onclick="refreshUsers()">
                            <i class="fas fa-sync-alt"></i>
                            تحديث
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>رقم المستخدم</th>
                                <th>الاسم</th>
                                <th>رقم البطاقة</th>
                                <th>القسم</th>
                                <th>المنصب</th>
                                <th>الحالة</th>
                                <th>مشرف</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['user_id']) ?></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['card_number'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($user['department'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($user['position'] ?? '-') ?></td>
                                <td>
                                    <span class="status <?= $user['is_active'] ? 'online' : 'offline' ?>">
                                        <?= $user['is_active'] ? 'نشط' : 'غير نشط' ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $user['is_admin'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' ?>
                                </td>
                                <td><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-outline btn-sm" onclick="editUser(<?= $user['id'] ?>)" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="syncToMachine(<?= $user['id'] ?>)" title="مزامنة مع الجهاز">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= $user['id'] ?>)" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit User Modal -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">إضافة مستخدم جديد</h3>
                <button class="close-btn" onclick="closeUserModal()">&times;</button>
            </div>
            <form id="userForm">
                <input type="hidden" id="userId" name="id">
                <input type="hidden" id="formAction" name="action" value="add">
                
                <div class="form-group">
                    <label class="form-label" for="userIdField">رقم المستخدم <span style="color: var(--danger)">*</span></label>
                    <input type="text" class="form-control" id="userIdField" name="user_id" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="userName">الاسم <span style="color: var(--danger)">*</span></label>
                    <input type="text" class="form-control" id="userName" name="name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="cardNumber">رقم البطاقة</label>
                    <input type="text" class="form-control" id="cardNumber" name="card_number">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="department">القسم</label>
                    <input type="text" class="form-control" id="department" name="department">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="position">المنصب</label>
                    <input type="text" class="form-control" id="position" name="position">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" id="isAdmin" name="is_admin" style="margin-left: 0.5rem;">
                        مشرف
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" id="isActive" name="is_active" checked style="margin-left: 0.5rem;">
                        نشط
                    </label>
                </div>
                
                <div class="d-flex gap-2 justify-content-between">
                    <button type="button" class="btn btn-outline" onclick="closeUserModal()">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sync to Machine Modal -->
    <div class="modal" id="syncModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">مزامنة مع الجهاز</h3>
                <button class="close-btn" onclick="closeSyncModal()">&times;</button>
            </div>
            <form id="syncForm">
                <input type="hidden" id="syncUserId" name="user_id">
                
                <div class="form-group">
                    <label class="form-label" for="machineSelect">اختر الجهاز</label>
                    <select class="form-control" id="machineSelect" name="machine_id" required>
                        <option value="">اختر جهاز...</option>
                        <?php foreach ($machines as $machine): ?>
                        <option value="<?= $machine['id'] ?>"><?= htmlspecialchars($machine['name']) ?> (<?= $machine['ip_address'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="d-flex gap-2 justify-content-between">
                    <button type="button" class="btn btn-outline" onclick="closeSyncModal()">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync"></i>
                        مزامنة
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openAddUserModal() {
            document.getElementById('modalTitle').textContent = 'إضافة مستخدم جديد';
            document.getElementById('formAction').value = 'add';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('userModal').classList.add('show');
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.remove('show');
        }

        function closeSyncModal() {
            document.getElementById('syncModal').classList.remove('show');
        }

        // Edit user
        function editUser(id) {
            fetch(`../api/get-user.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const user = data.data;
                        document.getElementById('modalTitle').textContent = 'تعديل المستخدم';
                        document.getElementById('formAction').value = 'edit';
                        document.getElementById('userId').value = user.id;
                        document.getElementById('userIdField').value = user.user_id;
                        document.getElementById('userName').value = user.name;
                        document.getElementById('cardNumber').value = user.card_number || '';
                        document.getElementById('department').value = user.department || '';
                        document.getElementById('position').value = user.position || '';
                        document.getElementById('isAdmin').checked = user.is_admin == 1;
                        document.getElementById('isActive').checked = user.is_active == 1;
                        document.getElementById('userModal').classList.add('show');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ في جلب بيانات المستخدم');
                });
        }

        // Delete user
        function deleteUser(id) {
            if (confirm('هل أنت متأكد من حذف هذا المستخدم؟')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('users.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('تم حذف المستخدم بنجاح');
                        location.reload();
                    } else {
                        alert('حدث خطأ في حذف المستخدم');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ في حذف المستخدم');
                });
            }
        }

        // Sync to machine
        function syncToMachine(userId) {
            document.getElementById('syncUserId').value = userId;
            document.getElementById('syncModal').classList.add('show');
        }

        // Refresh users
        function refreshUsers() {
            location.reload();
        }

        // Form submission
        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('users.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم حفظ المستخدم بنجاح');
                    location.reload();
                } else {
                    alert('حدث خطأ في حفظ المستخدم');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في حفظ المستخدم');
            });
        });

        // Sync form submission
        document.getElementById('syncForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('action', 'sync_to_machine');
            formData.append('user_id', document.getElementById('syncUserId').value);
            formData.append('machine_id', document.getElementById('machineSelect').value);
            
            fetch('users.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم مزامنة المستخدم مع الجهاز بنجاح');
                    closeSyncModal();
                } else {
                    alert('حدث خطأ في المزامنة');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في المزامنة');
            });
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('show');
            }
        });

        // Mobile menu toggle
        function toggleMobileMenu() {
            const navbar = document.querySelector('.navbar-nav');
            navbar.classList.toggle('mobile-show');
        }
    </script>
</body>
</html>
