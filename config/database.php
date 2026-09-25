<?php
/**
 * ============================================================================
 * Menu Studio — Configuración de Base de Datos
 * ============================================================================
 * Clase Singleton para conexión PDO segura a MySQL.
 * Configuración dual: detecta y conmuta automáticamente entre
 * entorno LOCAL (XAMPP) y entorno EN LÍNEA (InfinityFree).
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
            $hostHeader = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');

            // Detección automática: Local (XAMPP) vs En Línea (InfinityFree)
            $isLocal = in_array($hostHeader, ['localhost', '127.0.0.1', '::1'])
                       || str_starts_with($hostHeader, 'localhost:')
                       || str_starts_with($hostHeader, '127.0.0.1:')
                       || (php_sapi_name() === 'cli' && empty($_SERVER['HTTP_HOST']));

            if ($isLocal) {
                // ─── 1. CONFIGURACIÓN LOCAL (XAMPP) ─────────────────────────
                $host     = 'localhost';
                $port     = 3306;
                $dbName   = 'menu_studio';
                $username = 'root';
                $password = '';
                $charset  = 'utf8mb4';
            } else {
                // ─── 2. CONFIGURACIÓN EN LÍNEA (INFINITYFREE) ────────────────
                $host     = 'sql205.infinityfree.com';
                $port     = 3306;
                $dbName   = 'if0_43001696_menu_studio';
                $username = 'if0_43001696';
                $password = 'nS8kvvkNhkeX7';
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
                if ($isLocal) {
                    throw new \PDOException("Error de conexión LOCAL (XAMPP): " . $e->getMessage());
                }
                throw new \PDOException("Error de conexión EN LÍNEA (InfinityFree): " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}

