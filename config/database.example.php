<?php
/**
 * Tiv Culture Archive - Database Configuration
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    die('Direct access not permitted');
}

// Database Configuration
define('DB_HOST', php_sapi_name() === 'cli' ? '127.0.0.1' : 'localhost');
define('DB_NAME', 'tiv_archive');
define('DB_USER', php_sapi_name() === 'cli' ? 'root' : 'tivuser');
define('DB_PASS', php_sapi_name() === 'cli' ? '' : 'YOUR_PASSWORD_HERE');
define('DB_CHARSET', 'utf8mb4');

/**
 * Database Connection Singleton
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Get database connection instance
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
                ];

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);

            } catch (PDOException $e) {
                if (ENVIRONMENT === 'development') {
                    die('Database connection failed: ' . $e->getMessage());
                } else {
                    error_log('Database connection failed: ' . $e->getMessage());
                    die('Database connection error. Please try again later.');
                }
            }
        }

        return self::$instance;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
