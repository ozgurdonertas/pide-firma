<?php
require_once '../../includes/config.php';
require_once '../../includes/security.php';

if (!SecurityEnhanced::validateRequest()) {
    Logger::log('security', 'Unauthorized delete attempt');
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $menuId = (int)$data['id'];
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Transaction başlat
    $conn->beginTransaction();
    
    try {
        // Menü bilgilerini al
        $stmt = $conn->prepare("SELECT image_path FROM menu_images WHERE id = ?");
        $stmt->execute([$menuId]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$menu) {
            throw new Exception('Menü bulunamadı');
        }
        
        // Dosyayı sil
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $menu['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Veritabanından sil
        $stmt = $conn->prepare("DELETE FROM menu_images WHERE id = ?");
        $stmt->execute([$menuId]);
        
        // Transaction'ı onayla
        $conn->commit();
        
        // Cache'i temizle
        $cache = new Cache();
        $cache->delete('active_menu');
        
        Logger::log('menu', 'Menu deleted successfully', [
            'menu_id' => $menuId,
            'file_path' => $menu['image_path']
        ]);
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    Logger::log('error', 'Menu deletion failed', [
        'error' => $e->getMessage(),
        'menu_id' => $menuId ?? null
    ]);
    
    echo json_encode([
        'success' => false,
        'message' => 'Menü silinirken bir hata oluştu: ' . $e->getMessage()
    ]);
}