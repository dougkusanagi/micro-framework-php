<?php
// categories/show.php
?>
@extends('layouts.app')
@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($category->name); ?></h1>
    <div class="mb-2 text-gray-600">Slug: <?php echo htmlspecialchars($category->slug); ?></div>
    <a href="/categories/<?php echo $category->id; ?>/edit" class="bg-yellow-500 text-white px-4 py-2 rounded">Edit</a>
    <form action="/categories/<?php echo $category->id; ?>/delete" method="POST" style="display:inline;">
        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded" onclick="return confirm('Delete this category?')">Delete</button>
    </form>
    <a href="/categories" class="ml-4 text-blue-600">Back to Categories</a>
</div>
@endsection 