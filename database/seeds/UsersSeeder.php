<?php

use GuepardoSys\Core\BaseSeeder;

class UsersSeeder extends BaseSeeder
{
    public function run()
    {
        $password = password_hash('password', PASSWORD_BCRYPT);
        $this->exec(<<<SQL
            INSERT INTO users (name, email, password) VALUES ('Test User', 'test@example.com', '{$password}')
        SQL);
    }
}
