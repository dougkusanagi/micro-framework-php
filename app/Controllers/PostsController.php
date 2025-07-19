<?php
namespace App\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use App\Controllers\BaseController;

class PostsController extends BaseController
{
    public function index()
    {
        $posts = Post::all();
        return view('posts/index', ['posts' => $posts]);
    }

    public function create()
    {
        $categories = Category::all();
        return view('posts/create', ['categories' => $categories]);
    }

    public function store()
    {
        // Validate and save post
    }

    public function show($id)
    {
        $post = Post::find($id);
        return view('posts/show', ['post' => $post]);
    }

    public function edit($id)
    {
        $post = Post::find($id);
        $categories = Category::all();
        return view('posts/edit', ['post' => $post, 'categories' => $categories]);
    }

    public function update($id)
    {
        // Validate and update post
    }

    public function destroy($id)
    {
        // Delete post
    }
} 