<?php
/**
 * ============================================================================
 * Menu Studio — ApiController
 * ============================================================================
 * API JSON para el editor visual. Todas las respuestas son JSON.
 * El editor se comunica con este controller vía AJAX (fetch).
 */

class ApiController extends Controller
{
    private Menu $menuModel;
    private Template $templateModel;
    private CustomFont $fontModel;
    private MediaAsset $mediaModel;
    private Restaurant $restaurantModel;

    public function __construct()
    {
        Response::corsHeaders();
        $this->menuModel       = new Menu();
        $this->templateModel   = new Template();
        $this->fontModel       = new CustomFont();
        $this->mediaModel      = new MediaAsset();
        $this->restaurantModel = new Restaurant();
    }

    // ─── MENÚS ─────────────────────────────────────────────────────────────

    /**
     * GET /api/menus/{id} — Obtener menú completo con estilos y contenido
     */
    public function getMenu(string $id): void
    {
        $menu = $this->menuModel->findFull((int) $id);
        if (!$menu) {
            Response::error('Menu not found', 404);
        }
        Response::success($menu);
    }

    /**
     * PUT /api/menus/{id}/content — Guardar contenido JSON del menú
     */
    public function updateContent(string $id): void
    {
        $data = $this->getRequestData();

        if (!isset($data['content_json'])) {
            Response::error('content_json is required', 422);
        }

        $contentJson = is_string($data['content_json'])
            ? json_decode($data['content_json'], true)
            : $data['content_json'];

        if (!is_array($contentJson)) {
            Response::error('Invalid content_json format', 422);
        }

        $success = $this->menuModel->updateContent((int) $id, $contentJson);

        if ($success) {
            Response::success(null, 'Content saved successfully');
        } else {
            Response::error('Failed to save content', 500);
        }
    }

    /**
     * PUT /api/menus/{id}/styles — Guardar configuración de estilos JSON
     */
    public function updateStyles(string $id): void
    {
        $data = $this->getRequestData();

        if (!isset($data['style_config_json'])) {
            Response::error('style_config_json is required', 422);
        }

        $styleJson = is_string($data['style_config_json'])
            ? json_decode($data['style_config_json'], true)
            : $data['style_config_json'];

        if (!is_array($styleJson)) {
            Response::error('Invalid style_config_json format', 422);
        }

        $success = $this->menuModel->updateStyles((int) $id, $styleJson);

        if ($success) {
            Response::success(null, 'Styles saved successfully');
        } else {
            Response::error('Failed to save styles', 500);
        }
    }

    /**
     * PUT /api/menus/{id} — Actualizar menú completo (título, estilos, contenido)
     */
    public function updateMenu(string $id): void
    {
        $data = $this->getRequestData();
        $menuId = (int) $id;

        // Actualizar campos básicos
        $updateData = [];
        if (isset($data['title'])) {
            $updateData['title'] = trim($data['title']);
        }
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        if (!empty($updateData)) {
            $this->menuModel->update($menuId, $updateData);
        }

        // Actualizar estilos si se enviaron
        if (isset($data['style_config_json'])) {
            $styleJson = is_string($data['style_config_json'])
                ? json_decode($data['style_config_json'], true)
                : $data['style_config_json'];
            if (is_array($styleJson)) {
                $this->menuModel->updateStyles($menuId, $styleJson);
            }
        }

        // Actualizar contenido si se envió
        if (isset($data['content_json'])) {
            $contentJson = is_string($data['content_json'])
                ? json_decode($data['content_json'], true)
                : $data['content_json'];
            if (is_array($contentJson)) {
                $this->menuModel->updateContent($menuId, $contentJson);
            }
        }

        // Si se envió nuevo nombre de restaurante o logo, actualizar también la tabla restaurants
        if (isset($data['restaurant_name']) || array_key_exists('restaurant_logo', $data)) {
            $menu = $this->menuModel->findById($menuId);
            if ($menu && !empty($menu['restaurant_id'])) {
                $restaurantUpdates = [];
                if (isset($data['restaurant_name']) && trim($data['restaurant_name']) !== '') {
                    $restaurantUpdates['name'] = trim($data['restaurant_name']);
                }
                if (array_key_exists('restaurant_logo', $data)) {
                    $restaurantUpdates['logo_url'] = !empty($data['restaurant_logo']) ? trim($data['restaurant_logo']) : null;
                }
                if (!empty($restaurantUpdates)) {
                    $this->restaurantModel->update((int) $menu['restaurant_id'], $restaurantUpdates);
                }
            }
        }

        Response::success(null, 'Menu updated successfully');
    }

    // ─── PLANTILLAS ────────────────────────────────────────────────────────

    /**
     * GET /api/templates — Listar todas las plantillas
     */
    public function getTemplates(): void
    {
        $templates = $this->templateModel->findAllOrdered();
        Response::success($templates);
    }

    /**
     * GET /api/templates/{id} — Obtener una plantilla específica
     */
    public function getTemplate(string $id): void
    {
        $template = $this->templateModel->findById((int) $id);
        if (!$template) {
            Response::error('Template not found', 404);
        }
        Response::success($template);
    }

    // ─── FUENTES ───────────────────────────────────────────────────────────

    /**
     * GET /api/fonts — Listar fuentes disponibles (custom + Google Fonts estáticas)
     */
    public function getFonts(): void
    {
        $restaurantId = 1; // Demo
        $customFonts = $this->fontModel->findForRestaurant($restaurantId);

        // Lista estática de Google Fonts populares para restaurantes
        $googleFonts = [
            ['family' => 'Playfair Display', 'category' => 'serif',       'variants' => ['400','500','600','700','800','900']],
            ['family' => 'Montserrat',       'category' => 'sans-serif',  'variants' => ['100','200','300','400','500','600','700','800','900']],
            ['family' => 'Lato',             'category' => 'sans-serif',  'variants' => ['100','300','400','700','900']],
            ['family' => 'Inter',            'category' => 'sans-serif',  'variants' => ['100','200','300','400','500','600','700','800','900']],
            ['family' => 'Poppins',          'category' => 'sans-serif',  'variants' => ['100','200','300','400','500','600','700','800','900']],
            ['family' => 'Roboto',           'category' => 'sans-serif',  'variants' => ['100','300','400','500','700','900']],
            ['family' => 'Open Sans',        'category' => 'sans-serif',  'variants' => ['300','400','500','600','700','800']],
            ['family' => 'Bebas Neue',       'category' => 'display',     'variants' => ['400']],
            ['family' => 'Abril Fatface',    'category' => 'display',     'variants' => ['400']],
            ['family' => 'Cormorant Garamond','category'=> 'serif',       'variants' => ['300','400','500','600','700']],
            ['family' => 'Merriweather',     'category' => 'serif',       'variants' => ['300','400','700','900']],
            ['family' => 'Oswald',           'category' => 'sans-serif',  'variants' => ['200','300','400','500','600','700']],
            ['family' => 'Raleway',          'category' => 'sans-serif',  'variants' => ['100','200','300','400','500','600','700','800','900']],
            ['family' => 'Great Vibes',      'category' => 'handwriting', 'variants' => ['400']],
            ['family' => 'Dancing Script',   'category' => 'handwriting', 'variants' => ['400','500','600','700']],
            ['family' => 'Outfit',           'category' => 'sans-serif',  'variants' => ['100','200','300','400','500','600','700','800','900']],
            ['family' => 'DM Serif Display', 'category' => 'serif',       'variants' => ['400']],
            ['family' => 'Josefin Sans',     'category' => 'sans-serif',  'variants' => ['100','200','300','400','500','600','700']],
            ['family' => 'Crimson Text',     'category' => 'serif',       'variants' => ['400','600','700']],
            ['family' => 'Libre Baskerville','category' => 'serif',       'variants' => ['400','700']],
        ];

        Response::success([
            'custom'      => $customFonts,
            'googleFonts' => $googleFonts,
        ]);
    }

    // ─── MEDIA ─────────────────────────────────────────────────────────────

    /**
     * POST /api/media/upload — Subir un archivo multimedia
     */
    public function uploadMedia(): void
    {
        $file = Request::file('file');
        if (!$file) {
            Response::error('No file uploaded', 422);
        }

        $type = $_POST['type'] ?? 'dish_photo';
        $restaurantId = (int) ($_POST['restaurant_id'] ?? 1);

        try {
            $assetId = $this->mediaModel->upload($restaurantId, $type, $file);
            $asset = $this->mediaModel->findById($assetId);
            Response::success($asset, 'File uploaded successfully', 201);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * GET /api/media/{restaurantId} — Obtener assets multimedia
     */
    public function getMedia(string $restaurantId): void
    {
        $type = Request::get('type');
        $assets = $this->mediaModel->findByRestaurantAndType((int) $restaurantId, $type);
        Response::success($assets);
    }
}
