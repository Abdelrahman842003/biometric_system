<?php
require_once __DIR__ . '/../auth.php';
Auth::requireLogin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Machine.php';

$machineModel = new Machine();

// Handle AJAX requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_machine':
            try {
                $connectionType = trim($_POST['connection_type'] ?: 'adms');
                
                // Validate required fields based on connection type
                if (empty($_POST['name'])) {
                    echo json_encode(['success' => false, 'message' => 'اسم الجهاز مطلوب']);
                    exit;
                }
                
                if ($connectionType === 'public_ip' && empty($_POST['ip_address'])) {
                    echo json_encode(['success' => false, 'message' => 'عنوان IP مطلوب للاتصال المباشر']);
                    exit;
                }
                
                if ($connectionType === 'adms' && empty($_POST['adms_key'])) {
                    echo json_encode(['success' => false, 'message' => 'مفتاح ADMS مطلوب لاتصال ADMS']);
                    exit;
                }
                
                $data = [
                    'name' => trim($_POST['name']),
                    'location' => trim($_POST['location'] ?: ''),
                    'ip_address' => $connectionType === 'adms' ? '0.0.0.0' : trim($_POST['ip_address']),
                    'serial_number' => trim($_POST['serial_number'] ?: ''),
                    'port' => $connectionType === 'adms' ? 80 : (int)($_POST['port'] ?: 4370),
                    'connection_type' => $connectionType,
                    'adms_enabled' => $connectionType === 'adms' ? 1 : 0,
                    'adms_key' => trim($_POST['adms_key'] ?: ''),
                    'status' => $_POST['status'] ?: 'active'
                ];
                
                if ($machineModel->create($data)) {
                    echo json_encode(['success' => true, 'message' => 'تم إضافة الجهاز بنجاح']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'فشل في إضافة الجهاز']);
                }
            } catch (Exception $e) {
                error_log("Add machine error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'حدث خطأ في إضافة الجهاز']);
            }
            exit;
            
        case 'edit_machine':
            try {
                $id = $_POST['id'];
                $connectionType = trim($_POST['connection_type'] ?: 'adms');
                
                // Validate required fields
                if (empty($id)) {
                    echo json_encode(['success' => false, 'message' => 'معرف الجهاز مطلوب']);
                    exit;
                }
                
                if (empty($_POST['name'])) {
                    echo json_encode(['success' => false, 'message' => 'اسم الجهاز مطلوب']);
                    exit;
                }
                
                if ($connectionType === 'public_ip' && empty($_POST['ip_address'])) {
                    echo json_encode(['success' => false, 'message' => 'عنوان IP مطلوب للاتصال المباشر']);
                    exit;
                }
                
                if ($connectionType === 'adms' && empty($_POST['adms_key'])) {
                    echo json_encode(['success' => false, 'message' => 'مفتاح ADMS مطلوب لاتصال ADMS']);
                    exit;
                }
                
                $data = [
                    'name' => trim($_POST['name']),
                    'location' => trim($_POST['location'] ?: ''),
                    'ip_address' => $connectionType === 'adms' ? '0.0.0.0' : trim($_POST['ip_address']),
                    'serial_number' => trim($_POST['serial_number'] ?: ''),
                    'port' => $connectionType === 'adms' ? 80 : (int)($_POST['port'] ?: 4370),
                    'connection_type' => $connectionType,
                    'adms_enabled' => $connectionType === 'adms' ? 1 : 0,
                    'adms_key' => trim($_POST['adms_key'] ?: ''),
                    'status' => $_POST['status'] ?: 'active'
                ];
                
                if ($machineModel->update($id, $data)) {
                    echo json_encode(['success' => true, 'message' => 'تم تحديث الجهاز بنجاح']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'فشل في تحديث الجهاز']);
                }
            } catch (Exception $e) {
                error_log("Edit machine error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'حدث خطأ في تحديث الجهاز']);
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
    <link rel="stylesheet" href="../assets/css/select-fix.css">
    <style>
        /* Additional styles for machines page */
    </style>
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
            <h2 class="menu-title">إدارة الأجهزة</h2>
            <p class="menu-subtitle">إدارة وتحكم في أجهزة البايومترك</p>
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
                <a href="machines.php" class="nav-link active">
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
                    <h1 class="page-title">إدارة الأجهزة</h1>
                    <p class="page-subtitle">إدارة وتحكم في أجهزة البايومترك</p>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="showAddMachineModal()">
                        <i class="fas fa-plus"></i>
                        إضافة جهاز جديد
                    </button>
                </div>
            </div>

            <!-- Machines Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">قائمة الأجهزة</h3>
                    <div>
                        <button class="btn btn-outline" onclick="refreshMachines()">
                            <i class="fas fa-sync-alt"></i>
                            تحديث
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>اسم الجهاز</th>
                                <th>الموقع</th>
                                <th>عنوان IP</th>
                                <th>المنفذ</th>
                                <th>نوع الاتصال</th>
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
                                <td>
                                    <?php if ($machine['connection_type'] === 'adms'): ?>
                                        <span style="color: #666; font-style: italic;">غير مطلوب</span>
                                    <?php else: ?>
                                        <?= htmlspecialchars($machine['ip_address']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($machine['connection_type'] === 'adms'): ?>
                                        <span style="color: #666; font-style: italic;">غير مطلوب</span>
                                    <?php else: ?>
                                        <?= $machine['port'] ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status <?= $machine['connection_type'] === 'adms' ? 'online' : 'offline' ?>">
                                        <?= $machine['connection_type'] === 'adms' ? 'ADMS' : 'Public IP' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status <?= $machine['status'] === 'active' ? 'online' : 'offline' ?>">
                                        <?= $machine['status'] === 'active' ? 'نشط' : ($machine['status'] === 'inactive' ? 'غير نشط' : 'صيانة') ?>
                                    </span>
                                </td>
                                <td><?= $machine['last_sync'] ? date('Y-m-d H:i', strtotime($machine['last_sync'])) : 'لم يتم التزامن' ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-primary btn-sm" onclick="testConnection(<?= $machine['id'] ?>)" title="اختبار الاتصال">
                                            <i class="fas fa-network-wired"></i>
                                        </button>
                                        <button class="btn btn-outline btn-sm" onclick="editMachine(<?= $machine['id'] ?>)" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteMachine(<?= $machine['id'] ?>)" title="حذف">
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

    <!-- Add/Edit Machine Modal -->
    <div class="modal" id="machineModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">إضافة جهاز جديد</h3>
                <button class="close-btn" onclick="closeMachineModal()">&times;</button>
            </div>
            <form id="machineForm">
                <input type="hidden" id="machineId" name="id">
                
                <div class="form-group">
                    <label class="form-label" for="machineName">اسم الجهاز <span style="color: var(--danger)">*</span></label>
                    <input type="text" class="form-control" id="machineName" name="name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="machineLocation">الموقع</label>
                    <input type="text" class="form-control" id="machineLocation" name="location">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="machineIp">عنوان IP <span style="color: var(--danger)">*</span></label>
                    <input type="text" class="form-control" id="machineIp" name="ip_address" required placeholder="مثال: 192.168.1.100">
                    <small class="form-help" id="ipHelp">
                        تأكد من أن عنوان IP صحيح ويمكن الوصول إليه من الخادم
                    </small>
                </div>
                
                <div class="form-group" id="portGroup">
                    <label class="form-label" for="machinePort">المنفذ</label>
                    <input type="number" class="form-control" id="machinePort" name="port" value="4370">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="connectionType">نوع الاتصال <span style="color: var(--danger)">*</span></label>
                    <select class="form-control" id="connectionType" name="connection_type" onchange="toggleConnectionFields()">
                        <option value="adms">ADMS (Push من الجهاز) - موصى به</option>
                        <option value="public_ip">Public IP (Pull من الخادم)</option>
                    </select>
                    <div class="connection-help" id="connectionHelp">
                        <div class="help-adms" style="background: rgba(0, 214, 143, 0.1); padding: 0.75rem; border-radius: 0.25rem; margin-top: 0.5rem;">
                            <strong>ADMS:</strong> الجهاز يرسل البيانات تلقائياً للخادم<br>
                            <small>• يحتاج فقط: مفتاح ADMS<br>• لا يحتاج IP أو منفذ (الجهاز يتصل بالخادم)</small>
                        </div>
                        <div class="help-public" style="background: rgba(255, 170, 0, 0.1); padding: 0.75rem; border-radius: 0.25rem; margin-top: 0.5rem; display: none;">
                            <strong>Public IP:</strong> الخادم يطلب البيانات مباشرة من الجهاز<br>
                            <small>• يحتاج: IP عام + فتح المنفذ 4370<br>• لا يحتاج مفتاح ADMS</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="machineSerial">الرقم التسلسلي</label>
                    <input type="text" class="form-control" id="machineSerial" name="serial_number">
                </div>
                
                <div class="form-group" id="admsKeyGroup">
                    <label class="form-label" for="machineAdmsKey">
                        مفتاح ADMS <span style="color: var(--danger)">*</span>
                        <small style="font-weight: normal; color: var(--text-muted);">(مطلوب للـ ADMS)</small>
                    </label>
                    <input type="text" class="form-control" id="machineAdmsKey" name="adms_key" placeholder="نفس المفتاح المحدد في config/database.php">
                    <small class="form-help">
                        <strong>مهم:</strong> يجب أن يكون نفس المفتاح المحدد في ملف config/database.php<br>
                        هذا المفتاح يستخدم للتأكد من أن البيانات تأتي من جهاز موثوق
                    </small>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="machineStatus">الحالة</label>
                    <select class="form-control" id="machineStatus" name="status">
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>
                        <option value="maintenance">صيانة</option>
                    </select>
                </div>
                
                <div class="form-group" id="admsEnabledGroup">
                    <label class="form-label">
                        <input type="checkbox" id="machineAdmsEnabled" name="adms_enabled" checked style="margin-left: 0.5rem;">
                        تفعيل ADMS
                    </label>
                    <small class="form-help">يظهر فقط عند اختيار نوع الاتصال ADMS</small>
                </div>
                
                <div class="d-flex gap-2 justify-content-between">
                    <button type="button" class="btn btn-outline" onclick="closeMachineModal()">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* Additional styles for machines page */
        .form-help {
            font-size: 0.8rem;
            color: var(--text-muted, #666);
            margin-top: 0.25rem;
            line-height: 1.4;
        }
        
        .status.online {
            background-color: var(--success, #28a745);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
        }
        
        .status.offline {
            background-color: var(--warning, #ffc107);
            color: #333;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
        }
        
        #admsKeyGroup, #admsEnabledGroup {
            transition: all 0.3s ease;
        }
        
        /* Fix select dropdown styling */
        select.form-control {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }
        
        select.form-control option {
            background-color: #2a2d3a !important;
            color: white !important;
            padding: 0.5rem !important;
        }
        
        select.form-control option:hover {
            background-color: var(--primary, #4f46e5) !important;
            color: white !important;
        }
        
        select.form-control option:checked,
        select.form-control option:focus {
            background-color: var(--primary, #4f46e5) !important;
            color: white !important;
        }
        
        /* For WebKit browsers (Chrome, Safari) */
        select.form-control option:disabled {
            background-color: #1a1d29 !important;
            color: #666 !important;
        }
        
        /* For better compatibility across browsers */
        select.form-control:focus {
            border-color: var(--primary, #4f46e5) !important;
            outline: none !important;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2) !important;
        }
    </style>

    <script>
        function toggleConnectionFields() {
            const connectionType = document.getElementById('connectionType').value;
            const admsKeyGroup = document.getElementById('admsKeyGroup');
            const admsEnabledGroup = document.getElementById('admsEnabledGroup');
            const helpAdms = document.querySelector('.help-adms');
            const helpPublic = document.querySelector('.help-public');
            const ipGroup = document.querySelector('label[for="machineIp"]').closest('.form-group');
            const portGroup = document.getElementById('portGroup');
            const ipInput = document.getElementById('machineIp');
            
            if (connectionType === 'adms') {
                // Show ADMS fields
                admsKeyGroup.style.display = 'block';
                admsEnabledGroup.style.display = 'block';
                document.getElementById('machineAdmsEnabled').checked = true;
                
                // Hide IP and Port for ADMS (not needed)
                ipGroup.style.display = 'none';
                portGroup.style.display = 'none';
                
                // Set dummy values for validation
                ipInput.value = '0.0.0.0';
                ipInput.removeAttribute('required');
                
                // Show/hide help
                helpAdms.style.display = 'block';
                helpPublic.style.display = 'none';
                
            } else {
                // Hide ADMS fields
                admsKeyGroup.style.display = 'none';
                admsEnabledGroup.style.display = 'none';
                document.getElementById('machineAdmsEnabled').checked = false;
                document.getElementById('machineAdmsKey').value = '';
                
                // Show IP and Port for Public IP
                ipGroup.style.display = 'block';
                portGroup.style.display = 'block';
                
                // Restore IP field requirements
                ipInput.value = '';
                ipInput.setAttribute('required', 'required');
                ipInput.placeholder = 'مثال: 203.123.45.67';
                
                // Show/hide help
                helpAdms.style.display = 'none';
                helpPublic.style.display = 'block';
            }
        }

        function showAddMachineModal() {
            document.getElementById('modalTitle').textContent = 'إضافة جهاز جديد';
            document.getElementById('machineForm').reset();
            document.getElementById('machineId').value = '';
            document.getElementById('connectionType').value = 'adms';
            toggleConnectionFields(); // Initialize field visibility
            document.getElementById('machineModal').classList.add('show');
        }

        function closeMachineModal() {
            document.getElementById('machineModal').classList.remove('show');
        }

        function editMachine(id) {
            // Fetch machine data and populate form
            fetch('../api/get-machine.php?id=' + id)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const machine = data.machine;
                        document.getElementById('modalTitle').textContent = 'تعديل الجهاز';
                        document.getElementById('machineId').value = machine.id;
                        document.getElementById('machineName').value = machine.name || '';
                        document.getElementById('machineLocation').value = machine.location || '';
                        document.getElementById('connectionType').value = machine.connection_type || 'adms';
                        
                        // Set IP and Port only for Public IP connections
                        if (machine.connection_type === 'public_ip') {
                            document.getElementById('machineIp').value = machine.ip_address || '';
                            document.getElementById('machinePort').value = machine.port || '4370';
                        }
                        
                        document.getElementById('machineSerial').value = machine.serial_number || '';
                        document.getElementById('machineAdmsKey').value = machine.adms_key || '';
                        document.getElementById('machineStatus').value = machine.status || 'active';
                        document.getElementById('machineAdmsEnabled').checked = machine.adms_enabled == 1;
                        toggleConnectionFields(); // Update field visibility based on connection type
                        document.getElementById('machineModal').classList.add('show');
                    } else {
                        alert(data.message || 'فشل في تحميل بيانات الجهاز');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ في تحميل بيانات الجهاز');
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

        function refreshMachines() {
            location.reload();
        }

        // Handle form submission
        document.getElementById('machineForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            const name = document.getElementById('machineName').value.trim();
            const connectionType = document.getElementById('connectionType').value;
            
            if (!name) {
                alert('اسم الجهاز مطلوب');
                return;
            }
            
            // Validate based on connection type
            if (connectionType === 'public_ip') {
                const ip = document.getElementById('machineIp').value.trim();
                
                if (!ip) {
                    alert('عنوان IP مطلوب للاتصال المباشر');
                    return;
                }
                
                // IP validation
                const ipRegex = /^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/;
                if (!ipRegex.test(ip)) {
                    alert('عنوان IP غير صحيح');
                    return;
                }
            } else if (connectionType === 'adms') {
                const admsKey = document.getElementById('machineAdmsKey').value.trim();
                
                if (!admsKey) {
                    alert('مفتاح ADMS مطلوب لاتصال ADMS');
                    return;
                }
            }
            
            const formData = new FormData(this);
            const isEdit = document.getElementById('machineId').value !== '';
            formData.append('action', isEdit ? 'edit_machine' : 'add_machine');
            
            // Ensure checkbox value is sent correctly
            if (!document.getElementById('machineAdmsEnabled').checked) {
                formData.delete('adms_enabled');
            }

            fetch('machines.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                alert(data.message);
                if (data.success) {
                    closeMachineModal();
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في العملية: ' + error.message);
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('show');
            }
        });

        // Initialize connection fields visibility on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('connectionType')) {
                toggleConnectionFields();
            }
        });
    </script>

    <!-- Advanced Navbar Script -->
    <script src="../assets/js/navbar.js"></script>
</body>
</html>
