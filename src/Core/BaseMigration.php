<?php

namespace GuepardoSys\Core;

use PDO;

abstract class BaseMigration implements MigrationInterface
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: Database::getConnection();
    }

    protected function exec(string $sql): void
    {
        $this->pdo->exec($sql);
    }

    // Child classes must implement these
    abstract public function up();
    abstract public function down();
}
