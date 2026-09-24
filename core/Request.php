<?php
/**
 * ============================================================================
 * Menu Studio — Request Helper
 * ============================================================================
 * Wrapper para datos de la petición HTTP con sanitización integrada.
 */

class Request
{
    /**
     * Obtener un valor GET sanitizado
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return isset($_GET[$key]) ? self::sanitize($_GET[$key]) : $default;
    }

    /**
     * Obtener un valor POST sanitizado
     */
    public static function post(string $key, mixed $default = null): mixed
    {
        return isset($_POST[$key]) ? self::sanitize($_POST[$key]) : $default;
    }

    /**
     * Obtener datos del cuerpo JSON
     */
    public static function json(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Obtener archivo subido
     */
    public static function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Obtener el método HTTP real
     */
    public static function method(): string
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST' && isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        }
        return $method;
    }

    /**
     * Verificar si la petición es AJAX
     */
    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Verificar si la petición espera JSON
     */
    public static function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json');
    }

    /**
     * Sanitizar un valor string
     */
    private static function sanitize(mixed $value): mixed
    {
        if (is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
        if (is_array($value)) {
            return array_map([self::class, 'sanitize'], $value);
        }
        return $value;
    }
}
