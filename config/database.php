<?php
$host = "localhost";
$dbname = "qadria";
$username = "root";
$password = "";

// Cache directory
$cache_dir = __DIR__ . '/../cache/';
if (!file_exists($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

// Simple cache system
class Cache {
    private static $dir = null;
    
    public static function init() {
        if (self::$dir === null) {
            self::$dir = __DIR__ . '/../cache/';
        }
    }
    
    public static function get($key) {
        self::init();
        $file = self::$dir . md5($key) . '.cache';
        
        if (file_exists($file)) {
            $data = unserialize(file_get_contents($file));
            if ($data['expiry'] > time()) {
                return $data['value'];
            }
            @unlink($file);
        }
        return null;
    }
    
    public static function set($key, $value, $ttl = 3600) {
        self::init();
        $file = self::$dir . md5($key) . '.cache';
        $data = [
            'value' => $value,
            'expiry' => time() + $ttl
        ];
        file_put_contents($file, serialize($data));
    }
    
    public static function clear() {
        self::init();
        $files = glob(self::$dir . '*.cache');
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        ]
    );
    
    $pdo->exec("SET SESSION sql_mode = ''");
    
} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("Connection Failed: " . $e->getMessage());
}

// Create directories
$base_dir = __DIR__ . '/../invoices/';
if (!file_exists($base_dir)) mkdir($base_dir, 0755, true);

$exports_dir = __DIR__ . '/../exports/';
if (!file_exists($exports_dir)) mkdir($exports_dir, 0755, true);
?>