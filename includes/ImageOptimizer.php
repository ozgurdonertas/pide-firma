<?php
class ImageOptimizer {
    private $quality = 80;
    private $maxWidth = 1920;
    
    public function processAndSave($file, $type): string {
        $image = $this->createImageFromFile($file);
        
        // Boyut optimizasyonu
        $image = $this->resizeImage($image);
        
        // Dosya adı oluştur
        $fileName = time() . '_' . uniqid() . '.webp';
        $savePath = UPLOAD_PATH . $type . '/' . $fileName;
        
        // WebP formatında kaydet
        imagewebp($image, $savePath, $this->quality);
        imagedestroy($image);
        
        return $fileName;
    }
    
    private function createImageFromFile($file) {
        switch($file['type']) {
            case 'image/jpeg':
                return imagecreatefromjpeg($file['tmp_name']);
            case 'image/png':
                return imagecreatefrompng($file['tmp_name']);
            case 'image/webp':
                return imagecreatefromwebp($file['tmp_name']);
            default:
                throw new Exception('Desteklenmeyen dosya formatı');
        }
    }
    
    private function resizeImage($image) {
        $width = imagesx($image);
        $height = imagesy($image);
        
        if ($width > $this->maxWidth) {
            $newWidth = $this->maxWidth;
            $newHeight = ($height / $width) * $newWidth;
            
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, 
                             $newWidth, $newHeight, $width, $height);
            
            imagedestroy($image);
            return $newImage;
        }
        
        return $image;
    }
}