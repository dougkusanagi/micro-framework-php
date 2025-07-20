<?php

namespace GuepardoSys\Core;

use PDO;

abstract class BaseSeeder implements SeederInterface
{
    protected PDO $pdo;
    protected ?string $seedsPath;

    public function __construct(?PDO $pdo = null, ?string $seedsPath = null)
    {
        $this->pdo = $pdo ?: Database::getConnection();
        $this->seedsPath = $seedsPath;
    }

    protected function exec(string $sql): void
    {
        $this->pdo->exec($sql);
    }

    protected function call(string $seederClass): void
    {
        if ($this->seedsPath && !class_exists($seederClass)) {
            $seederFile = $this->seedsPath . '/' . $seederClass . '.php';
            if (file_exists($seederFile)) {
                require_once $seederFile;
            }
        }

        $seeder = new $seederClass($this->pdo, $this->seedsPath);
        $seeder->run();
    }

    // Child classes must implement this
    abstract public function run();
}
