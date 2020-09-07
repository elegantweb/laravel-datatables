<?php

namespace Elegant\DataTables\Tests\Unit;

use Elegant\DataTables\Transformer;
use Elegant\DataTables\Tests\TestCase;

class TransformerTest extends TestCase
{
    public function test_transforms_date_objects_to_string()
    {
        $data = [
            [
                'date' => new \DateTime('January 1, 1970'),
            ],
        ];

        (new Transformer())->transform($data);

        $expected = [
            [
                'date' => '1970-01-01 00:00:00',
            ],
        ];

        $this->assertEquals(
            $expected,
            $data,
        );
    }

    public function test_transforms_objects_to_string()
    {
        $object = new class {
            public function __toString()
            {
                return 'string';
            }
        };

        $data = [
            [
                'object' => $object,
            ],
        ];

        (new Transformer())->transform($data);

        $expected = [
            [
                'object' => 'string',
            ],
        ];

        $this->assertEquals(
            $expected,
            $data,
        );
    }
}
