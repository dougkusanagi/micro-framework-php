<?php 
require_once "C:\Users\dl_ag\Herd\micro-framework-php\src\Core\MigrationInterface.php";
require_once "C:\Users\dl_ag\Herd\micro-framework-php\src\Core\Database.php";
class TestMigrationA implements \GuepardoSys\Core\MigrationInterface {
    protected $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }
    public function up() { $this->pdo->exec('CREATE TABLE test_a (id INTEGER PRIMARY KEY)'); }
    public function down() { $this->pdo->exec('DROP TABLE IF EXISTS test_a'); }
}