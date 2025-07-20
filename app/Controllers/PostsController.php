<?php
namespace App\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use App\Controllers\BaseController;
use GuepardoSys\Core\Request;

class PostsController extends BaseController
{
    public function index()
    {
        $posts = Post::all();
        return view('posts/index', [
            'posts' => $posts,
            'title' => 'Manage Posts'
        ]);
    }

    public function create()
    {
        $categories = Category::all();
        return view('posts/create', [
            'categories' => $categories,
            'title' => 'Create Post'
        ]);
    }

    public function store(Request $request)
    {
        $data = [
            'title' => $request->input('title', ''),
            'content' => $request->input('content', ''),
            'category_id' => $request->input('category_id', ''),
            'user_id' => $_SESSION['user_id'] ?? 1 // Default to user ID 1 if not set
        ];

        $errors = Post::validate($data);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: /posts/create');
            exit;
        }

        try {
            $post = Post::create($data);
            $_SESSION['success'] = 'Post created successfully!';
            header('Location: /posts');
            exit;
        } catch (\Exception $e) {
            $_SESSION['errors'] = ['general' => 'Failed to create post. Please try again.'];
            $_SESSION['old'] = $data;
            header('Location: /posts/create');
            exit;
        }
    }

    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) {
            header('Location: /posts');
            exit;
        }

        return view('posts/show', [
            'post' => $post,
            'title' => $post->title
        ]);
    }

    public function edit($id)
    {
        $post = Post::find($id);
        if (!$post) {
            header('Location: /posts');
            exit;
        }

        $categories = Category::all();
        return view('posts/edit', [
            'post' => $post,
            'categories' => $categories,
            'title' => 'Edit Post'
        ]);
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) {
            header('Location: /posts');
            exit;
        }

        $data = [
            'title' => $request->input('title', ''),
            'content' => $request->input('content', ''),
            'category_id' => $request->input('category_id', '')
        ];

        $errors = Post::validate($data);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: /posts/' . $id . '/edit');
            exit;
        }

        try {
            $post->update($data);
            $_SESSION['success'] = 'Post updated successfully!';
            header('Location: /posts');
            exit;
        } catch (\Exception $e) {
            $_SESSION['errors'] = ['general' => 'Failed to update post. Please try again.'];
            $_SESSION['old'] = $data;
            header('Location: /posts/' . $id . '/edit');
            exit;
        }
    }

    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            header('Location: /posts');
            exit;
        }

        try {
            $post->delete();
            $_SESSION['success'] = 'Post deleted successfully!';
        } catch (\Exception $e) {
            $_SESSION['errors'] = ['general' => 'Failed to delete post. Please try again.'];
        }

        header('Location: /posts');
        exit;
    }
} 