<?php
namespace App\Controllers;

use App\Models\Category;
use App\Controllers\BaseController;

class CategoriesController extends BaseController
{
    public function index()
    {
        $categories = Category::all();
        return view('categories/index', ['categories' => $categories]);
    }

    public function create()
    {
        return view('categories/create');
    }

    public function store()
    {
        // Validate and save category
    }

    public function show($id)
    {
        $category = Category::find($id);
        return view('categories/show', ['category' => $category]);
    }

    public function edit($id)
    {
        $category = Category::find($id);
        return view('categories/edit', ['category' => $category]);
    }

    public function update($id)
    {
        // Validate and update category
    }

    public function destroy($id)
    {
        // Delete category
    }
} 