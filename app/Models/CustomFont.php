<?php
/**
 * ============================================================================
 * Menu Studio — CustomFont Model
 * ============================================================================
 */

class CustomFont extends Model
{
    protected string $table = 'custom_fonts';

    /**
     * Obtener fuentes de un restaurante (+ fuentes globales del sistema)
     */
    public function findForRestaurant(int $restaurantId): array
    {
        $sql = "SELECT * FROM `{$this->table}`
                WHERE `restaurant_id` = :restaurant_id OR `restaurant_id` IS NULL
                ORDER BY `font_family` ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['restaurant_id' => $restaurantId]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar por familia de fuente
     */
    public function findByFamily(string $fontFamily): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `font_family` = :font_family";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['font_family' => $fontFamily]);
        return $stmt->fetchAll();
    }

    /**
     * Subir una fuente personalizada
     *
     * @param int|null $restaurantId NULL para fuente global
     * @param array    $file         Datos de $_FILES
     * @param string   $fontName     Nombre visible
     * @param string   $fontFamily   CSS font-family
     * @param string   $fontWeight   Peso (400, 700, etc.)
     * @param string   $fontStyle    Estilo (normal, italic)
     * @return int
     */
    public function uploadFont(
        ?int $restaurantId,
        array $file,
        string $fontName,
        string $fontFamily,
        string $fontWeight = '400',
        string $fontStyle = 'normal'
    ): int {
        // Validar tipo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $validFormats = ['woff2', 'woff', 'ttf', 'otf'];
        if (!in_array($extension, $validFormats)) {
            throw new \RuntimeException("Invalid font format: {$extension}");
        }

        // Generar nombre único
        $fileName = uniqid('font_', true) . '.' . $extension;
        $uploadDir = UPLOAD_PATH . '/fonts';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new \RuntimeException("Failed to move uploaded font file");
        }

        return $this->create([
            'restaurant_id'  => $restaurantId,
            'font_name'      => $fontName,
            'font_family'    => $fontFamily,
            'font_file_path' => UPLOADS_URL . '/fonts/' . $fileName,
            'font_format'    => $extension,
            'font_weight'    => $fontWeight,
            'font_style'     => $fontStyle,
        ]);
    }
}
