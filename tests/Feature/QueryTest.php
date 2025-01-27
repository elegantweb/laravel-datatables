<?php

namespace Elegant\DataTables\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Elegant\DataTables\Tests\Fixtures\Models\Post;
use Elegant\DataTables\Tests\Fixtures\Models\Category;

class QueryTest extends DataTableTesting
{
    use RefreshDatabase;

    protected function getPostSource()
    {
        return DB::table('posts');
    }

    protected function getCategorySource()
    {
        return DB::table('categories');
    }

    protected function createPost(array $attributes = [])
    {
        $post = Post::factory()->create($attributes);

        return (array) DB::table('posts')->where('id', $post->id)->first();
    }

    protected function createCategory(array $attributes = [])
    {
        $post = Category::factory()->create($attributes);

        return (array) DB::table('category')->where('id', $post->id)->first();
    }
}
