<?php
/**
 * ============================================================================
 * Menu Studio — Router
 * ============================================================================
 * Enrutador minimalista que parsea la URI y despacha al Controller@method
 * correspondiente. Soporta rutas con parámetros dinámicos.
 *
 * Uso:
 *   $router = new Router();
 *   $router->get('/menus',          'MenuController@index');
 *   $router->get('/menus/{id}',     'MenuController@show');
 *   $router->post('/menus',         'MenuController@store');
 *   $router->put('/menus/{id}',     'MenuController@update');
 *   $router->delete('/menus/{id}',  'MenuController@delete');
 *   $router->dispatch();
 */

class Router
{
    /** @var array Tabla de rutas registradas, agrupadas por método HTTP */
    private array $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'DELETE' => [],
    ];

    /**
     * Registrar una ruta GET
     */
    public function get(string $path, string $handler): self
    {
        $this->addRoute('GET', $path, $handler);
        return $this;
    }

    /**
     * Registrar una ruta POST
     */
    public function post(string $path, string $handler): self
    {
        $this->addRoute('POST', $path, $handler);
        return $this;
    }

    /**
     * Registrar una ruta PUT
     */
    public function put(string $path, string $handler): self
    {
        $this->addRoute('PUT', $path, $handler);
        return $this;
    }

    /**
     * Registrar una ruta DELETE
     */
    public function delete(string $path, string $handler): self
    {
        $this->addRoute('DELETE', $path, $handler);
        return $this;
    }

    /**
     * Agregar ruta a la tabla interna
     *
     * @param string $method  Método HTTP
     * @param string $path    Patrón de ruta (ej: /menus/{id})
     * @param string $handler Controller@method
     */
    private function addRoute(string $method, string $path, string $handler): void
    {
        // Convertir {param} a regex con grupo nombrado
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[a-zA-Z0-9_-]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    /**
     * Despachar la petición actual al controller apropiado
     *
     * @throws \Exception Si no se encuentra una ruta coincidente
     */
    public function dispatch(): void
    {
        // Determinar método HTTP (soporta _method override para PUT/DELETE desde forms)
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        // Obtener la URI limpia
        $uri = $this->getUri();

        // Buscar coincidencia en la tabla de rutas
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route) {
                if (preg_match($route['pattern'], $uri, $matches)) {
                    // Extraer parámetros nombrados
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    $this->callHandler($route['handler'], $params);
                    return;
                }
            }
        }

        // 404 — No se encontró la ruta
        http_response_code(404);
        if ($this->isApiRequest($uri)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Route not found', 'uri' => $uri]);
        } else {
            echo '<h1>404 — Página no encontrada</h1>';
            echo '<p>La ruta <code>' . htmlspecialchars($uri) . '</code> no existe.</p>';
            echo '<a href="' . APP_URL . '/">Volver al inicio</a>';
        }
    }

    /**
     * Obtener la URI limpia (sin query string ni prefijo de app)
     */
    private function getUri(): string
    {
        $uri = $_GET['url'] ?? '';
        $uri = '/' . trim($uri, '/');

        return $uri;
    }

    /**
     * Ejecutar el handler (Controller@method)
     *
     * @param string $handler Formato: 'ControllerClass@methodName'
     * @param array  $params  Parámetros extraídos de la URI
     */
    private function callHandler(string $handler, array $params): void
    {
        [$controllerName, $methodName] = explode('@', $handler);

        $controllerFile = APP_PATH . '/Controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            throw new \RuntimeException("Controller file not found: {$controllerFile}");
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            throw new \RuntimeException("Controller class not found: {$controllerName}");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            throw new \RuntimeException("Method {$methodName} not found in {$controllerName}");
        }

        // Llamar al método con los parámetros de la URI
        call_user_func_array([$controller, $methodName], $params);
    }

    /**
     * Verificar si la petición es una API request
     */
    private function isApiRequest(string $uri): bool
    {
        return str_starts_with($uri, '/api/');
    }
}
