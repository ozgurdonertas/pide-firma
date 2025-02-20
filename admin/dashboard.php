<?php
require_once '../includes/config.php';
require_once '../includes/security.php';
require_once '../includes/functions.php';

// Oturum ve güvenlik kontrolü
SecurityEnhanced::validateSession();

$db = new Database();
$conn = $db->getConnection();

// Performans optimizasyonu için cache kontrolü
$cache = new Cache();
$menuData = $cache->get('active_menu');

if (!$menuData) {
    // Cache yoksa veritabanından çek
    $stmt = $conn->prepare("SELECT * FROM menu_images ORDER BY upload_date DESC");
    $stmt->execute();
    $menuData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $cache->set('active_menu', $menuData);
}

// Sistem metrikleri
$metrics = [
    'total_menus' => count($menuData),
    'active_menu' => array_filter($menuData, fn($m) => $m['active'] == 1),
    'last_update' => end($menuData)['upload_date'] ?? 'Henüz yükleme yapılmamış'
];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli - Dashboard</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <h2>Yönetim Paneli</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="dashboard.php">Dashboard</a>
                    </li>
                    <li>
                        <a href="settings.php">Site Ayarları</a>
                    </li>
                    <li>
                        <a href="logout.php">Çıkış</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1>Dashboard</h1>
                <button class="btn btn-primary" onclick="openUploadModal()">
                    Yeni Menü Yükle
                </button>
            </div>

            <!-- Metrics Cards -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <h3>Toplam Menü</h3>
                    <p class="metric-value"><?= $metrics['total_menus'] ?></p>
                </div>
                <div class="metric-card">
                    <h3>Aktif Menü</h3>
                    <p class="metric-value"><?= count($metrics['active_menu']) ?></p>
                </div>
                <div class="metric-card">
                    <h3>Son Güncelleme</h3>
                    <p class="metric-value"><?= formatDate($metrics['last_update']) ?></p>
                </div>
            </div>

            <!-- Menu List -->
            <div class="menu-list">
                <h2>Menü Listesi</h2>
                <div class="menu-grid">
                    <?php foreach($menuData as $menu): ?>
                    <div class="menu-item" data-id="<?= $menu['id'] ?>">
                        <img src="<?= $menu['image_path'] ?>" alt="<?= $menu['description'] ?>">
                        <div class="menu-actions">
                            <label class="switch">
                                <input type="checkbox" 
                                       <?= $menu['active'] ? 'checked' : '' ?>
                                       onchange="toggleMenuStatus(<?= $menu['id'] ?>)">
                                <span class="slider"></span>
                            </label>
                            <button class="btn btn-danger btn-sm" 
                                    onclick="deleteMenu(<?= $menu['id'] ?>)">
                                Sil
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <h2>Yeni Menü Yükle</h2>
            <form id="menuUploadForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Menü Resmi</label>
                    <input type="file" name="menu_image" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label>Açıklama</label>
                    <input type="text" name="description" required>
                </div>
                <button type="submit" class="btn btn-primary">Yükle</button>
            </form>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>