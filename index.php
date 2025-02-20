<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = new Database();
$conn = $db->getConnection();

// Site ayarlarını al
$settings = getSettings($conn);
// Aktif menüyü al
$activeMenu = getActiveMenu($conn);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $settings['site_title'] ?></title>
    <?php echo generateMetaTags($settings); ?>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <img src="<?= $settings['logo_path'] ?>" alt="<?= $settings['site_title'] ?>">
                </div>
                <div class="contact-info">
                    <a href="tel:<?= $settings['phone'] ?>" class="phone">
                        <?= $settings['phone'] ?>
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="hero">
        <div class="container">
            <h1>Geleneksel Lezzet, Modern Sunum</h1>
            <p><?= $settings['meta_description'] ?></p>
        </div>
    </section>

    <!-- Menu Section -->
    <section id="menu">
        <div class="container">
            <h2>Menümüz</h2>
            <?php if($activeMenu): ?>
                <div class="menu-image">
                    <img src="<?= $activeMenu['image_path'] ?>" 
                         alt="<?= $activeMenu['description'] ?>"
                         class="lazy"
                         data-src="<?= $activeMenu['image_path'] ?>">
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact">
        <div class="container">
            <h2>İletişim</h2>
            <div class="contact-details">
                <div class="address">
                    <h3>Adres</h3>
                    <p><?= $settings['address'] ?></p>
                </div>
                <div class="working-hours">
                    <h3>Çalışma Saatleri</h3>
                    <p><?= $settings['working_hours'] ?></p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= $settings['site_title'] ?></p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>