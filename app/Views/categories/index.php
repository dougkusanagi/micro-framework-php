<?php
// categories/index.php
?>
@extends('layouts.app')
@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Categories</h1>
    <a href="/categories/create" class="bg-green-500 text-white px-4 py-2 rounded mb-4 inline-block">Create New Category</a>
    <table class="min-w-full bg-white">
        <thead>
            <tr>
                <th class="py-2">Name</th>
                <th class="py-2">Slug</th>
                <th class="py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
            <tr>
                <td class="py-2"><?php echo htmlspecialchars($category->name); ?></td>
                <td class="py-2"><?php echo htmlspecialchars($category->slug); ?></td>
                <td class="py-2">
                    <a href="/categories/<?php echo $category->id; ?>" class="text-blue-600">View</a> |
                    <a href="/categories/<?php echo $category->id; ?>/edit" class="text-yellow-600">Edit</a> |
                    <form action="/categories/<?php echo $category->id; ?>/delete" method="POST" style="display:inline;">
                        <button type="submit" class="text-red-600" onclick="return confirm('Delete this category?')">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
@endsection 