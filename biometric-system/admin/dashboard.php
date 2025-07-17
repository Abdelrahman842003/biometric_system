<?php
require_once __DIR__ . '/../auth.php';
Auth::requireLogin();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة البايومترك | لوحة التحكم</title>
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
            <h2 class="menu-title">لوحة التحكم</h2>
            <p class="menu-subtitle">نظام إدارة البايومترك المتقدم</p>
        </div>
        
        <ul class="nav-items">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active">
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
                    <h1 class="page-title">لوحة التحكم</h1>
                    <p class="page-subtitle">نظرة عامة على النشاط والإحصائيات</p>
                </div>
                <button class="btn btn-primary" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i>
                    تحديث البيانات
                </button>
            </div>

            <!-- Stats Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-desktop"></i>
                            إجمالي الأجهزة
                        </h3>
                    </div>
                    <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary);" id="total-machines">
                        <div class="loading"></div>
                    </div>
                    <small style="color: rgba(255, 255, 255, 0.7);">الأجهزة المسجلة في النظام</small>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users"></i>
                            المستخدمين
                        </h3>
                    </div>
                    <div style="font-size: 2.5rem; font-weight: bold; color: var(--success);" id="total-users">
                        <div class="loading"></div>
                    </div>
                    <small style="color: rgba(255, 255, 255, 0.7);">المستخدمين المسجلين</small>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock"></i>
                            حضور اليوم
                        </h3>
                    </div>
                    <div style="font-size: 2.5rem; font-weight: bold; color: var(--warning);" id="today-attendance">
                        <div class="loading"></div>
                    </div>
                    <small style="color: rgba(255, 255, 255, 0.7);">سجلات حضور اليوم</small>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">النشاط الأخير</h3>
                    <a href="attendance.php" class="btn btn-outline">عرض الكل</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>المستخدم</th>
                                <th>الجهاز</th>
                                <th>الوقت</th>
                                <th>النوع</th>
                                <th>طريقة التحقق</th>
                            </tr>
                        </thead>
                        <tbody id="recent-activity">
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem;">
                                    <div class="loading"></div>
                                    جاري تحميل البيانات...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Machine Status -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">حالة الأجهزة</h3>
                    <a href="machines.php" class="btn btn-outline">إدارة الأجهزة</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>اسم الجهاز</th>
                                <th>الموقع</th>
                                <th>عنوان IP</th>
                                <th>الحالة</th>
                                <th>آخر تزامن</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="machines-status">
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem;">
                                    <div class="loading"></div>
                                    جاري تحميل البيانات...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dashboard functionality
        function refreshData() {
            loadDashboardStats();
            loadRecentActivity();
            loadMachinesStatus();
        }

        function loadDashboardStats() {
            fetch('../api/dashboard-stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('total-machines').textContent = data.stats.total_machines || 0;
                        document.getElementById('total-users').textContent = data.stats.total_users || 0;
                        document.getElementById('today-attendance').textContent = data.stats.today_attendance || 0;
                    }
                })
                .catch(error => {
                    console.error('Error loading dashboard stats:', error);
                    document.getElementById('total-machines').textContent = '0';
                    document.getElementById('total-users').textContent = '0';
                    document.getElementById('today-attendance').textContent = '0';
                });
        }

        function loadRecentActivity() {
            fetch('../api/recent-activity.php?limit=10')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('recent-activity');
                    if (data.success && data.logs && data.logs.length > 0) {
                        tbody.innerHTML = '';
                        data.logs.forEach(log => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${log.user_name || log.user_id}</td>
                                <td>${log.machine_name || 'غير محدد'}</td>
                                <td>${new Date(log.log_time).toLocaleString('ar-SA')}</td>
                                <td><span class="status ${log.log_type === 'check_in' ? 'online' : 'pending'}">${log.log_type === 'check_in' ? 'دخول' : 'خروج'}</span></td>
                                <td>${log.verify_type === 'fingerprint' ? 'بصمة' : (log.verify_type === 'face' ? 'وجه' : log.verify_type)}</td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">لا توجد أنشطة حديثة</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error loading recent activity:', error);
                    document.getElementById('recent-activity').innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">خطأ في تحميل البيانات</td></tr>';
                });
        }

        function loadMachinesStatus() {
            fetch('../api/machines-status.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('machines-status');
                    if (data.success && data.machines && data.machines.length > 0) {
                        tbody.innerHTML = '';
                        data.machines.forEach(machine => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${machine.name}</td>
                                <td>${machine.location || '-'}</td>
                                <td>${machine.ip_address}:${machine.port}</td>
                                <td><span class="status ${machine.is_active ? 'online' : 'offline'}">${machine.is_active ? 'متصل' : 'غير متصل'}</span></td>
                                <td>${machine.last_sync ? new Date(machine.last_sync).toLocaleString('ar-SA') : 'لم يتم'}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick="testConnection(${machine.id})">
                                        <i class="fas fa-plug"></i>
                                        اختبار
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">لا توجد أجهزة مسجلة</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error loading machines status:', error);
                    document.getElementById('machines-status').innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">خطأ في تحميل البيانات</td></tr>';
                });
        }

        function testConnection(machineId) {
            const btn = event.target.closest('button');
            btn.classList.add('loading');
            
            fetch('../api/test-connection.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `machine_id=${machineId}`
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message || 'تم اختبار الاتصال');
                btn.classList.remove('loading');
            })
            .catch(error => {
                console.error('Error testing connection:', error);
                alert('فشل في اختبار الاتصال');
                btn.classList.remove('loading');
            });
        }

        // Auto refresh every 30 seconds
        setInterval(refreshData, 30000);

        // Initial load
        document.addEventListener('DOMContentLoaded', function() {
            refreshData();
        });
    </script>

    <!-- Advanced Navbar Script -->
    <script src="../assets/js/navbar.js"></script>
</body>
</html>
