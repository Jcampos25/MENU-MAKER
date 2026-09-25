<?php
/**
 * ============================================================================
 * Menu Studio — Configuración de Aplicación
 * ============================================================================
 * Constantes globales y configuración del sistema.
 */

// ─── Detección Automática de Entorno ────────────────────────────────────────
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'])
           || str_starts_with($_SERVER['HTTP_HOST'] ?? '', 'localhost:')
           || str_starts_with($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1:');

// ─── Información de la Aplicación ──────────────────────────────────────────
define('APP_NAME',    'Menu Studio');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     $isLocal ? 'development' : 'production');

// ─── Rutas Base ────────────────────────────────────────────────────────────
define('BASE_PATH',   dirname(__DIR__));
define('APP_PATH',    BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('CORE_PATH',   BASE_PATH . '/core');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('VIEWS_PATH',  APP_PATH . '/Views');

// ─── URL Base ──────────────────────────────────────────────────────────────
// En local: '/MENU%20MAKER/public' | En hosting (InfinityFree): '' (raíz)
define('APP_URL',     $isLocal ? '/MENU%20MAKER/public' : '');
define('ASSETS_URL',  APP_URL . '/assets');
define('UPLOADS_URL', APP_URL . '/uploads');

// ─── Límites de Subida ────────────────────────────────────────────────────
define('MAX_UPLOAD_SIZE',   10 * 1024 * 1024); // 10 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);
define('ALLOWED_FONT_TYPES',  ['font/woff2', 'font/woff', 'font/ttf', 'font/otf', 'application/x-font-woff2', 'application/x-font-woff', 'application/x-font-ttf', 'application/x-font-otf']);

// ─── Configuración de Errores ──────────────────────────────────────────────
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// ─── Sesión ────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
