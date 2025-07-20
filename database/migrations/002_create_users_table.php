<?php

use GuepardoSys\Core\BaseMigration;

class CreateUsersTable extends BaseMigration
{
    public function up()
    {
        $this->exec("CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            email VARCHAR(100),
            password VARCHAR(100)
        )");
    }

    public function down()
    {
        $this->exec("DROP TABLE IF EXISTS users");
    }
}
