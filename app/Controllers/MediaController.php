<?php
/**
 * ============================================================================
 * Menu Studio — MediaController
 * ============================================================================
 */

class MediaController extends Controller
{
    /**
     * POST /media/upload — Subir archivo desde formulario web
     */
    public function upload(): void
    {
        $file = Request::file('file');
        if (!$file) {
            $_SESSION['errors'] = ['No se seleccionó ningún archivo.'];
            $this->redirect('/menus');
            return;
        }

        $type = Request::post('type', 'dish_photo');
        $restaurantId = (int) Request::post('restaurant_id', 1);

        try {
            $mediaModel = new MediaAsset();
            $mediaModel->upload($restaurantId, $type, $file);
            $_SESSION['success'] = 'Archivo subido exitosamente.';
        } catch (\RuntimeException $e) {
            $_SESSION['errors'] = [$e->getMessage()];
        }

        $this->redirect('/menus');
    }
}
