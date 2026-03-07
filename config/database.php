<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $credentialsFile = __DIR__ . '/db.credentials.php';
        $fileConfig = [];

        if (is_readable($credentialsFile)) {
            $loaded = require $credentialsFile;
            if (is_array($loaded)) {
                $fileConfig = $loaded;
            }
        }

        $host = getenv('DB_HOST') ?: ($fileConfig['host'] ?? '127.0.0.1');
        $user = getenv('DB_USER') ?: ($fileConfig['user'] ?? '');
        $pass = getenv('DB_PASS') ?: ($fileConfig['pass'] ?? '');
        $name = getenv('DB_NAME') ?: ($fileConfig['name'] ?? '');
        $port = (int) (getenv('DB_PORT') ?: ($fileConfig['port'] ?? 3306));

        if ($user === '' || $name === '') {
            throw new RuntimeException('Database is not configured.');
        }

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host === 'localhost' ? '127.0.0.1' : $host, $port, $name);

        self::$pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return self::$pdo;
    }
}
