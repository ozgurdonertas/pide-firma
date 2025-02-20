<?php
class Optimizer {
    private $compressionQuality = 80;
    private $maxImageWidth = 1920;
    private $maxImageHeight = 1080;
    private $allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp'];
    private $cacheControl = 2592000; // 30 gün

    /**
     * HTML Optimizasyonu
     */
    public static function compressHTML($html) {
        $search = [
            '/\>[^\S ]+/s',     // boşlukları temizle
            '/[^\S ]+\</s',     // tag öncesi boşlukları temizle
            '/(\s)+/s',         // birden fazla boşluğu tekile indir
            '/<!--(.|\s)*?-->/' // yorumları kaldır
        ];

        $replace = [
            '>',
            '<',
            '\\1',
            ''
        ];

        return preg_replace($search, $replace, $html);
    }

    /**
     * Görsel Optimizasyonu
     */
    public function optimizeImage($source, $destination, $type = null) {
        // Görsel tipini kontrol et
        $imageInfo = getimagesize($source);
        $type = $type ?? $imageInfo['mime'];

        if (!in_array($type, $this->allowedImageTypes)) {
            throw new Exception('Desteklenmeyen görsel formatı');
        }

        // Kaynak görseli yükle
        $sourceImage = $this->createImageFromSource($source, $type);
        
        // Boyutları optimize et
        list($width, $height) = $this->calculateOptimalDimensions(
            imagesx($sourceImage),
            imagesy($sourceImage)
        );

        // Yeni görsel oluştur
        $optimizedImage = imagecreatetruecolor($width, $height);
        
        // PNG/WebP için şeffaflığı koru
        if (in_array($type, ['image/png', 'image/webp'])) {
            imagealphablending($optimizedImage, false);
            imagesavealpha($optimizedImage, true);
        }

        // Görseli yeniden boyutlandır
        imagecopyresampled(
            $optimizedImage, $sourceImage,
            0, 0, 0, 0,
            $width, $height,
            imagesx($sourceImage),
            imagesy($sourceImage)
        );

        // WebP formatında kaydet
        imagewebp($optimizedImage, $destination, $this->compressionQuality);

        // Belleği temizle
        imagedestroy($sourceImage);
        imagedestroy($optimizedImage);

        return true;
    }

    /**
     * CSS Optimizasyonu
     */
    public static function optimizeCSS($css) {
        // Yorumları kaldır
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Gereksiz boşlukları temizle
        $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
        
        // Gereksiz noktalı virgülleri temizle
        $css = str_replace(';}', '}', $css);
        
        return trim($css);
    }

    /**
     * JavaScript Optimizasyonu
     */
    public static function optimizeJS($js) {
        // Yorumları kaldır
        $js = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/', '', $js);
        
        // Gereksiz boşlukları temizle
        $js = preg_replace('/\s+/', ' ', $js);
        
        return trim($js);
    }

    /**
     * Cache Header Optimizasyonu
     */
    public static function setCacheHeaders() {
        $timestamp = gmdate("D, d M Y H:i:s", time() + 2592000) . " GMT";
        
        header("Cache-Control: public, max-age=2592000");
        header("Expires: $timestamp");
        header("Pragma: cache");
    }

    /**
     * Yardımcı Metodlar
     */
    private function createImageFromSource($source, $type) {
        switch($type) {
            case 'image/jpeg':
                return imagecreatefromjpeg($source);
            case 'image/png':
                return imagecreatefrompng($source);
            case 'image/webp':
                return imagecreatefromwebp($source);
            default:
                throw new Exception('Desteklenmeyen görsel formatı');
        }
    }

    private function calculateOptimalDimensions($width, $height) {
        if ($width > $this->maxImageWidth) {
            $ratio = $this->maxImageWidth / $width;
            $width = $this->maxImageWidth;
            $height = $height * $ratio;
        }

        if ($height > $this->maxImageHeight) {
            $ratio = $this->maxImageHeight / $height;
            $height = $this->maxImageHeight;
            $width = $width * $ratio;
        }

        return [round($width), round($height)];
    }

    /**
     * Sistem Performans Optimizasyonu
     */
    public static function optimizeSystem() {
        // PHP bellek limitini ayarla
        ini_set('memory_limit', '256M');
        
        // Maksimum yürütme süresini ayarla
        set_time_limit(300);
        
        // Çıktı tamponlamasını etkinleştir
        ob_start("ob_gzhandler");
        
        // Hata raporlamayı devre dışı bırak
        error_reporting(0);
        ini_set('display_errors', 0);
    }
}