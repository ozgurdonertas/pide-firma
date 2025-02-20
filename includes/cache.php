<?php
class Cache {
    private $cache_path;
    private $duration;
    
    public function __construct() {
        $this->cache_path = SITE_ROOT . '/cache/';
        $this->duration = CACHE_DURATION;
        
        if (!file_exists($this->cache_path)) {
            mkdir($this->cache_path, 0777, true);
        }
    }
    
    public function set($key, $data) {
        $file = $this->cache_path . md5($key);
        $content = [
            'time' => time(),
            'data' => $data
        ];
        
        return file_put_contents($file, serialize($content));
    }
    
    public function get($key) {
        $file = $this->cache_path . md5($key);
        
        if (file_exists($file)) {
            $content = unserialize(file_get_contents($file));
            
            if (time() - $content['time'] < $this->duration) {
                return $content['data'];
            }
            
            unlink($file);
        }
        
        return false;
    }
    
    public function delete($key) {
        $file = $this->cache_path . md5($key);
        if (file_exists($file)) {
            return unlink($file);
        }
        return false;
    }
    
    public function clear() {
        $files = glob($this->cache_path . '*');
        foreach($files as $file) {
            if(is_file($file)) {
                unlink($file);
            }
        }
    }
}