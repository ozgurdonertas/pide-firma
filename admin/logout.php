<?php
require_once '../includes/config.php';
require_once '../includes/logger.php';

Logger::log('auth', 'User logged out', ['user_id' => $_SESSION['admin_id'] ?? null]);

session_destroy();
header('Location: login.php');
exit;