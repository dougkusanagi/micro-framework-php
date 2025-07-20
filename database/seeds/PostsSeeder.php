<?php

use GuepardoSys\Core\BaseSeeder;

class PostsSeeder extends BaseSeeder
{
    public function run()
    {
        $this->exec(<<<SQL
            INSERT INTO posts (title, slug, content, user_id) VALUES ('Hello World', 'hello-world', 'This is a sample post.', 1)
        SQL);
    }
}
