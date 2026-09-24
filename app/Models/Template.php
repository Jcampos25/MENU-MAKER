<?php
/**
 * ============================================================================
 * Menu Studio — Template Model
 * ============================================================================
 */

class Template extends Model
{
    protected string $table = 'templates';
    protected array $jsonColumns = ['layout_json', 'style_defaults_json'];

    /**
     * Obtener plantillas por categoría
     */
    public function findByCategory(string $category): array
    {
        return $this->findAll(['category' => $category], 'sort_order', 'ASC');
    }

    /**
     * Obtener plantillas por dimensión
     */
    public function findByDimension(string $dimension): array
    {
        return $this->findAll(['dimensions' => $dimension], 'sort_order', 'ASC');
    }

    /**
     * Obtener solo plantillas gratuitas
     */
    public function findFree(): array
    {
        return $this->findAll(['is_premium' => 0], 'sort_order', 'ASC');
    }

    /**
     * Obtener todas las plantillas ordenadas
     */
    public function findAllOrdered(): array
    {
        return $this->findAll([], 'sort_order', 'ASC', 200);
    }
}
