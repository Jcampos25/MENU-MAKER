<?php
/**
 * ============================================================================
 * Menu Studio — Base Controller
 * ============================================================================
 * Clase base para todos los controllers. Provee helpers para renderizar
 * vistas, enviar respuestas JSON y redireccionar.
 */

class Controller
{
    /**
     * Renderizar una vista PHP con datos
     *
     * @param string $view   Ruta relativa a Views/ (ej: 'dashboard/index')
     * @param array  $data   Variables a extraer en la vista
     * @param string|null $layout Layout a usar (null para sin layout)
     */
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        // Extraer datos como variables locales para la vista
        extract($data);

        // Ruta al archivo de vista
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }

        if ($layout !== null) {
            // Capturar el contenido de la vista en un buffer
            ob_start();
            require $viewFile;
            $content = ob_get_clean();

            // Renderizar dentro del layout
            $layoutFile = VIEWS_PATH . '/' . $layout . '.php';
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("Layout not found: {$layoutFile}");
            }
            require $layoutFile;
        } else {
            require $viewFile;
        }
    }

    /**
     * Enviar respuesta JSON
     *
     * @param mixed $data       Datos a serializar
     * @param int   $statusCode Código HTTP
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Redireccionar a otra URL
     *
     * @param string $url URL destino
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . APP_URL . $url);
        exit;
    }

    /**
     * Obtener datos del body de la petición (JSON o form data)
     *
     * @return array Datos parseados
     */
    protected function getRequestData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            return is_array($data) ? $data : [];
        }

        // Para PUT/PATCH enviados como form data
        if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
            parse_str(file_get_contents('php://input'), $data);
            return $data;
        }

        return $_POST;
    }

    /**
     * Validar campos requeridos
     *
     * @param array $data   Datos a validar
     * @param array $fields Campos requeridos
     * @return array Lista de errores (vacía si todo OK)
     */
    protected function validateRequired(array $data, array $fields): array
    {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $errors[] = "El campo '{$field}' es obligatorio.";
            }
        }
        return $errors;
    }
}
