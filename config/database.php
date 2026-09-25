<?php
/**
 * ============================================================================
 * Menu Studio — Configuración de Base de Datos
 * ============================================================================
 * Clase Singleton para conexión PDO segura a MySQL 8.0
 * - Charset: utf8mb4
 * - Emulate prepares: false (consultas preparadas nativas)
 * - Error mode: exceptions
 */

class Database
{
    /** @var PDO|null Instancia única de conexión */
    private static ?PDO $instance = null;

    /**
     * Prevenir instanciación directa
     */
    private function __construct() {}

    /**
     * Prevenir clonación
     */
    private function __clone() {}

    /**
     * Obtener la instancia PDO (Singleton)
     *
     * @return PDO Conexión activa a la base de datos
     * @throws PDOException Si la conexión falla
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'])
                       || str_starts_with($_SERVER['HTTP_HOST'] ?? '', 'localhost:')
                       || str_starts_with($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1:');

            if ($isLocal) {
                // Entorno Local (XAMPP)
                $host     = 'localhost';
                $port     = 3306;
                $dbName   = 'menu_studio';
                $username = 'root';
                $password = '';
                $charset  = 'utf8mb4';
            } else {
                // Entorno Hosting (InfinityFree)
                // NOTA: Ajusta el host según el 'MySQL Hostname' indicado en tu cPanel de InfinityFree
                $host     = getenv('DB_HOST') ?: 'sql300.infinityfree.com';
                $port     = (int)(getenv('DB_PORT') ?: 3306);
                $dbName   = getenv('DB_NAME') ?: 'if0_43001696_menu_studio';
                $username = getenv('DB_USER') ?: 'if0_43001696';
                $password = getenv('DB_PASS') ?: 'nS8kvvkNhkeX7';
                $charset  = 'utf8mb4';
            }

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $host,
                $port,
                $dbName,
                $charset
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ];

            try {
                self::$instance = new PDO($dsn, $username, $password, $options);
            } catch (\PDOException $e) {
                if (defined('APP_ENV') && APP_ENV === 'development') {
                    throw new \PDOException("Error de base de datos local: " . $e->getMessage());
                }
                throw new \PDOException("Error de conexión a la base de datos en hosting: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
