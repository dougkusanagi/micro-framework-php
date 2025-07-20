<?php

use GuepardoSys\Core\BaseMigration;

class CreatePostsTable extends BaseMigration
{
    public function up()
    {
        $this->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS posts ( 
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content TEXT NOT NULL,
                user_id INTEGER NOT NULL,
                category_id INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            )
        SQL);
    }

    public function down()
    {
        $this->exec(<<<SQL
            DROP TABLE IF EXISTS posts
        SQL);
    }
}
