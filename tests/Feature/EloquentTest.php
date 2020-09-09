<?php

namespace Elegant\DataTables\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Elegant\DataTables\Tests\Fixtures\Models\Post;
use Elegant\DataTables\Tests\Fixtures\Models\User;
use Elegant\DataTables\Tests\Fixtures\Models\Category;
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

    protected function getUserSource()
    {
        return User::query();
    }

    protected function createUser(array $attributes = [])
    {
        return User::factory()->create($attributes);
    }

    protected function createCategory(array $attributes = [])
    {
        return Category::factory()->create($attributes);
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

    public function test_can_filter_has_many_relation_columns_using_global_search()
    {
        $users = collect();
        $users[] = $this->createUser(['name' => 'Alpha']);
        $users[] = $this->createUser(['name' => 'Beta']);

        $this->createPost(['title' => 'First of Alpha', 'user_id' => $users[0]->id]);
        $this->createPost(['title' => 'Second of Alpha', 'user_id' => $users[0]->id]);

        $this->createPost(['title' => 'First of Beta', 'user_id' => $users[1]->id]);
        $this->createPost(['title' => 'Second of Beta', 'user_id' => $users[1]->id]);

        $users->each->load('posts');

        $request = [
            'draw' => '1000',
            'search' => ['value' => 'First of Beta', 'regex' => 'false'],
            'columns' => [
                ['data' => 'posts.0.title', 'name' => 'posts.title', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $source = $this->getUserSource()->with('posts');

        $dataTable = DataTables::make($source)->build();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 2,
            "recordsFiltered" => 1,
            'data' => $users->only([1])->values()->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_can_filter_belongs_to_relation_columns_using_global_search()
    {
        $user1 = $this->createUser(['name' => 'Alpha']);
        $user2 = $this->createUser(['name' => 'Beta']);

        $posts = collect();
        $posts[] = $this->createPost(['user_id' => $user1->id]);
        $posts[] = $this->createPost(['user_id' => $user2->id]);

        $posts->each->load('user');

        $request = [
            'draw' => '1000',
            'search' => ['value' => 'Beta', 'regex' => 'false'],
            'columns' => [
                ['data' => 'user.name', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $source = $this->getPostSource()->with('user');

        $dataTable = DataTables::make($source)->build();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 2,
            "recordsFiltered" => 1,
            'data' => $posts->only([1])->values()->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_can_filter_belongs_to_many_relation_columns_using_global_search()
    {
        $category1 = $this->createCategory(['label' => 'Alpha']);
        $category2 = $this->createCategory(['label' => 'Beta']);

        $posts = collect();
        $posts[] = $this->createPost();
        $posts[] = $this->createPost();

        $posts[0]->categories()->sync([$category1->id]);
        $posts[1]->categories()->sync([$category1->id, $category2->id]);

        $posts->each->load('categories');

        $request = [
            'draw' => '1000',
            'search' => ['value' => 'Beta', 'regex' => 'false'],
            'columns' => [
                ['data' => 'categories.0.label', 'name' => 'categories.label', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $source = $this->getPostSource()->with('categories');

        $dataTable = DataTables::make($source)->build();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 2,
            "recordsFiltered" => 1,
            'data' => $posts->only([1])->values()->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_can_filter_has_many_relation_columns_using_column_search()
    {
        $users = collect();
        $users[] = $this->createUser(['name' => 'Alpha']);
        $users[] = $this->createUser(['name' => 'Beta']);

        $this->createPost(['title' => 'First of Alpha', 'user_id' => $users[0]->id]);
        $this->createPost(['title' => 'Second of Alpha', 'user_id' => $users[0]->id]);

        $this->createPost(['title' => 'First of Beta', 'user_id' => $users[1]->id]);
        $this->createPost(['title' => 'Second of Beta', 'user_id' => $users[1]->id]);

        $users->each->load('posts');

        $request = [
            'draw' => '1000',
            'columns' => [
                ['data' => 'posts.0.title', 'name' => 'posts.title', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => 'First of Beta', 'regex' => 'false']],
            ],
        ];

        request()->replace($request);

        $source = $this->getUserSource()->with('posts');

        $dataTable = DataTables::make($source)->build();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 2,
            "recordsFiltered" => 1,
            'data' => $users->only([1])->values()->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_can_filter_belongs_to_relation_columns_using_column_search()
    {
        $user1 = $this->createUser(['name' => 'Alpha']);
        $user2 = $this->createUser(['name' => 'Beta']);

        $posts = collect();
        $posts[] = $this->createPost(['user_id' => $user1->id]);
        $posts[] = $this->createPost(['user_id' => $user2->id]);

        $posts->each->load('user');

        $request = [
            'draw' => '1000',
            'columns' => [
                ['data' => 'user.name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => 'Beta', 'regex' => 'false']],
            ],
        ];

        request()->replace($request);

        $source = $this->getPostSource()->with('user');

        $dataTable = DataTables::make($source)->build();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 2,
            "recordsFiltered" => 1,
            'data' => $posts->only([1])->values()->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_can_filter_belongs_to_many_relation_columns_using_column_search()
    {
        $category1 = $this->createCategory(['label' => 'Alpha']);
        $category2 = $this->createCategory(['label' => 'Beta']);

        $posts = collect();
        $posts[] = $this->createPost();
        $posts[] = $this->createPost();

        $posts[0]->categories()->sync([$category1->id]);
        $posts[1]->categories()->sync([$category1->id, $category2->id]);

        $posts->each->load('categories');

        $request = [
            'draw' => '1000',
            'columns' => [
                ['data' => 'categories.0.label', 'name' => 'categories.label', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => 'Beta', 'regex' => 'false']],
            ],
        ];

        request()->replace($request);

        $source = $this->getPostSource()->with('categories');

        $dataTable = DataTables::make($source)->build();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 2,
            "recordsFiltered" => 1,
            'data' => $posts->only([1])->values()->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    /**
     * @dataProvider sort_direction_provider
     */
    public function test_can_sort_has_many_relation_columns($dir)
    {
        $users = collect();
        $users[] = $this->createUser(['name' => 'Alpha']);
        $users[] = $this->createUser(['name' => 'Beta']);

        $this->createPost(['created_at' => '2000-01-01', 'user_id' => $users[0]->id]);
        $this->createPost(['created_at' => '2010-01-01', 'user_id' => $users[1]->id]);

        $users->each->load('posts');

        $request = [
            'draw' => '1000',
            'order' => [
                ['column' => '0', 'dir' => $dir]
            ],
            'columns' => [
                ['data' => 'posts.0.created_at', 'name' => 'posts.created_at', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $source = $this->getUserSource()->with('posts');

        $dataTable = DataTables::make($source)->build();

        $users = $users->sortBy('posts.0.created_at', SORT_REGULAR, $dir === 'desc')->values();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 2,
            "recordsFiltered" => 2,
            'data' => $users->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    /**
     * @dataProvider sort_direction_provider
     */
    public function test_can_sort_belongs_to_relation_columns($dir)
    {
        $user1 = $this->createUser(['created_at' => '2000-01-01']);
        $user2 = $this->createUser(['created_at' => '2010-01-01']);
        $user3 = $this->createUser(['created_at' => '2020-01-01']);

        $posts = collect();
        $posts[] = $this->createPost(['user_id' => $user1->id]);
        $posts[] = $this->createPost(['user_id' => $user2->id]);
        $posts[] = $this->createPost(['user_id' => $user3->id]);

        $posts->each->load('user');

        $request = [
            'draw' => '1000',
            'order' => [
                ['column' => '0', 'dir' => $dir],
            ],
            'columns' => [
                ['data' => 'user.created_at', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $source = $this->getPostSource()->with('user');

        $dataTable = DataTables::make($source)->build();

        $posts = $posts->sortBy('user.created_at', SORT_REGULAR, $dir === 'desc')->values();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 3,
            "recordsFiltered" => 3,
            'data' => $posts->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    /**
     * @dataProvider sort_direction_provider
     */
    public function test_can_sort_belongs_to_many_relation_columns($dir)
    {
        $category1 = $this->createCategory(['label' => 'Alpha']);
        $category2 = $this->createCategory(['label' => 'Beta']);
        $category3 = $this->createCategory(['label' => 'Gamma']);

        $posts = collect();
        $posts[] = $this->createPost();
        $posts[] = $this->createPost();
        $posts[] = $this->createPost();

        $posts[0]->categories()->sync([$category1->id]);
        $posts[1]->categories()->sync([$category2->id]);
        $posts[2]->categories()->sync([$category3->id]);

        $posts->each->load('categories');

        $request = [
            'draw' => '1000',
            'order' => [
                ['column' => '0', 'dir' => $dir],
            ],
            'columns' => [
                ['data' => 'categories.0.label', 'name' => 'categories.label', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $source = $this->getPostSource()->with('categories');

        $dataTable = DataTables::make($source)->build();

        $posts = $posts->sortBy('categories.0.label', SORT_REGULAR, $dir === 'desc')->values();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 3,
            "recordsFiltered" => 3,
            'data' => $posts->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }
}
