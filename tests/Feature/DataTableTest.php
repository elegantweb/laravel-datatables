<?php

namespace Elegant\DataTables\Tests\Feature;

use Elegant\DataTables\Support\Facades\DataTables;
use Elegant\DataTables\Tests\TestCase;

abstract class DataTableTest extends TestCase
{
    abstract protected function getPostSource();

    abstract protected function createPost();

    public function test_can_make_simple_table()
    {
        $posts = collect();
        $posts[] = $this->createPost();

        $request = [
            'draw' => '1000',
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())->build();

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

    public function test_can_filter_columns_using_global_search()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'First', 'content' => 'Alpha']);
        $posts[] = $this->createPost(['title' => 'Alpha', 'content' => 'First']);
        $posts[] = $this->createPost(['title' => 'Second', 'content' => 'Beta']);
        $posts[] = $this->createPost(['title' => 'Third', 'content' => 'Gamma']);

        $request = [
            'draw' => '1000',
            'search' => ['value' => 'First', 'regex' => 'false'],
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'content', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())->build();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 4,
            "recordsFiltered" => 2,
            'data' => $posts->only([0, 1])->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_applies_global_search_to_searchable_columns_only()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'First', 'content' => 'Alpha']);
        $posts[] = $this->createPost(['title' => 'Alpha', 'content' => 'First']);

        $request = [
            'draw' => '1000',
            'search' => ['value' => 'First', 'regex' => 'false'],
            'columns' => [
                ['data' => 'title', 'searchable' => 'false', 'orderable' => 'true'],
                ['data' => 'content', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())->build();

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

    public function test_can_filter_columns_using_column_search()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'First']);
        $posts[] = $this->createPost(['title' => 'Second']);
        $posts[] = $this->createPost(['title' => 'Third']);

        $request = [
            'draw' => '1000',
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => 'Second', 'regex' => 'false']],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())->build();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 3,
            "recordsFiltered" => 1,
            'data' => $posts->only([1])->values()->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_applies_column_search_to_searchable_columns_only()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'First']);
        $posts[] = $this->createPost(['title' => 'Second']);
        $posts[] = $this->createPost(['title' => 'Third']);

        $request = [
            'draw' => '1000',
            'columns' => [
                ['data' => 'title', 'searchable' => 'false', 'orderable' => 'true', 'search' => ['value' => 'Second', 'regex' => 'false']],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())->build();

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

    public function sort_direction_provider()
    {
        return [
            ['asc'],
            ['desc'],
        ];
    }

    /**
     * @dataProvider sort_direction_provider
     */
    public function test_can_sort_columns($dir)
    {
        $posts = collect();
        $posts[] = $this->createPost(['created_at' => '2000-01-01']);
        $posts[] = $this->createPost(['created_at' => '2020-01-01']);
        $posts[] = $this->createPost(['created_at' => '2010-01-01']);

        $request = [
            'draw' => '1000',
            'order' => [
                ['column' => '0', 'dir' => $dir],
            ],
            'columns' => [
                ['data' => 'created_at', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())->build();

        $posts = $posts->sortBy('created_at', SORT_REGULAR, $dir === 'desc')->values();

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
    public function test_applies_sort_to_only_orderable_columns($dir)
    {
        $posts = collect();
        $posts[] = $this->createPost(['created_at' => '2000-01-01']);
        $posts[] = $this->createPost(['created_at' => '2020-01-01']);
        $posts[] = $this->createPost(['created_at' => '2010-01-01']);

        $request = [
            'draw' => '1000',
            'order' => [
                ['column' => '0', 'dir' => $dir]
            ],
            'columns' => [
                ['data' => 'created_at', 'searchable' => 'true', 'orderable' => 'false'],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())->build();

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
    public function test_can_sort_multiple_columns($dir)
    {
        $posts = [];
        $posts[] = $this->createPost(['title' => 'Beta', 'created_at' => '2030-01-01']);
        $posts[] = $this->createPost(['title' => 'Alpha', 'created_at' => '2000-01-01']);
        $posts[] = $this->createPost(['title' => 'Beta', 'created_at' => '2020-01-01']);
        $posts[] = $this->createPost(['title' => 'Alpha', 'created_at' => '2010-01-01']);

        $request = [
            'draw' => '1000',
            'order' => [
                ['column' => '0', 'dir' => $dir],
                ['column' => '1', 'dir' => $dir],
            ],
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'created_at', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())->build();

        usort($posts, fn ($a, $b) => ($a['title'] <=> $b['title']) ?: ($a['created_at'] <=> $b['created_at']));

        if ($dir === 'desc') {
            $posts = array_reverse($posts);
        }

        $posts = collect($posts);

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 4,
            "recordsFiltered" => 4,
            'data' => $posts->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_only_included_columns_are_in_table()
    {
        $posts = collect();
        $posts[] = $this->createPost();
        $posts[] = $this->createPost();
        $posts[] = $this->createPost();

        $request = [
            'draw' => '1000',
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())
            ->include(['title'])
            ->build();

        $posts = $posts->map(function ($post) {
            return collect($post)->only(['title']);
        });

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

    public function test_excluded_columns_are_not_in_table()
    {
        $posts = collect();
        $posts[] = $this->createPost();
        $posts[] = $this->createPost();
        $posts[] = $this->createPost();

        $request = [
            'draw' => '1000',
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())
            ->exclude(['content', 'updated_at'])
            ->build();

        $posts = $posts->map(function ($post) {
            return collect($post)->except(['content', 'updated_at']);
        });

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

    public function test_applies_global_search_to_only_whitelisted_columns()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'First', 'content' => 'Alpha']);
        $posts[] = $this->createPost(['title' => 'Alpha', 'content' => 'First']);

        $request = [
            'draw' => '1000',
            'search' => ['value' => 'Alpha', 'regex' => 'false'],
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'content', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())
            ->whitelist(['title'])
            ->build();

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

    public function test_does_not_apply_global_search_to_blacklisted_columns()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'First', 'content' => 'Alpha']);
        $posts[] = $this->createPost(['title' => 'Alpha', 'content' => 'First']);

        $request = [
            'draw' => '1000',
            'search' => ['value' => 'Alpha', 'regex' => 'false'],
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'content', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())
            ->blacklist(['content'])
            ->build();

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

    public function test_applies_column_search_to_only_whitelisted_columns()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'First', 'content' => 'Alpha']);
        $posts[] = $this->createPost(['title' => 'Alpha', 'content' => 'First']);

        $request = [
            'draw' => '1000',
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => 'Alpha', 'regex' => 'false']],
                ['data' => 'content', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => 'Alpha', 'regex' => 'false']],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())
            ->whitelist(['title'])
            ->build();

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

    public function test_does_not_apply_column_search_blacklisted_columns()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'First', 'content' => 'Alpha']);
        $posts[] = $this->createPost(['title' => 'Alpha', 'content' => 'First']);

        $request = [
            'draw' => '1000',
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => 'Alpha', 'regex' => 'false']],
                ['data' => 'content', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => 'Alpha', 'regex' => 'false']],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())
            ->blacklist(['content'])
            ->build();

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

    public function test_sorts_only_whitelisted_columns()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'Alpha', 'created_at' => '2000-01-01']);
        $posts[] = $this->createPost(['title' => 'Beta', 'created_at' => '2010-01-01']);

        $request = [
            'draw' => '1000',
            'order' => [
                ['column' => '1', 'dir' => 'desc'],
            ],
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'created_at', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())
            ->whitelist(['title'])
            ->build();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 2,
            "recordsFiltered" => 2,
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
    public function test_does_not_sort_blacklisted_columns($dir)
    {
        $posts = collect();
        $posts[] = $this->createPost(['created_at' => '2000-01-01']);
        $posts[] = $this->createPost(['created_at' => '2020-01-01']);
        $posts[] = $this->createPost(['created_at' => '2010-01-01']);

        $request = [
            'draw' => '1000',
            'order' => [
                ['column' => '0', 'dir' => $dir],
            ],
            'columns' => [
                ['data' => 'created_at', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())
            ->blacklist(['created_at'])
            ->build();

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

    public function test_does_not_filter_when_default_filtering_is_disabled()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'Alpha']);
        $posts[] = $this->createPost(['title' => 'Beta']);
        $posts[] = $this->createPost(['title' => 'Gamma']);

        $request = [
            'draw' => '1000',
            'search' => ['value' => 'Alpha', 'regex' => 'false'],
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $builder = DataTables::make($this->getPostSource());
        $builder->defaultFilters(false);
        $dataTable = $builder->build();

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
    public function test_does_not_sort_when_default_sorting_is_disabled($dir)
    {
        $posts = collect();
        $posts[] = $this->createPost(['created_at' => '2000-01-01']);
        $posts[] = $this->createPost(['created_at' => '2020-01-01']);
        $posts[] = $this->createPost(['created_at' => '2010-01-01']);

        $request = [
            'draw' => '1000',
            'order' => [
                ['column' => '0', 'dir' => $dir],
            ],
            'columns' => [
                ['data' => 'created_at', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $builder = DataTables::make($this->getPostSource());
        $builder->defaultSorts(false);
        $dataTable = $builder->build();

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

    public function test_custom_global_filter()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'Alpha', 'content' => 'First']);
        $posts[] = $this->createPost(['title' => 'First', 'content' => 'Alpha']);

        $request = [
            'draw' => '1000',
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'content', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $builder = DataTables::make($this->getPostSource());
        $builder->filter(function ($query) {
            $query->where('title', 'Alpha');
        });
        $dataTable = $builder->build();

        $posts = $posts->sortBy('created_at', SORT_REGULAR, true)->values();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 2,
            "recordsFiltered" => 1,
            'data' => $posts->only([0])->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_custom_global_sort()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'Alpha', 'created_at' => '2000-01-01']);
        $posts[] = $this->createPost(['title' => 'Beta', 'created_at' => '2010-01-01']);

        $request = [
            'draw' => '1000',
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'created_at', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $builder = DataTables::make($this->getPostSource());
        $builder->sort(function ($query) {
            $query->orderBy('created_at', 'desc');
        });
        $dataTable = $builder->build();

        $posts = $posts->sortBy('created_at', SORT_REGULAR, true)->values();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 2,
            "recordsFiltered" => 2,
            'data' => $posts->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_custom_column_filter_for_global_search()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'Alpha', 'content' => 'First']);
        $posts[] = $this->createPost(['title' => 'Second Alpha', 'content' => 'Second First']);

        $request = [
            'draw' => '1000',
            'search' => ['value' => 'Alpha', 'regex' => 'false'],
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'content', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $builder = DataTables::make($this->getPostSource());
        $builder->columnFilter('title', function ($query, $value) {
            $query->where('title', $value); // we make title match exact to see that this function has priority
        });
        $dataTable = $builder->build();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 2,
            "recordsFiltered" => 1,
            'data' => $posts->only([0])->values()->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_custom_column_filter_for_column_search()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'Alpha', 'content' => 'First']);
        $posts[] = $this->createPost(['title' => 'Second Alpha', 'content' => 'Second First']);

        $request = [
            'draw' => '1000',
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => 'Alpha', 'regex' => 'false']],
                ['data' => 'content', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $builder = DataTables::make($this->getPostSource());
        $builder->columnFilter('title', function ($query, $value) {
            $query->where('title', $value); // we make title match exact to see that this function has priority
        });
        $dataTable = $builder->build();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 2,
            "recordsFiltered" => 1,
            'data' => $posts->only([0])->values()->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_custom_column_sort()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'Alpha', 'created_at' => '2000-01-01']);
        $posts[] = $this->createPost(['title' => 'Beta', 'created_at' => '2020-01-01']);
        $posts[] = $this->createPost(['title' => 'Gamma', 'created_at' => '2010-01-01']);

        $request = [
            'draw' => '1000',
            'order' => [
                ['column' => '1', 'dir' => 'desc'],
            ],
            'columns' => [
                ['data' => 'title', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'created_at', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $builder = DataTables::make($this->getPostSource());
        $builder->columnSort('created_at', function ($query, $dir) {
            $query->orderBy('title', $dir); // we sort by title instead of created at to see that this function has priority on default sort
        });
        $dataTable = $builder->build();

        $posts = $posts->sortBy('title', SORT_REGULAR, true)->values();

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

    public function test_applies_paging()
    {
        $posts = collect();
        $posts[] = $this->createPost();
        $posts[] = $this->createPost();
        $posts[] = $this->createPost();
        $posts[] = $this->createPost();

        $request = [
            'draw' => '1000',
            'start' => '2',
            'length' => '2',
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())->build();

        $expected = [
            'draw' => 1000,
            "recordsTotal" => 4,
            "recordsFiltered" => 4,
            'data' => $posts->splice(2, 2)->toArray(),
        ];

        $this->assertEquals(
            $expected,
            $dataTable->toArray(),
        );
    }

    public function test_can_add_custom_columns()
    {
        $posts = collect();
        $posts[] = $this->createPost();

        $request = [
            'draw' => '1000',
        ];

        request()->replace($request);

        $builder = DataTables::make($this->getPostSource());
        $builder->add('custom', function () {
            return 'custom';
        });
        $dataTable = $builder->build();

        $this->assertEquals(
            'custom',
            $dataTable->data[0]['custom'],
        );
    }

    public function test_can_override_columns()
    {
        $posts = collect();
        $posts[] = $this->createPost();

        $request = [
            'draw' => '1000',
        ];

        request()->replace($request);

        $builder = DataTables::make($this->getPostSource());
        $builder->add('title', function ($record) {
            return strtoupper($record->title);
        });
        $dataTable = $builder->build();

        $this->assertEquals(
            strtoupper($posts[0]['title']),
            $dataTable->data[0]['title'],
        );
    }

    public function test_will_not_escape_columns()
    {
        $posts = collect();
        $posts[] = $this->createPost(['content' => '<b>Test</b>']);

        $request = [
            'draw' => '1000',
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())->build();

        $this->assertEquals(
            '&lt;b&gt;Test&lt;/b&gt;',
            $dataTable->data[0]['content'],
        );
    }

    public function test_will_not_escape_raw_columns()
    {
        $posts = collect();
        $posts[] = $this->createPost(['content' => '<b>Test</b>']);

        $request = [
            'draw' => '1000',
        ];

        request()->replace($request);

        $builder = DataTables::make($this->getPostSource());
        $builder->raw(['content']);
        $dataTable = $builder->build();

        $this->assertEquals(
            '<b>Test</b>',
            $dataTable->data[0]['content'],
        );
    }

    public function test_adds_undefined_requested_columns()
    {
        $posts = collect();
        $posts[] = $this->createPost();

        $request = [
            'draw' => '1000',
            'columns' => [
                ['data' => 'undefined', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $dataTable = DataTables::make($this->getPostSource())->build();

        $this->assertEquals(
            '',
            $dataTable->data[0]['undefined'],
        );
    }

    public function test_can_filter_custom_column_by_name_using_global_search()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'Alpha']);
        $posts[] = $this->createPost(['title' => 'Beta']);

        $request = [
            'draw' => '1000',
            'search' => ['value' => 'Beta', 'regex' => 'false'],
            'columns' => [
                ['data' => 'custom', 'name' => 'title', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $builder = DataTables::make($this->getPostSource());
        $builder->add('custom', function ($record) {
            return strtoupper($record->title);
        });
        $dataTable = $builder->build();

        $posts = $posts->map(function ($post) {
            $post['custom'] = strtoupper($post['title']);
            return $post;
        });

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

    public function test_can_filter_custom_column_by_name_using_column_search()
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'Alpha']);
        $posts[] = $this->createPost(['title' => 'Beta']);

        $request = [
            'draw' => '1000',
            'columns' => [
                ['data' => 'custom', 'name' => 'title', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => 'Beta', 'regex' => 'false']],
            ],
        ];

        request()->replace($request);

        $builder = DataTables::make($this->getPostSource());
        $builder->add('custom', function ($record) {
            return strtoupper($record->title);
        });
        $dataTable = $builder->build();

        $posts = $posts->map(function ($post) {
            $post['custom'] = strtoupper($post['title']);
            return $post;
        });

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
    public function test_can_sort_custom_column_by_name($dir)
    {
        $posts = collect();
        $posts[] = $this->createPost(['title' => 'Gamma']);
        $posts[] = $this->createPost(['title' => 'Alpha']);
        $posts[] = $this->createPost(['title' => 'Beta']);

        $request = [
            'draw' => '1000',
            'order' => [
                ['column' => '0', 'dir' => $dir],
            ],
            'columns' => [
                ['data' => 'custom', 'name' => 'title', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ];

        request()->replace($request);

        $builder = DataTables::make($this->getPostSource());
        $builder->add('custom', function ($record) {
            return strtoupper($record->title);
        });
        $dataTable = $builder->build();

        $posts = $posts->map(function ($post) {
            $post['custom'] = strtoupper($post['title']);
            return $post;
        });

        $posts = $posts->sortBy('title', SORT_REGULAR, $dir === 'desc')->values();

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
