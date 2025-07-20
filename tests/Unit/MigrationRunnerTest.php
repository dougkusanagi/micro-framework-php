<?php

require_once __DIR__ . '/../../src/Core/MigrationInterface.php';
require_once __DIR__ . '/../../src/Core/Database.php';

use GuepardoSys\Core\MigrationInterface;
use GuepardoSys\Core\Database;

$testMigrationsPath = __DIR__ . '/../../temp_migrations_runner';
if (!is_dir($testMigrationsPath)) {
    mkdir($testMigrationsPath, 0755, true);
}
$migrationInterfacePath = realpath(__DIR__ . '/../../src/Core/MigrationInterface.php');
$databasePath = realpath(__DIR__ . '/../../src/Core/Database.php');
file_put_contents(
    $testMigrationsPath . '/TestMigrationA.php',
    '<?php ' . PHP_EOL .
        'require_once "' . $migrationInterfacePath . '";' . PHP_EOL .
        'require_once "' . $databasePath . '";' . PHP_EOL .
        'class TestMigrationA implements \\GuepardoSys\\Core\\MigrationInterface {' . PHP_EOL .
        '    protected $pdo;' . PHP_EOL .
        '    public function __construct($pdo) { $this->pdo = $pdo; }' . PHP_EOL .
        '    public function up() { $this->pdo->exec(\'CREATE TABLE test_a (id INTEGER PRIMARY KEY)\'); }' . PHP_EOL .
        '    public function down() { $this->pdo->exec(\'DROP TABLE IF EXISTS test_a\'); }' . PHP_EOL .
        '}'
);
require_once $testMigrationsPath . '/TestMigrationA.php';

describe('MigrationRunner', function () {
    beforeEach(function () {
        $this->pdo = new PDO('sqlite::memory:');
        $this->migrationsPath = __DIR__ . '/../../temp_migrations_runner';
    });

    afterEach(function () {
        $files = glob($this->migrationsPath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->migrationsPath);
    });

    it('runs up migration', function () {
        // class TestMigrationA is now defined before this test runs
        $migration = new TestMigrationA($this->pdo);
        $migration->up();
        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='test_a'");
        $result = $stmt->fetch();
        expect($result)->not->toBeFalse();
    });

    it('runs down migration', function () {
        // class TestMigrationA is now defined before this test runs
        $migration = new TestMigrationA($this->pdo);
        $migration->up();
        $migration->down();
        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='test_a'");
        $result = $stmt->fetch();
        expect($result)->toBeFalse();
    });
});
