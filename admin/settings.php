<?php
require_once '../includes/config.php';
require_once '../includes/security.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Ayarları getir
$stmt = $conn->prepare("SELECT * FROM settings WHERE id = 1");
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $conn->prepare("
            UPDATE settings SET 
            site_title = ?,
            meta_description = ?,
            meta_keywords = ?,
            phone = ?,
            email = ?,
            address = ?,
            working_hours = ?,
            updated_at = NOW()
            WHERE id = 1
        ");
        
        $stmt->execute([
            $_POST['site_title'],
            $_POST['meta_description'],
            $_POST['meta_keywords'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['address'],
            $_POST['working_hours']
        ]);

        // Logo yükleme kontrolü
        if(isset($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
            $optimizer = new ImageOptimizer();
            $fileName = $optimizer->processAndSave($_FILES['logo'], 'logo');
            
            $stmt = $conn->prepare("UPDATE settings SET logo_path = ? WHERE id = 1");
            $stmt->execute(['/uploads/logo/' . $fileName]);
        }

        // Cache temizle
        $cache = new Cache();
        $cache->delete('site_settings');

        $success = "Ayarlar başarıyla güncellendi";
        
        // Güncel ayarları getir
        $stmt = $conn->prepare("SELECT * FROM settings WHERE id = 1");
        $stmt->execute();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        $error = "Bir hata oluştu: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Ayarları</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <h1>Site Ayarları</h1>
            
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="settings-form">
                <div class="form-group">
                    <label>Site Başlığı</label>
                    <input type="text" name="site_title" value="<?= htmlspecialchars($settings['site_title']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Meta Açıklama</label>
                    <textarea name="meta_description" required><?= htmlspecialchars($settings['meta_description']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Meta Anahtar Kelimeler</label>
                    <textarea name="meta_keywords"><?= htmlspecialchars($settings['meta_keywords']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Telefon</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($settings['phone']) ?>">
                </div>
                
                <div class="form-group">
                    <label>E-posta</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($settings['email']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Adres</label>
                    <textarea name="address"><?= htmlspecialchars($settings['address']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Çalışma Saatleri</label>
                    <textarea name="working_hours"><?= htmlspecialchars($settings['working_hours']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Logo</label>
                    <?php if($settings['logo_path']): ?>
                        <div class="current-logo">
                            <img src="<?= $settings['logo_path'] ?>" alt="Mevcut Logo">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="logo" accept="image/*">
                </div>
                
                <button type="submit" class="btn btn-primary">Ayarları Kaydet</button>
            </form>
        </main>
    </div>
    
    <script src="assets/js/admin.js"></script>
</body>
</html>