<?php
// posts/show.php
?>
@extends('layouts.app')
@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($post->title); ?></h1>
    <div class="mb-2 text-gray-600">Category: <?php echo htmlspecialchars($post->category_id); ?></div>
    <div class="mb-4 text-gray-700"><?php echo nl2br(htmlspecialchars($post->content)); ?></div>
    <a href="/posts/<?php echo $post->id; ?>/edit" class="bg-yellow-500 text-white px-4 py-2 rounded">Edit</a>
    <form action="/posts/<?php echo $post->id; ?>/delete" method="POST" style="display:inline;">
        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded" onclick="return confirm('Delete this post?')">Delete</button>
    </form>
    <a href="/posts" class="ml-4 text-blue-600">Back to Posts</a>
</div>
@endsection 