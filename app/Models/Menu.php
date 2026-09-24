<?php
/**
 * ============================================================================
 * Menu Studio — Menu Model (Corazón del Sistema)
 * ============================================================================
 * Gestiona los menús de restaurantes con sus configuraciones JSON
 * de estilos (style_config_json) y contenido (content_json).
 */

class Menu extends Model
{
    protected string $table = 'menus';
    protected array $jsonColumns = ['style_config_json', 'content_json'];

    /**
     * Obtener menús de un restaurante
     *
     * @param int    $restaurantId ID del restaurante
     * @param string $status       Filtro por estado (null = todos)
     * @return array
     */
    public function findByRestaurant(int $restaurantId, ?string $status = null): array
    {
        $sql = "SELECT m.*, t.title as template_title, t.thumbnail_url as template_thumbnail
                FROM `{$this->table}` m
                LEFT JOIN `templates` t ON m.template_id = t.id
                WHERE m.restaurant_id = :restaurant_id";
        $params = ['restaurant_id' => $restaurantId];

        if ($status !== null) {
            $sql .= " AND m.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY m.updated_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        return array_map([$this, 'decodeJsonColumns'], $results);
    }

    /**
     * Obtener un menú completo con datos de restaurante y plantilla
     *
     * @param int $id ID del menú
     * @return array|null
     */
    public function findFull(int $id): ?array
    {
        $sql = "SELECT m.*,
                       r.name as restaurant_name,
                       r.logo_url as restaurant_logo,
                       r.brand_colors as restaurant_brand_colors,
                       t.title as template_title,
                       t.dimensions as template_dimensions,
                       t.width_mm as template_width,
                       t.height_mm as template_height
                FROM `{$this->table}` m
                LEFT JOIN `restaurants` r ON m.restaurant_id = r.id
                LEFT JOIN `templates` t ON m.template_id = t.id
                WHERE m.id = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        if (!$result) return null;

        // Decodificar JSON del menú
        $result = $this->decodeJsonColumns($result);

        // Decodificar brand_colors del restaurante
        if (isset($result['restaurant_brand_colors']) && is_string($result['restaurant_brand_colors'])) {
            $result['restaurant_brand_colors'] = json_decode($result['restaurant_brand_colors'], true) ?? [];
        }

        return $result;
    }

    /**
     * Actualizar solo el contenido JSON del menú
     *
     * @param int   $id          ID del menú
     * @param array $contentJson Contenido estructurado
     * @return bool
     */
    public function updateContent(int $id, array $contentJson): bool
    {
        $sql = "UPDATE `{$this->table}`
                SET `content_json` = :content_json, `updated_at` = NOW()
                WHERE `id` = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'           => $id,
            'content_json' => json_encode($contentJson, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * Actualizar solo la configuración de estilos JSON
     *
     * @param int   $id        ID del menú
     * @param array $styleJson Configuración de estilos
     * @return bool
     */
    public function updateStyles(int $id, array $styleJson): bool
    {
        $sql = "UPDATE `{$this->table}`
                SET `style_config_json` = :style_config_json, `updated_at` = NOW()
                WHERE `id` = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'                => $id,
            'style_config_json' => json_encode($styleJson, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * Duplicar un menú existente
     *
     * @param int $id ID del menú original
     * @return int ID del menú duplicado
     */
    public function duplicate(int $id): int
    {
        $original = $this->findById($id);
        if (!$original) {
            throw new \RuntimeException("Menu not found: {$id}");
        }

        return $this->create([
            'restaurant_id'    => $original['restaurant_id'],
            'template_id'      => $original['template_id'],
            'title'            => $original['title'] . ' (Copia)',
            'style_config_json' => $original['style_config_json'],
            'content_json'     => $original['content_json'],
            'status'           => 'draft',
        ]);
    }

    /**
     * Cambiar estado del menú
     *
     * @param int    $id     ID del menú
     * @param string $status Nuevo estado ('draft'|'published'|'archived')
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool
    {
        $validStatuses = ['draft', 'published', 'archived'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        return $this->update($id, ['status' => $status]);
    }
}
