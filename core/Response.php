<?php
/**
 * ============================================================================
 * Menu Studio — Response Helper
 * ============================================================================
 * Helper para enviar respuestas HTTP estructuradas (JSON, redirect, error).
 */

class Response
{
    /**
     * Enviar respuesta JSON exitosa
     */
    public static function success(mixed $data = null, string $message = 'OK', int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Enviar respuesta JSON de error
     */
    public static function error(string $message, int $code = 400, mixed $errors = null): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Establecer cabeceras CORS para API
     */
    public static function corsHeaders(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
