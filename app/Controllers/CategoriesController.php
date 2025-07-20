<?php
namespace App\Controllers;

use App\Models\Category;
use App\Controllers\BaseController;
use GuepardoSys\Core\Request;

class CategoriesController extends BaseController
{
    public function index()
    {
        $categories = Category::all();
        return view('categories/index', [
            'categories' => $categories,
            'title' => 'Manage Categories'
        ]);
    }

    public function create()
    {
        return view('categories/create', [
            'title' => 'Create Category'
        ]);
    }

    public function store(Request $request)
    {
        $data = [
            'name' => $request->input('name', '')
        ];

        $errors = Category::validate($data);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: /categories/create');
            exit;
        }

        try {
            $category = Category::create($data);
            $_SESSION['success'] = 'Category created successfully!';
            header('Location: /categories');
            exit;
        } catch (\Exception $e) {
            $_SESSION['errors'] = ['general' => 'Failed to create category. Please try again.'];
            $_SESSION['old'] = $data;
            header('Location: /categories/create');
            exit;
        }
    }

    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            header('Location: /categories');
            exit;
        }

        return view('categories/show', [
            'category' => $category,
            'title' => $category->name
        ]);
    }

    public function edit($id)
    {
        $category = Category::find($id);
        if (!$category) {
            header('Location: /categories');
            exit;
        }

        return view('categories/edit', [
            'category' => $category,
            'title' => 'Edit Category'
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            header('Location: /categories');
            exit;
        }

        $data = [
            'name' => $request->input('name', '')
        ];

        $errors = Category::validate($data);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: /categories/' . $id . '/edit');
            exit;
        }

        try {
            $category->update($data);
            $_SESSION['success'] = 'Category updated successfully!';
            header('Location: /categories');
            exit;
        } catch (\Exception $e) {
            $_SESSION['errors'] = ['general' => 'Failed to update category. Please try again.'];
            $_SESSION['old'] = $data;
            header('Location: /categories/' . $id . '/edit');
            exit;
        }
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            header('Location: /categories');
            exit;
        }

        // Check if category has posts
        $posts = $category->posts();
        if (!empty($posts)) {
            $_SESSION['errors'] = ['general' => 'Cannot delete category that has posts. Please move or delete the posts first.'];
            header('Location: /categories');
            exit;
        }

        try {
            $category->delete();
            $_SESSION['success'] = 'Category deleted successfully!';
        } catch (\Exception $e) {
            $_SESSION['errors'] = ['general' => 'Failed to delete category. Please try again.'];
        }

        header('Location: /categories');
        exit;
    }
} 