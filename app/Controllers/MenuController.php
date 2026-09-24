<?php
/**
 * ============================================================================
 * Menu Studio — MenuController
 * ============================================================================
 * Controlador principal para la gestión CRUD de menús.
 * Maneja las vistas web (dashboard, editor) y las operaciones de datos.
 */

class MenuController extends Controller
{
    private Menu $menuModel;
    private Template $templateModel;
    private Restaurant $restaurantModel;

    public function __construct()
    {
        $this->menuModel       = new Menu();
        $this->templateModel   = new Template();
        $this->restaurantModel = new Restaurant();
    }

    /**
     * GET /menus — Listar todos los menús del restaurante activo
     */
    public function index(): void
    {
        // Por ahora usamos el restaurante demo (id=1)
        $restaurantId = 1;
        $restaurant = $this->restaurantModel->findById($restaurantId);
        $menus = $this->menuModel->findByRestaurant($restaurantId);

        $this->view('dashboard/index', [
            'pageTitle'    => 'Mis Menús',
            'restaurant'   => $restaurant,
            'menus'        => $menus,
            'menuCount'    => count($menus),
        ]);
    }

    /**
     * GET /menus/create — Formulario de creación (selección de plantilla)
     */
    public function create(): void
    {
        $templates = $this->templateModel->findAllOrdered();

        $this->view('dashboard/index', [
            'pageTitle'    => 'Crear Nuevo Menú',
            'templates'    => $templates,
            'showModal'    => true,
            'restaurant'   => $this->restaurantModel->findById(1),
            'menus'        => $this->menuModel->findByRestaurant(1),
            'menuCount'    => $this->menuModel->count(['restaurant_id' => 1]),
        ]);
    }

    /**
     * POST /menus — Crear nuevo menú a partir de una plantilla
     */
    public function store(): void
    {
        $data = $this->getRequestData();

        $errors = $this->validateRequired($data, ['title', 'template_id']);
        if (!empty($errors)) {
            // Redireccionar con errores (simplificado para MVP)
            $_SESSION['errors'] = $errors;
            $this->redirect('/menus/create');
            return;
        }

        $restaurantId = 1; // Demo
        $templateId = (int) $data['template_id'];

        // Cargar plantilla para obtener estilos por defecto
        $template = $this->templateModel->findById($templateId);
        if (!$template) {
            $_SESSION['errors'] = ['Plantilla no encontrada.'];
            $this->redirect('/menus/create');
            return;
        }

        // Construir style_config inicial desde la plantilla
        $styleConfig = $template['style_defaults_json'] ?? [];
        $styleConfig['dimensions'] = [
            'width'  => (float) $template['width_mm'],
            'height' => (float) $template['height_mm'],
            'unit'   => 'mm',
            'format' => $template['dimensions'],
        ];

        // Contenido inicial: usar el de la plantilla si existe, o estructura base por defecto
        $contentJson = $template['layout_json']['default_content'] ?? [
            'sections' => [
                [
                    'id'        => 'sec_' . uniqid(),
                    'title'     => 'Entradas',
                    'icon'      => 'appetizer',
                    'sortOrder' => 0,
                    'items'     => [],
                ],
                [
                    'id'        => 'sec_' . uniqid(),
                    'title'     => 'Platos Fuertes',
                    'icon'      => 'main_course',
                    'sortOrder' => 1,
                    'items'     => [],
                ],
            ],
        ];

        // Crear menú
        $menuId = $this->menuModel->create([
            'restaurant_id'     => $restaurantId,
            'template_id'       => $templateId,
            'title'             => trim($data['title']),
            'style_config_json' => $styleConfig,
            'content_json'      => $contentJson,
            'status'            => 'draft',
        ]);

        $_SESSION['success'] = 'Menú creado exitosamente.';
        $this->redirect("/menus/{$menuId}/edit");
    }

    /**
     * GET /menus/{id}/edit — Abrir el editor visual
     */
    public function edit(string $id): void
    {
        $menuId = (int) $id;
        $menu = $this->menuModel->findFull($menuId);

        if (!$menu) {
            $_SESSION['errors'] = ['Menú no encontrado.'];
            $this->redirect('/menus');
            return;
        }

        // Cargar fuentes personalizadas disponibles
        $fontModel = new CustomFont();
        $customFonts = $fontModel->findForRestaurant($menu['restaurant_id']);

        // Cargar assets multimedia
        $mediaModel = new MediaAsset();
        $mediaAssets = $mediaModel->findByRestaurantAndType($menu['restaurant_id']);

        // Renderizar editor sin layout (layout propio del editor)
        $this->view('editor/index', [
            'pageTitle'   => 'Editando: ' . $menu['title'],
            'menu'        => $menu,
            'customFonts' => $customFonts,
            'mediaAssets' => $mediaAssets,
        ], null); // Sin layout wrapper — el editor tiene su propio HTML completo
    }

    /**
     * POST /menus/{id} — Actualizar menú (desde formulario)
     */
    public function update(string $id): void
    {
        $menuId = (int) $id;
        $data = $this->getRequestData();

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

        $_SESSION['success'] = 'Menú actualizado.';
        $this->redirect('/menus');
    }

    /**
     * POST /menus/{id}/delete — Eliminar menú
     */
    public function destroy(string $id): void
    {
        $menuId = (int) $id;
        $this->menuModel->delete($menuId);

        $_SESSION['success'] = 'Menú eliminado.';
        $this->redirect('/menus');
    }

    /**
     * POST /menus/{id}/duplicate — Duplicar menú
     */
    public function duplicate(string $id): void
    {
        $menuId = (int) $id;

        try {
            $newId = $this->menuModel->duplicate($menuId);
            $_SESSION['success'] = 'Menú duplicado exitosamente.';
            $this->redirect("/menus/{$newId}/edit");
        } catch (\RuntimeException $e) {
            $_SESSION['errors'] = [$e->getMessage()];
            $this->redirect('/menus');
        }
    }
}
