<?php
// posts/index.php
?>
@extends('layouts.app')
@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Posts</h1>
    <a href="/posts/create" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Create New Post</a>
    <table class="min-w-full bg-white">
        <thead>
            <tr>
                <th class="py-2">Title</th>
                <th class="py-2">Category</th>
                <th class="py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
            <tr>
                <td class="py-2"><?php echo htmlspecialchars($post->title); ?></td>
                <td class="py-2"><?php echo htmlspecialchars($post->category_id); ?></td>
                <td class="py-2">
                    <a href="/posts/<?php echo $post->id; ?>" class="text-blue-600">View</a> |
                    <a href="/posts/<?php echo $post->id; ?>/edit" class="text-yellow-600">Edit</a> |
                    <form action="/posts/<?php echo $post->id; ?>/delete" method="POST" style="display:inline;">
                        <button type="submit" class="text-red-600" onclick="return confirm('Delete this post?')">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
@endsection 