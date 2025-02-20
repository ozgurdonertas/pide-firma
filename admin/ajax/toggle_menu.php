<?php
require_once '../../includes/config.php';
require_once '../../includes/security.php';

if (!SecurityEnhanced::validateRequest()) {
    Logger::log('security', 'Unauthorized toggle attempt');
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
        // Tüm menüleri pasif yap
        $stmt = $conn->prepare("UPDATE menu_images SET active = 0");
        $stmt->execute();
        
        // Seçilen menüyü aktif yap
        $stmt = $conn->prepare("UPDATE menu_images SET active = 1 WHERE id = ?");
        $stmt->execute([$menuId]);
        
        // Transaction'ı onayla
        $conn->commit();
        
        // Cache'i temizle
        $cache = new Cache();
        $cache->delete('active_menu');
        
        Logger::log('menu', 'Menu status toggled', [
            'menu_id' => $menuId,
            'status' => 'active'
        ]);
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    Logger::log('error', 'Menu toggle failed', [
        'error' => $e->getMessage(),
        'menu_id' => $menuId ?? null
    ]);
    
    echo json_encode([
        'success' => false,
        'message' => 'Menü durumu değiştirilirken bir hata oluştu: ' . $e->getMessage()
    ]);
}