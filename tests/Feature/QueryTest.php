<?php

namespace Elegant\DataTables\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Elegant\DataTables\Tests\Fixtures\Models\Post;

class QueryTest extends DataTableTest
{
    use RefreshDatabase;

    protected function getPostSource()
    {
        return DB::table('posts');
    }

    protected function createPost(array $attributes = [])
    {
        $post = Post::factory()->create($attributes);

        return (array) DB::table('posts')->where('id', $post->id)->first();
    }
}
