<?php

namespace GuepardoSys\Core;

use PDO;

class SeederRunner
{
    private string $seedsPath;
    private PDO $pdo;

    public function __construct(string $seedsPath, PDO $pdo)
    {
        $this->seedsPath = $seedsPath;
        $this->pdo = $pdo;
    }

    public function run(): void
    {
        $this->runSeeder('DatabaseSeeder');
    }

    private function runSeeder(string $seederName): void
    {
        $file = $this->seedsPath . '/' . $seederName . '.php';

        if (!file_exists($file)) {
            echo "Seeder not found: {$seederName}" . PHP_EOL;
            return;
        }

        require_once $file;

        $seeder = new $seederName($this->pdo, $this->seedsPath);
        $seeder->run();

        echo "✓ Seeded: {$seederName}" . PHP_EOL;
    }
}