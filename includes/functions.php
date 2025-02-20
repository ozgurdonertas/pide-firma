<?php
function generateMetaTags($settings) {
    return [
        '<meta name="description" content="' . htmlspecialchars($settings['meta_description']) . '">',
        '<meta name="keywords" content="' . htmlspecialchars($settings['meta_keywords']) . '">',
        '<meta property="og:title" content="' . htmlspecialchars($settings['site_title']) . '">',
        '<meta property="og:description" content="' . htmlspecialchars($settings['meta_description']) . '">',
        '<meta property="og:type" content="website">',
        '<link rel="canonical" href="' . SITE_URL . '">'
    ];
}

function formatDate($date) {
    return date('d.m.Y H:i', strtotime($date));
}

function getSettings($conn) {
    $cache = new Cache();
    $settings = $cache->get('site_settings');
    
    if (!$settings) {
        $stmt = $conn->prepare("SELECT * FROM settings WHERE id = 1");
        $stmt->execute();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        $cache->set('site_settings', $settings);
    }
    
    return $settings;
}

function getActiveMenu($conn) {
    $cache = new Cache();
    $menu = $cache->get('active_menu');
    
    if (!$menu) {
        $stmt = $conn->prepare("SELECT * FROM menu_images WHERE active = 1 LIMIT 1");
        $stmt->execute();
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);
        $cache->set('active_menu', $menu);
    }
    
    return $menu;
}

function generateSlug($string) {
    $string = mb_strtolower($string, 'UTF-8');
    $string = str_replace(['ı','ğ','ü','ş','ö','ç'], ['i','g','u','s','o','c'], $string);
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}