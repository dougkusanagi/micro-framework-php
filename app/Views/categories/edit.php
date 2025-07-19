<?php
// categories/edit.php
?>
@extends('layouts.app')
@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Edit Category</h1>
    <form action="/categories/<?php echo $category->id; ?>/update" method="POST" class="space-y-4">
        <div>
            <label class="block">Name</label>
            <input type="text" name="name" class="border rounded w-full px-3 py-2" value="<?php echo htmlspecialchars($category->name); ?>" required>
        </div>
        <div>
            <label class="block">Slug</label>
            <input type="text" name="slug" class="border rounded w-full px-3 py-2" value="<?php echo htmlspecialchars($category->slug); ?>" required>
        </div>
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update</button>
    </form>
</div>
@endsection 