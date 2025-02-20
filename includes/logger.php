<?php
class Logger {
    private static $logPath;
    
    public static function init() {
        self::$logPath = SITE_ROOT . '/logs/';
        if (!file_exists(self::$logPath)) {
            mkdir(self::$logPath, 0777, true);
        }
    }
    
    public static function log($type, $message, $data = []) {
        if (!isset(self::$logPath)) {
            self::init();
        }
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        
        $logFile = self::$logPath . date('Y-m-d') . '.log';
        
        return file_put_contents(
            $logFile,
            json_encode($logEntry) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }
    
    public static function getRecentLogs($limit = 100) {
        $logs = [];
        $logFiles = glob(self::$logPath . '*.log');
        rsort($logFiles);
        
        foreach($logFiles as $file) {
            $lines = array_reverse(file($file));
            foreach($lines as $line) {
                $logs[] = json_decode($line, true);
                if(count($logs) >= $limit) break;
            }
            if(count($logs) >= $limit) break;
        }
        
        return $logs;
    }
}