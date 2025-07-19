<?php
// posts/create.php
?>
@extends('layouts.app')
@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Create New Post</h1>
    <form action="/posts" method="POST" class="space-y-4">
        <div>
            <label class="block">Title</label>
            <input type="text" name="title" class="border rounded w-full px-3 py-2" required>
        </div>
        <div>
            <label class="block">Slug</label>
            <input type="text" name="slug" class="border rounded w-full px-3 py-2" required>
        </div>
        <div>
            <label class="block">Content</label>
            <textarea name="content" class="border rounded w-full px-3 py-2" rows="6" required></textarea>
        </div>
        <div>
            <label class="block">Category</label>
            <select name="category_id" class="border rounded w-full px-3 py-2" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category->id; ?>"><?php echo htmlspecialchars($category->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Create</button>
    </form>
</div>
@endsection 