<?php
/**
 * ============================================================================
 * Menu Studio — Front Controller
 * ============================================================================
 * Punto de entrada único de la aplicación. Carga la configuración,
 * los archivos del core y despacha la petición al router.
 */

// ─── Cargar Configuración ──────────────────────────────────────────────────
require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';

// ─── Cargar Core ───────────────────────────────────────────────────────────
require_once CORE_PATH . '/Router.php';
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/Model.php';
require_once CORE_PATH . '/Request.php';
require_once CORE_PATH . '/Response.php';

// ─── Cargar Modelos ────────────────────────────────────────────────────────
require_once APP_PATH . '/Models/User.php';
require_once APP_PATH . '/Models/Restaurant.php';
require_once APP_PATH . '/Models/Template.php';
require_once APP_PATH . '/Models/Menu.php';
require_once APP_PATH . '/Models/MediaAsset.php';
require_once APP_PATH . '/Models/CustomFont.php';

// ─── Definir Rutas ─────────────────────────────────────────────────────────
$router = new Router();

// Dashboard
$router->get('/',                      'DashboardController@index');
$router->get('/dashboard',             'DashboardController@index');

// Menús — CRUD Web
$router->get('/menus',                 'MenuController@index');
$router->get('/menus/create',          'MenuController@create');
$router->post('/menus',                'MenuController@store');
$router->get('/menus/{id}/edit',       'MenuController@edit');
$router->post('/menus/{id}',           'MenuController@update');
$router->post('/menus/{id}/delete',    'MenuController@destroy');
$router->post('/menus/{id}/duplicate', 'MenuController@duplicate');

// Plantillas
$router->get('/templates',            'TemplateController@index');

// ─── API JSON (para el editor AJAX) ───────────────────────────────────────
$router->get('/api/menus/{id}',              'ApiController@getMenu');
$router->put('/api/menus/{id}/content',      'ApiController@updateContent');
$router->put('/api/menus/{id}/styles',       'ApiController@updateStyles');
$router->put('/api/menus/{id}',              'ApiController@updateMenu');
$router->get('/api/templates',               'ApiController@getTemplates');
$router->get('/api/templates/{id}',          'ApiController@getTemplate');
$router->get('/api/fonts',                   'ApiController@getFonts');
$router->post('/api/media/upload',           'ApiController@uploadMedia');
$router->get('/api/media/{restaurantId}',    'ApiController@getMedia');

// ─── Despachar ─────────────────────────────────────────────────────────────
try {
    $router->dispatch();
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;padding:32px;max-width:850px;margin:50px auto;background:#ffffff;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.08);border-left:6px solid #ef4444;color:#1e293b">';
    echo '<h2 style="color:#ef4444;margin-top:0;display:flex;align-items:center;gap:10px;">⚠️ Error de Ejecución</h2>';
    echo '<p style="font-size:16px;line-height:1.6;background:#f8fafc;padding:16px;border-radius:8px;border:1px solid #e2e8f0;word-break:break-all;"><strong>Detalle:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';

    if (str_contains($e->getMessage(), "Table") || str_contains($e->getMessage(), "doesn't exist")) {
        echo '<div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:16px;border-radius:8px;margin-top:16px;">';
        echo '<h4 style="margin:0 0 8px 0;">💡 Solución sugerida:</h4>';
        echo '<p style="margin:0;line-height:1.5;">Falta importar la base de datos en InfinityFree. Entra al botón morado <strong>phpMyAdmin</strong> en tu panel de InfinityFree e importa el archivo <code>database/menu_studio_full_dump.sql</code>.</p>';
        echo '</div>';
    }

    echo '<p style="color:#94a3b8;font-size:13px;margin-top:20px;">' . htmlspecialchars($e->getFile()) . ' : línea ' . $e->getLine() . '</p>';
    echo '</div>';
}
