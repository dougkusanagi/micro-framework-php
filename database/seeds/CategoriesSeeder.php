<?php

use GuepardoSys\Core\BaseSeeder;

class CategoriesSeeder extends BaseSeeder
{
    public function run()
    {
        $this->exec(<<<SQL
            INSERT INTO categories (name, slug) VALUES ('General', 'general')
        SQL);
    }
}
