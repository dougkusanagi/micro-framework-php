<?php
namespace App\Models;

use App\Core\BaseModel;

class Category extends BaseModel
{
    protected $table = 'categories';
    protected $fillable = ['name', 'slug'];
} 