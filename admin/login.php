<?php
require_once '../includes/config.php';
require_once '../includes/security.php';

// Session kontrolü
if(isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

// Login işlemi
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = SecurityEnhanced::sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ? AND active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['last_activity'] = time();
            
            Logger::log('auth', 'Başarılı giriş', ['user_id' => $user['id']]);
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Geçersiz kullanıcı adı veya şifre";
            Logger::log('auth', 'Başarısız giriş denemesi', ['username' => $username]);
        }
    } catch(PDOException $e) {
        Logger::log('error', 'Login hatası', ['message' => $e->getMessage()]);
        $error = "Sistem hatası oluştu";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Girişi</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h2>Yönetici Girişi</h2>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label>Kullanıcı Adı</label>
                    <input type="text" name="username" required 
                           class="form-control" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label>Şifre</label>
                    <input type="password" name="password" required 
                           class="form-control">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Giriş Yap
                </button>
            </form>
        </div>
    </div>
</body>
</html>