<?php
/**
 * ============================================================================
 * Menu Studio — TemplateController
 * ============================================================================
 */

class TemplateController extends Controller
{
    private Template $templateModel;

    public function __construct()
    {
        $this->templateModel = new Template();
    }

    /**
     * GET /templates — Galería de plantillas
     */
    public function index(): void
    {
        $templates = $this->templateModel->findAllOrdered();

        // Agrupar por categoría
        $grouped = [];
        foreach ($templates as $template) {
            $grouped[$template['category']][] = $template;
        }

        $this->view('dashboard/index', [
            'pageTitle'         => 'Plantillas',
            'templates'         => $templates,
            'groupedTemplates'  => $grouped,
            'restaurant'        => (new Restaurant())->findById(1),
            'menus'             => (new Menu())->findByRestaurant(1),
            'menuCount'         => (new Menu())->count(['restaurant_id' => 1]),
        ]);
    }
}
