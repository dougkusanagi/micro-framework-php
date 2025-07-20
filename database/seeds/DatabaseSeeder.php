<?php

use GuepardoSys\Core\BaseSeeder;

class DatabaseSeeder extends BaseSeeder
{
    public function run()
    {
        $this->call(UsersSeeder::class);
        $this->call(CategoriesSeeder::class);
        $this->call(PostsSeeder::class);
    }
}
