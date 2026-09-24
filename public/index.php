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
$router->dispatch();
