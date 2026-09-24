<?php
/**
 * ============================================================================
 * Menu Studio — User Model
 * ============================================================================
 */

class User extends Model
{
    protected string $table = 'users';

    /**
     * Buscar usuario por email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `email` = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Verificar contraseña
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Hashear contraseña para almacenamiento
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
