<?php
/**
 * ============================================================================
 * Menu Studio — MediaAsset Model
 * ============================================================================
 */

class MediaAsset extends Model
{
    protected string $table = 'media_assets';

    /**
     * Obtener assets de un restaurante por tipo
     */
    public function findByRestaurantAndType(int $restaurantId, ?string $type = null): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `restaurant_id` = :restaurant_id";
        $params = ['restaurant_id' => $restaurantId];

        if ($type !== null) {
            $sql .= " AND `type` = :type";
            $params['type'] = $type;
        }

        $sql .= " ORDER BY `created_at` DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Subir y registrar un archivo
     *
     * @param int    $restaurantId ID del restaurante
     * @param string $type         Tipo de asset
     * @param array  $file         Datos de $_FILES
     * @return int ID del asset creado
     * @throws \RuntimeException Si la subida falla
     */
    public function upload(int $restaurantId, string $type, array $file): int
    {
        // Validar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException("Upload error code: {$file['error']}");
        }

        // Validar tamaño
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            throw new \RuntimeException("File too large. Max: " . (MAX_UPLOAD_SIZE / 1024 / 1024) . "MB");
        }

        // Validar tipo MIME
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
            throw new \RuntimeException("Invalid file type: {$mime}");
        }

        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName  = uniqid($type . '_', true) . '.' . strtolower($extension);

        // Directorio destino
        $typeDir = match($type) {
            'logo'       => 'logos',
            'background' => 'backgrounds',
            'dish_photo' => 'dishes',
            'icon'       => 'icons',
            'texture'    => 'textures',
            default      => 'misc',
        };

        $uploadDir = UPLOAD_PATH . '/' . $typeDir;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir . '/' . $fileName;

        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new \RuntimeException("Failed to move uploaded file");
        }

        // Obtener dimensiones de imagen
        $imageInfo = getimagesize($filePath);
        $width  = $imageInfo[0] ?? null;
        $height = $imageInfo[1] ?? null;

        // Registrar en BD
        return $this->create([
            'restaurant_id' => $restaurantId,
            'type'          => $type,
            'file_name'     => $file['name'],
            'file_path'     => UPLOADS_URL . '/' . $typeDir . '/' . $fileName,
            'mime_type'     => $mime,
            'file_size'     => $file['size'],
            'width'         => $width,
            'height'        => $height,
        ]);
    }
}
