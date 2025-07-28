<?php

use GuepardoSys\Core\BaseMigration;

class CreateMigrationsTable extends BaseMigration
{
    public function up()
    {
        $this->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS migrations (
                id INT PRIMARY KEY AUTO_INCREMENT,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL DEFAULT 1,
                migrated_at DATETIME
            )
        SQL);
    }

    public function down()
    {
        $this->exec(<<<SQL
            DROP TABLE IF EXISTS migrations
        SQL);
    }
}
