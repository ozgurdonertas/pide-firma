<?php
class FileValidator {
    private $file;
    private $error;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    private $maxSize = 5242880; // 5MB
    
    public function __construct($file) {
        $this->file = $file;
    }
    
    public function validate(): bool {
        // Dosya boyutu kontrolü
        if ($this->file['size'] > $this->maxSize) {
            $this->error = 'Dosya boyutu çok büyük (Max: 5MB)';
            return false;
        }
        
        // Dosya tipi kontrolü
        if (!in_array($this->file['type'], $this->allowedTypes)) {
            $this->error = 'Geçersiz dosya tipi';
            return false;
        }
        
        // Dosya içeriği kontrolü
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            $this->error = 'Geçersiz dosya içeriği';
            return false;
        }
        
        return true;
    }
    
    public function getError(): string {
        return $this->error;
    }
}