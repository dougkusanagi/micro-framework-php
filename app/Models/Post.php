<?php
namespace App\Models;

use App\Core\BaseModel;

class Post extends BaseModel
{
    protected $table = 'posts';
    protected $fillable = ['title', 'slug', 'content', 'user_id', 'category_id'];
} 