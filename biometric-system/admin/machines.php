<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Machine.php';
require_once __DIR__ . '/../models/Command.php';

$machineModel = new Machine();
$commandModel = new Command();

// Handle AJAX requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_machine':
            $data = [
                'name' => $_POST['name'],
                'location' => $_POST['location'],
                'ip_address' => $_POST['ip_address'],
                'serial_number' => $_POST['serial_number'],
                'port' => $_POST['port'] ?: 4370,
                'adms_enabled' => isset($_POST['adms_enabled']) ? 1 : 0,
                'adms_key' => $_POST['adms_key'],
                'status' => $_POST['status']
            ];
            
            if ($machineModel->create($data)) {
                echo json_encode(['success' => true, 'message' => 'تم إضافة الجهاز بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'فشل في إضافة الجهاز']);
            }
            exit;
            
        case 'edit_machine':
            $id = $_POST['id'];
            $data = [
                'name' => $_POST['name'],
                'location' => $_POST['location'],
                'ip_address' => $_POST['ip_address'],
                'serial_number' => $_POST['serial_number'],
                'port' => $_POST['port'],
                'adms_enabled' => isset($_POST['adms_enabled']) ? 1 : 0,
                'adms_key' => $_POST['adms_key'],
                'status' => $_POST['status']
            ];
            
            if ($machineModel->update($id, $data)) {
                echo json_encode(['success' => true, 'message' => 'تم تحديث الجهاز بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'فشل في تحديث الجهاز']);
            }
            exit;
            
        case 'delete_machine':
            $id = $_POST['id'];
            if ($machineModel->delete($id)) {
                echo json_encode(['success' => true, 'message' => 'تم حذف الجهاز بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'فشل في حذف الجهاز']);
            }
            exit;
            
        case 'test_connection':
            $id = $_POST['id'];
            $machine = $machineModel->getById($id);
            if ($machine) {
                $result = $machineModel->testConnection($machine['ip_address'], $machine['port']);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'الجهاز غير موجود']);
            }
            exit;
            
        case 'reboot_machine':
            $id = $_POST['id'];
            if ($commandModel->createRebootCommand($id)) {
                echo json_encode(['success' => true, 'message' => 'تم إرسال أمر إعادة التشغيل']);
            } else {
                echo json_encode(['success' => false, 'message' => 'فشل في إرسال الأمر']);
            }
            exit;
            
        case 'sync_time':
            $id = $_POST['id'];
            if ($commandModel->createSyncTimeCommand($id)) {
                echo json_encode(['success' => true, 'message' => 'تم إرسال أمر مزامنة الوقت']);
            } else {
                echo json_encode(['success' => false, 'message' => 'فشل في إرسال الأمر']);
            }
            exit;
    }
}

// Get machines list
$machines = $machineModel->getAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الأجهزة | نظام البايومترك</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Additional styles for machines page */
    </style>
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
                <li><a href="machines.php" class="active">
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
            <div class="page-header">
                <div>
                    <h1 class="page-title">إدارة الأجهزة</h1>
                    <div class="breadcrumb">الرئيسية / إدارة الأجهزة</div>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="showAddMachineModal()">
                        <i class="fas fa-plus"></i>
                        إضافة جهاز جديد
                    </button>
                </div>
            </div>

            <!-- Machines Table -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">قائمة الأجهزة</h2>
                    <div>
                        <button class="btn btn-outline" onclick="refreshMachines()">
                            <i class="fas fa-sync-alt"></i>
                            تحديث
                        </button>
                    </div>
                </div>
                <div class="section-content">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>اسم الجهاز</th>
                                    <th>الموقع</th>
                                    <th>عنوان IP</th>
                                    <th>المنفذ</th>
                                    <th>الحالة</th>
                                    <th>آخر تزامن</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($machines as $machine): ?>
                                <tr>
                                    <td><?= $machine['id'] ?></td>
                                    <td><?= htmlspecialchars($machine['name']) ?></td>
                                    <td><?= htmlspecialchars($machine['location']) ?></td>
                                    <td><?= htmlspecialchars($machine['ip_address']) ?></td>
                                    <td><?= $machine['port'] ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $machine['status'] ?>">
                                            <?= $machine['status'] === 'active' ? 'نشط' : ($machine['status'] === 'inactive' ? 'غير نشط' : 'صيانة') ?>
                                        </span>
                                    </td>
                                    <td><?= $machine['last_sync'] ? date('Y-m-d H:i', strtotime($machine['last_sync'])) : 'لم يتم التزامن' ?></td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                                                    onclick="testConnection(<?= $machine['id'] ?>)">
                                                <i class="fas fa-network-wired"></i>
                                            </button>
                                            <button class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                                                    onclick="rebootMachine(<?= $machine['id'] ?>)">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                            <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                                                    onclick="syncTime(<?= $machine['id'] ?>)">
                                                <i class="fas fa-clock"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                                                    onclick="editMachine(<?= $machine['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                                                    onclick="deleteMachine(<?= $machine['id'] ?>)">
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
            </div>
        </main>
    </div>

    <!-- Add/Edit Machine Modal -->
    <div id="machineModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">إضافة جهاز جديد</h3>
                <button class="modal-close" onclick="closeMachineModal()">&times;</button>
            </div>
            <form id="machineForm">
                <input type="hidden" id="machineId" name="id">
                <div class="form-group">
                    <label for="machineName">اسم الجهاز *</label>
                    <input type="text" id="machineName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="machineLocation">الموقع</label>
                    <input type="text" id="machineLocation" name="location">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="machineIp">عنوان IP *</label>
                        <input type="text" id="machineIp" name="ip_address" required>
                    </div>
                    <div class="form-group">
                        <label for="machinePort">المنفذ</label>
                        <input type="number" id="machinePort" name="port" value="4370">
                    </div>
                </div>
                <div class="form-group">
                    <label for="machineSerial">الرقم التسلسلي</label>
                    <input type="text" id="machineSerial" name="serial_number">
                </div>
                <div class="form-group">
                    <label for="machineAdmsKey">مفتاح ADMS</label>
                    <input type="text" id="machineAdmsKey" name="adms_key">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="machineStatus">الحالة</label>
                        <select id="machineStatus" name="status">
                            <option value="active">نشط</option>
                            <option value="inactive">غير نشط</option>
                            <option value="maintenance">صيانة</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="machineAdmsEnabled" name="adms_enabled" checked>
                            تفعيل ADMS
                        </label>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="closeMachineModal()">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .modal-content {
            background: var(--dark);
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            color: white;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: white;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-actions {
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        #machineForm {
            padding: 1.5rem;
        }
    </style>

    <script>
        function showAddMachineModal() {
            document.getElementById('modalTitle').textContent = 'إضافة جهاز جديد';
            document.getElementById('machineForm').reset();
            document.getElementById('machineId').value = '';
            document.getElementById('machineModal').style.display = 'flex';
        }

        function closeMachineModal() {
            document.getElementById('machineModal').style.display = 'none';
        }

        function editMachine(id) {
            // Fetch machine data and populate form
            fetch('../api/get-machine.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const machine = data.machine;
                        document.getElementById('modalTitle').textContent = 'تعديل الجهاز';
                        document.getElementById('machineId').value = machine.id;
                        document.getElementById('machineName').value = machine.name;
                        document.getElementById('machineLocation').value = machine.location;
                        document.getElementById('machineIp').value = machine.ip_address;
                        document.getElementById('machinePort').value = machine.port;
                        document.getElementById('machineSerial').value = machine.serial_number;
                        document.getElementById('machineAdmsKey').value = machine.adms_key;
                        document.getElementById('machineStatus').value = machine.status;
                        document.getElementById('machineAdmsEnabled').checked = machine.adms_enabled == 1;
                        document.getElementById('machineModal').style.display = 'flex';
                    }
                });
        }

        function deleteMachine(id) {
            if (confirm('هل أنت متأكد من حذف هذا الجهاز؟')) {
                const formData = new FormData();
                formData.append('action', 'delete_machine');
                formData.append('id', id);

                fetch('machines.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }

        function testConnection(id) {
            const formData = new FormData();
            formData.append('action', 'test_connection');
            formData.append('id', id);

            fetch('machines.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            });
        }

        function rebootMachine(id) {
            if (confirm('هل تريد إعادة تشغيل هذا الجهاز؟')) {
                const formData = new FormData();
                formData.append('action', 'reboot_machine');
                formData.append('id', id);

                fetch('machines.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                });
            }
        }

        function syncTime(id) {
            const formData = new FormData();
            formData.append('action', 'sync_time');
            formData.append('id', id);

            fetch('machines.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            });
        }

        function refreshMachines() {
            location.reload();
        }

        // Handle form submission
        document.getElementById('machineForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const isEdit = document.getElementById('machineId').value !== '';
            formData.append('action', isEdit ? 'edit_machine' : 'add_machine');

            fetch('machines.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    closeMachineModal();
                    location.reload();
                }
            });
        });

        // Mobile menu toggle
        function toggleMobileMenu() {
            const navbar = document.querySelector('.navbar-nav');
            navbar.classList.toggle('mobile-show');
        }
    </script>
</body>
</html>
