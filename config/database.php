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
    // ─── Credenciales ──────────────────────────────────────────────────────
    private const HOST     = 'localhost';
    private const PORT     = 3306;
    private const DB_NAME  = 'menu_studio';
    private const USERNAME = 'root';
    private const PASSWORD = '';
    private const CHARSET  = 'utf8mb4';

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
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                self::HOST,
                self::PORT,
                self::DB_NAME,
                self::CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ];

            try {
                self::$instance = new PDO($dsn, self::USERNAME, self::PASSWORD, $options);
            } catch (\PDOException $e) {
                if (defined('APP_ENV') && APP_ENV === 'development') {
                    throw new \PDOException("Database connection failed: " . $e->getMessage());
                }
                throw new \PDOException("Database connection failed. Please try again later.");
            }
        }

        return self::$instance;
    }
}
