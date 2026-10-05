<?php
/**
 * Konfigurasi koneksi database.
 *
 * Lokal (XAMPP)  : biarkan seperti di bawah.
 * InfinityFree   : ganti keempat nilai sesuai data di menu "MySQL Databases"
 *                  pada panel hosting (contoh host: sql123.infinityfree.com).
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_inventaris');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Mengembalikan satu koneksi PDO yang dipakai bersama (singleton sederhana).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Koneksi database gagal. Periksa pengaturan di config/database.php.');
        }
    }

    return $pdo;
}
