<?php

use GuepardoSys\Core\BaseMigration;

class CreateCategoriesTable extends BaseMigration
{
    public function up()
    {
        $this->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS categories (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(120) NOT NULL UNIQUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        SQL);
    }

    public function down()
    {
        $this->exec("DROP TABLE IF EXISTS categories");
    }
}
