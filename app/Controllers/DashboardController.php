<?php
/**
 * ============================================================================
 * Menu Studio — DashboardController
 * ============================================================================
 */

class DashboardController extends Controller
{
    /**
     * GET / — Página principal (redirige al listado de menús)
     */
    public function index(): void
    {
        $restaurantModel = new Restaurant();
        $menuModel = new Menu();
        $templateModel = new Template();

        $restaurantId = 1; // Demo
        $restaurant = $restaurantModel->findById($restaurantId);
        $menus = $menuModel->findByRestaurant($restaurantId);
        $templates = $templateModel->findAllOrdered();

        $this->view('dashboard/index', [
            'pageTitle'  => 'Dashboard',
            'restaurant' => $restaurant,
            'menus'      => $menus,
            'menuCount'  => count($menus),
            'templates'  => $templates,
            'showModal'  => false,
        ]);
    }
}
