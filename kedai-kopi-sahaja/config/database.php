<?php
/** Koneksi database (PDO MySQL / MariaDB). */
declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_NAME = 'sahaja_db';
const DB_USER = 'root';
const DB_PASS = '';          // XAMPP default: kosong. Di hosting: isi password asli.

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('Koneksi DB gagal: ' . $e->getMessage());
            http_response_code(500);
            exit('Maaf, database belum tersambung. Periksa config/database.php dan impor database/sahaja_db.sql.');
        }
    }
    return $pdo;
}
