<?php

use GuepardoSys\Core\BaseSeeder;

class UsersSeeder extends BaseSeeder
{
    public function run()
    {
        $password = bcrypt('password');
        $this->exec(<<<SQL
            INSERT INTO users (name, email, password) VALUES ('Admin', 'admin@example.com', '{$password}')
        SQL);
    }
}
