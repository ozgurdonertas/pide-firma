<?php
require_once '../../includes/config.php';
require_once '../../includes/security.php';

// Operasyonel güvenlik kontrolü
if (!SecurityEnhanced::validateRequest()) {
    Logger::log('security', 'Unauthorized upload attempt');
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

try {
    $response = ['success' => false, 'message' => ''];
    
    if (isset($_FILES['menu_image'])) {
        $file = $_FILES['menu_image'];
        $description = SecurityEnhanced::sanitizeInput($_POST['description'] ?? '');
        
        // Dosya validasyonu
        $validator = new FileValidator($file);
        if (!$validator->validate()) {
            throw new Exception($validator->getError());
        }
        
        // Optimize et ve kaydet
        $optimizer = new ImageOptimizer();
        $fileName = $optimizer->processAndSave($file, 'menu');
        
        // Veritabanı işlemleri
        $db = new Database();
        $conn = $db->getConnection();
        
        // Transaction başlat
        $conn->beginTransaction();
        
        try {
            // Eski aktif menüyü pasifleştir
            $stmt = $conn->prepare("UPDATE menu_images SET active = 0 WHERE active = 1");
            $stmt->execute();
            
            // Yeni menüyü ekle
            $stmt = $conn->prepare("
                INSERT INTO menu_images (image_path, description, active) 
                VALUES (?, ?, 1)
            ");
            
            $imagePath = '/uploads/menu/' . $fileName;
            $stmt->execute([$imagePath, $description]);
            
            // Transaction'ı onayla
            $conn->commit();
            
            // Cache'i temizle
            $cache = new Cache();
            $cache->delete('active_menu');
            
            $response['success'] = true;
            $response['message'] = 'Menü başarıyla yüklendi';
            
            Logger::log('menu', 'Menu uploaded successfully', [
                'file' => $fileName,
                'description' => $description
            ]);
            
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
} catch (Exception $e) {
    Logger::log('error', 'Menu upload failed', [
        'error' => $e->getMessage()
    ]);
    $response['message'] = 'İşlem başarısız: ' . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
