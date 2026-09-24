<?php
/**
 * ============================================================================
 * Menu Studio — Base Model
 * ============================================================================
 * Clase base para todos los modelos. Provee operaciones CRUD genéricas
 * utilizando PDO prepared statements para seguridad contra SQL Injection.
 */

class Model
{
    /** @var PDO Conexión a la base de datos */
    protected PDO $db;

    /** @var string Nombre de la tabla (debe ser definido en cada modelo hijo) */
    protected string $table = '';

    /** @var string Clave primaria */
    protected string $primaryKey = 'id';

    /** @var array Columnas que contienen JSON (se decodifican automáticamente) */
    protected array $jsonColumns = [];

    /**
     * Constructor — inyecta la conexión PDO
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Buscar un registro por su ID
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ? $this->decodeJsonColumns($result) : null;
    }

    /**
     * Obtener todos los registros (con opción de filtro simple)
     *
     * @param array  $where   Condiciones WHERE ['column' => 'value']
     * @param string $orderBy Columna de ordenamiento
     * @param string $order   Dirección (ASC|DESC)
     * @param int    $limit   Límite de resultados
     * @return array
     */
    public function findAll(
        array $where = [],
        string $orderBy = 'id',
        string $order = 'DESC',
        int $limit = 100
    ): array {
        $sql = "SELECT * FROM `{$this->table}`";
        $params = [];

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "`{$column}` = :{$column}";
                $params[$column] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        // Validar orderBy para prevenir inyección
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY `{$orderBy}` {$order} LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        return array_map([$this, 'decodeJsonColumns'], $results);
    }

    /**
     * Crear un nuevo registro
     *
     * @param array $data Datos del registro ['column' => 'value']
     * @return int ID del registro creado
     */
    public function create(array $data): int
    {
        $data = $this->encodeJsonColumns($data);

        $columns = implode('`, `', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO `{$this->table}` (`{$columns}`) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar un registro existente
     *
     * @param int   $id   ID del registro
     * @param array $data Datos a actualizar ['column' => 'value']
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $data = $this->encodeJsonColumns($data);

        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "`{$column}` = :{$column}";
        }
        $setClause = implode(', ', $setParts);

        $sql = "UPDATE `{$this->table}` SET {$setClause} WHERE `{$this->primaryKey}` = :_pk_id";
        $data['_pk_id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Eliminar un registro
     *
     * @param int $id ID del registro
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Contar registros
     *
     * @param array $where Condiciones opcionales
     * @return int
     */
    public function count(array $where = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        $params = [];

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "`{$column}` = :{$column}";
                $params[$column] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Ejecutar una consulta SQL personalizada
     *
     * @param string $sql    Consulta SQL con placeholders
     * @param array  $params Parámetros para la consulta
     * @return array Resultados
     */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Decodificar columnas JSON de un registro
     */
    protected function decodeJsonColumns(array $row): array
    {
        foreach ($this->jsonColumns as $col) {
            if (isset($row[$col]) && is_string($row[$col])) {
                $row[$col] = json_decode($row[$col], true) ?? [];
            }
        }
        return $row;
    }

    /**
     * Codificar columnas JSON antes de insertar/actualizar
     */
    protected function encodeJsonColumns(array $data): array
    {
        foreach ($this->jsonColumns as $col) {
            if (isset($data[$col]) && is_array($data[$col])) {
                $data[$col] = json_encode($data[$col], JSON_UNESCAPED_UNICODE);
            }
        }
        return $data;
    }
}
