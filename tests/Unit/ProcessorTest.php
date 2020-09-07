<?php

namespace Elegant\DataTables\Tests\Unit;

use Elegant\DataTables\Processor;
use Elegant\DataTables\Tests\TestCase;

class ProcessorTest extends TestCase
{
    public function test_adds_undefined_requested_column()
    {
        $records = [
            [
                'name' => 'First Post',
            ]
        ];

        $processor = new Processor();
        $processor->request(['category']);

        $processed = $processor->process($records);

        $expected = [
            [
                'name' => 'First Post',
                'category' => '',
            ]
        ];

        $this->assertEquals(
            $expected,
            $processed,
        );
    }

    public function test_adds_custom_callable_columns()
    {
        $records = [
            [
                'name' => 'First Post',
            ]
        ];

        $addon = [
            'category' => function (array $params) {
                return "Category of {$params['name']}";
            }
        ];

        $processor = new Processor();
        $processor->add($addon);

        $processed = $processor->process($records);

        $expected = [
            [
                'name' => 'First Post',
                'category' => 'Category of First Post',
            ]
        ];

        $this->assertEquals(
            $expected,
            $processed,
        );
    }

    public function test_adds_custom_blade_view_columns()
    {
        $records = [
            [
                'name' => 'First Post',
            ]
        ];

        $addon = [
            'category' => 'text',
        ];

        $processor = new Processor();
        $processor->add($addon);

        $processed = $processor->process($records);

        $expected = [
            [
                'name' => 'First Post',
                'category' => "Text\n",
            ]
        ];

        $this->assertEquals(
            $expected,
            $processed,
        );
    }

    public function test_adds_custom_blade_string_columns()
    {
        $records = [
            [
                'name' => 'First Post',
            ]
        ];

        $addon = [
            'category' => "{{ 'Text' }}\n",
        ];

        $processor = new Processor();
        $processor->add($addon);

        $processed = $processor->process($records);

        $expected = [
            [
                'name' => 'First Post',
                'category' => "Text\n",
            ]
        ];

        $this->assertEquals(
            $expected,
            $processed,
        );
    }

    public function test_escapes_html_columns()
    {
        $records = [
            [
                'name' => 'First Post',
                'category' => '<b>Blog</b>',
            ]
        ];

        $processor = new Processor();

        $processed = $processor->process($records);

        $expected = [
            [
                'name' => 'First Post',
                'category' => "&lt;b&gt;Blog&lt;/b&gt;",
            ]
        ];

        $this->assertEquals(
            $expected,
            $processed,
        );
    }

    public function test_escapes_custom_callable_html_columns()
    {
        $records = [
            [
                'name' => 'First Post',
            ]
        ];

        $addon = [
            'category' => fn () => '<b>Blog</b>',
        ];

        $processor = new Processor();
        $processor->add($addon);

        $processed = $processor->process($records);

        $expected = [
            [
                'name' => 'First Post',
                'category' => "&lt;b&gt;Blog&lt;/b&gt;",
            ]
        ];

        $this->assertEquals(
            $expected,
            $processed,
        );
    }

    public function test_does_not_escape_raw_columns()
    {
        $records = [
            [
                'name' => 'First Post',
                'category' => '<b>Blog</b>',
            ]
        ];

        $processor = new Processor();
        $processor->raw(['category']);

        $processed = $processor->process($records);

        $expected = [
            [
                'name' => 'First Post',
                'category' => "<b>Blog</b>",
            ]
        ];

        $this->assertEquals(
            $expected,
            $processed,
        );
    }

    public function test_does_not_escape_custom_callable_raw_columns()
    {
        $records = [
            [
                'name' => 'First Post',
            ]
        ];

        $addon = [
            'category' => fn () => '<b>Blog</b>',
        ];

        $processor = new Processor();
        $processor->add($addon);
        $processor->raw(['category']);

        $processed = $processor->process($records);

        $expected = [
            [
                'name' => 'First Post',
                'category' => "<b>Blog</b>",
            ]
        ];

        $this->assertEquals(
            $expected,
            $processed,
        );
    }

    public function test_can_include_columns()
    {
        $records = [
            [
                'name' => 'First Post',
                'category' => 'Blog',
            ]
        ];

        $processor = new Processor();
        $processor->include(['name']);

        $processed = $processor->process($records);

        $expected = [
            [
                'name' => 'First Post',
            ]
        ];

        $this->assertEquals(
            $expected,
            $processed,
        );
    }

    public function test_can_include_custom_columns()
    {
        $records = [
            [
                'name' => 'First Post',
            ]
        ];

        $addon = [
            'category' => fn () => 'Blog',
        ];

        $processor = new Processor();
        $processor->include(['name']);

        $processed = $processor->process($records);

        $expected = [
            [
                'name' => 'First Post',
            ]
        ];

        $this->assertEquals(
            $expected,
            $processed,
        );
    }

    public function test_can_exclude_columns()
    {
        $records = [
            [
                'name' => 'First Post',
                'category' => 'Blog',
            ]
        ];

        $processor = new Processor();
        $processor->exclude(['category']);

        $processed = $processor->process($records);

        $expected = [
            [
                'name' => 'First Post',
            ]
        ];

        $this->assertEquals(
            $expected,
            $processed,
        );
    }

    public function test_can_exclude_custom_columns()
    {
        $records = [
            [
                'name' => 'First Post',
            ]
        ];

        $addon = [
            'category' => fn () => 'Blog',
        ];

        $processor = new Processor();
        $processor->exclude(['category']);

        $processed = $processor->process($records);

        $expected = [
            [
                'name' => 'First Post',
            ]
        ];

        $this->assertEquals(
            $expected,
            $processed,
        );
    }
}
