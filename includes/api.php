<?php
class API {
    private $endpoint;
    private $apiKey;
    private $timeout = 30;
    private $cache;
    private $logger;
    
    public function __construct() {
        $this->endpoint = SITE_URL . 'api/';
        $this->apiKey = getenv('API_KEY') ?: 'your-default-api-key';
        $this->cache = new Cache();
        $this->logger = new Logger();
    }

    private function request($method, $path, $data = null) {
        $url = $this->endpoint . trim($path, '/');
        $startTime = microtime(true);

        try {
            $ch = curl_init();
            
            $options = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->apiKey,
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'X-Request-ID: ' . uniqid()
                ]
            ];

            if ($data && in_array($method, ['POST', 'PUT'])) {
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
            }

            curl_setopt_array($ch, $options);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            $duration = microtime(true) - $startTime;

            $this->logApiRequest([
                'url' => $url,
                'method' => $method,
                'duration' => $duration,
                'http_code' => $httpCode,
                'error' => $error
            ]);

            curl_close($ch);

            if ($error) {
                throw new Exception("CURL Error: " . $error);
            }

            $result = json_decode($response, true);
            
            return [
                'success' => true,
                'data' => $result,
                'http_code' => $httpCode
            ];

        } catch (Exception $e) {
            $this->logger->log('error', 'API Request Failed', [
                'error' => $e->getMessage(),
                'url' => $url,
                'method' => $method
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'http_code' => $httpCode ?? 500
            ];
        }
    }

    public function getMenu($id = null) {
        $cacheKey = 'menu_' . ($id ?: 'all');
        $cachedData = $this->cache->get($cacheKey);
        
        if ($cachedData !== false) {
            return $cachedData;
        }

        $endpoint = $id ? "menu/{$id}" : 'menu';
        $response = $this->request('GET', $endpoint);
        
        if ($response['success']) {
            $this->cache->set($cacheKey, $response['data']);
        }
        
        return $response;
    }

    // Diğer API metodları...
}