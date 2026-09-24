<?php
/**
 * ============================================================================
 * Menu Studio — Restaurant Model
 * ============================================================================
 */

class Restaurant extends Model
{
    protected string $table = 'restaurants';
    protected array $jsonColumns = ['brand_colors'];

    /**
     * Obtener restaurantes de un usuario
     */
    public function findByUser(int $userId): array
    {
        return $this->findAll(['user_id' => $userId], 'created_at', 'DESC');
    }

    /**
     * Buscar por slug
     */
    public function findBySlug(string $slug): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `slug` = :slug LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $result = $stmt->fetch();
        return $result ? $this->decodeJsonColumns($result) : null;
    }
}
