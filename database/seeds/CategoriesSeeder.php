<?php

use GuepardoSys\Core\BaseSeeder;

class CategoriesSeeder extends BaseSeeder
{
    public function run()
    {
        $this->exec(<<<SQL
            INSERT INTO categories (name, slug) 
            SELECT * FROM (SELECT 'General', 'general') AS tmp
            WHERE NOT EXISTS (
                SELECT name FROM categories WHERE name = 'General'
            ) LIMIT 1;
        SQL);
    }
}
