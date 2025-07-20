<?php

require_once __DIR__ . '/../../src/Core/BaseSeeder.php';
require_once __DIR__ . '/../../src/Core/SeederInterface.php';

use GuepardoSys\Core\BaseSeeder;
use GuepardoSys\Core\SeederRunner;

class TestSeederA extends BaseSeeder
{
    public function run()
    {
        $this->exec('CREATE TABLE seeded_a (id INTEGER PRIMARY KEY)');
    }
}

class TestSeederB extends BaseSeeder
{
    public function run()
    {
        $this->call(TestSeederA::class);
        $this->exec('CREATE TABLE seeded_b (id INTEGER PRIMARY KEY)');
    }
}

describe('SeederRunner', function () {
    beforeEach(function () {
        $this->pdo = new PDO('sqlite::memory:');
        $this->seedsPath = __DIR__ . '/../../temp_seeds_runner';
        if (!is_dir($this->seedsPath)) {
            mkdir($this->seedsPath, 0755, true);
        }
        $baseSeederPath = realpath(__DIR__ . '/../../src/Core/BaseSeeder.php');
        // Write test seeders to temp dir
        file_put_contents($this->seedsPath . '/TestSeederA.php', '<?php ' . PHP_EOL . 'require_once "' . $baseSeederPath . '"; class TestSeederA extends \\GuepardoSys\\Core\\BaseSeeder { public function run() { $this->exec(\'CREATE TABLE seeded_a (id INTEGER PRIMARY KEY)\'); } }');
        file_put_contents($this->seedsPath . '/TestSeederB.php', '<?php ' . PHP_EOL . 'require_once "' . $baseSeederPath . '"; class TestSeederB extends \\GuepardoSys\\Core\\BaseSeeder { public function run() { $this->call(\\TestSeederA::class); $this->exec(\'CREATE TABLE seeded_b (id INTEGER PRIMARY KEY)\'); } }');
    });

    afterEach(function () {
        $files = glob($this->seedsPath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->seedsPath);
    });

    it('runs seeders using exec', function () {
        require_once $this->seedsPath . '/TestSeederA.php';
        $seeder = new TestSeederA($this->pdo);
        $seeder->run();
        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='seeded_a'");
        $result = $stmt->fetch();
        expect($result)->not->toBeFalse();
    });

    it('runs seeders using call', function () {
        require_once $this->seedsPath . '/TestSeederA.php';
        require_once $this->seedsPath . '/TestSeederB.php';
        $seeder = new TestSeederB($this->pdo);
        $seeder->run();
        $stmtA = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='seeded_a'");
        $stmtB = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='seeded_b'");
        expect($stmtA->fetch())->not->toBeFalse();
        expect($stmtB->fetch())->not->toBeFalse();
    });
});
