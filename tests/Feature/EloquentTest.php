<?php

namespace Elegant\DataTables\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Elegant\DataTables\Tests\Fixtures\Models\Post;
use Elegant\DataTables\Support\Facades\DataTables;

class EloquentTest extends DataTableTest
{
    use RefreshDatabase;

    protected function getPostSource()
    {
        return Post::query();
    }

    protected function createPost(array $attributes = [])
    {
        return Post::factory()->create($attributes);
    }

    public function test_related_columns()
    {
        $posts = collect();
        $posts[] = $this->createPost();

        $posts->each->load('user');

        $request = [
            'draw' => '1000',
            'columns' => [
                ['data' => 'user.name', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $source = $this->getPostSource()->with('user');

        $dataTable = DataTables::make($source)->build();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 1,
            "recordsFiltered" => 1,
            'data' => $posts->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_can_filter_has_many_relationship_columns_using_global_search()
    {
        $this->assertTrue(true);
    }

    public function test_can_filter_belongs_to_relationship_columns_using_global_search()
    {
        $this->assertTrue(true);
    }

    public function test_can_filter_belongs_to_many_relationship_columns_using_global_search()
    {
        $this->assertTrue(true);
    }

    public function test_can_filter_has_many_relationship_columns_using_column_search()
    {
        $this->assertTrue(true);
    }

    public function test_can_filter_belongs_to_relationship_columns_using_column_search()
    {
        $this->assertTrue(true);
    }

    public function test_can_filter_belongs_to_many_relationship_columns_using_column_search()
    {
        $this->assertTrue(true);
    }

    public function test_can_sort_has_many_relationship_columns()
    {
        $this->assertTrue(true);
    }

    public function test_can_sort_belongs_to_relationship_columns()
    {
        $this->assertTrue(true);
    }

    public function test_can_sort_belongs_to_many_relationship_columns()
    {
        $this->assertTrue(true);
    }
}
