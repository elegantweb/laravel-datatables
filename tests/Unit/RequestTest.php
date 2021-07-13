<?php

namespace Elegant\DataTables\Tests\Unit;

use Elegant\DataTables\Request;
use Elegant\DataTables\Tests\TestCase;
use Illuminate\Http\Request as HttpRequest;

class RequestTest extends TestCase
{
    protected function cerateHttpRequest(array $params = [])
    {
        return HttpRequest::create('/', 'GET', $params);
    }

    public function draw_value_data_provider()
    {
        return [
            'valid' => ['1000', 1000],
            'invalid' => ['xxxx', 0],
            'empty' => [null, 0],
        ];
    }

    /**
     * @dataProvider draw_value_data_provider
     */
    public function test_can_detect_draw_value($draw, $expected)
    {
        $httpRequest = $this->cerateHttpRequest(['draw' => $draw]);

        $request = new Request($httpRequest);

        $this->assertEquals($expected, $request->draw());
    }

    public function start_point_data_provider()
    {
        return [
            'valid' => ['1000', 1000],
            'invalid' => ['xxxx', null],
            'invalid negative' => ['-1', null],
            'empty' => [null, null],
        ];
    }

    /**
     * @dataProvider start_point_data_provider
     */
    public function test_can_detect_start_point($start, $expected)
    {
        $httpRequest = $this->cerateHttpRequest(['start' => $start]);

        $request = new Request($httpRequest);

        $this->assertEquals($expected, $request->start());
    }

    public function length_number_data_provider()
    {
        return [
            'valid' => ['1000', 1000],
            'valid special -1' => ['-1', -1],
            'invalid' => ['xxxx', null],
            'invalid negative' => ['-2', null],
            'empty' => [null, null],
        ];
    }

    /**
     * @dataProvider length_number_data_provider
     */
    public function test_can_detect_length_number($length, $expected)
    {
        $httpRequest = $this->cerateHttpRequest(['length' => $length]);

        $request = new Request($httpRequest);

        $this->assertEquals($expected, $request->length());
    }

    public function real_length_number_data_provider()
    {
        return [
            'valid' => ['1000', 1000],
            'valid special -1' => ['-1', null],
            'invalid not integer' => ['xxxx', null],
            'invalid negative' => ['-2', null],
            'empty' => [null, null],
        ];
    }

    /**
     * @dataProvider real_length_number_data_provider
     */
    public function test_can_detect_real_length_number($length, $expected)
    {
        $httpRequest = $this->cerateHttpRequest(['length' => $length]);

        $request = new Request($httpRequest);

        $this->assertEquals($expected, $request->realLength());
    }

    public function paging_status_data_provider()
    {
        return [
            [['start' => '0', 'length' => '10'], true],
            [['start' => '0', 'length' => '-1'], true],
            [['start' => '0'], false],
            [['length' => '10'], false],
            [['start' => 'xxxx', 'length' => '10'], false],
            [['start' => '0', 'length' => 'xxxx'], false],
        ];
    }

    /**
     * @dataProvider paging_status_data_provider
     */
    public function test_can_indicate_paging_status(array $params, $expected)
    {
        $httpRequest = $this->cerateHttpRequest($params);

        $request = new Request($httpRequest);

        $this->assertEquals($expected, $request->hasPaging());
    }

    public function global_search_data_provider()
    {
        return [
            'valid normal' => [['value' => 'match', 'regex' => 'false'], ['value' => 'match', 'regex' => false]],
            'valid regex' => [['value' => '/^match$/', 'regex' => 'true'], ['value' => '/^match$/', 'regex' => true]],
            'invalid no regex' => [['value' => 'match'], null],
            'invalid no value' => [['regex' => 'true'], null],
            'invalid empty value' => [['value' => '', 'regex' => 'true'], null],
            'empty' => [null, null],
        ];
    }

    /**
     * @dataProvider global_search_data_provider
     */
    public function test_can_detect_global_search($search, $expected)
    {
        $httpRequest = $this->cerateHttpRequest(['search' => $search]);

        $request = new Request($httpRequest);

        $this->assertEquals($expected, $request->search());
    }

    public function global_search_status_data_provider()
    {
        return [
            'valid normal' => [['value' => 'match', 'regex' => 'false'], true],
            'valid regex' => [['value' => '/^match$/', 'regex' => 'true'], true],
            'invalid no regex' => [['value' => 'match'], false],
            'invalid no value' => [['regex' => 'true'], false],
            'invalid empty value' => [['value' => '', 'regex' => 'true'], false],
            'empty' => [null, false],
        ];
    }

    /**
     * @dataProvider global_search_status_data_provider
     */
    public function test_can_detect_global_search_status($search, $expected)
    {
        $httpRequest = $this->cerateHttpRequest(['search' => $search]);

        $request = new Request($httpRequest);

        $this->assertEquals($expected, $request->hasSearch());
    }

    public function order_data_provider()
    {
        return [
            'valid asc' => [[['column' => '1', 'dir' => 'asc']], [['column' => '1', 'dir' => 'asc']]],
            'valid desc' => [[['column' => '2', 'dir' => 'desc']], [['column' => '2', 'dir' => 'desc']]],
            'invalid different type' => ['xxxx', []],
            'invalid different children type' => [['xxxx', 'xxxx'], []],
            'invalid without column' => [[['dir' => 'asc']], []],
            'invalid without dir' => [[['column' => '1']], []],
            'invalid column value' => [[['column' => 'xxxx', 'dir' => 'asc']], []],
            'invalid dir value' => [[['column' => '2', 'dir' => 'xxxx']], []],
            'empty' => [null, []],
        ];
    }

    /**
     * @dataProvider order_data_provider
     */
    public function test_can_detect_order($order, $expected)
    {
        $httpRequest = $this->cerateHttpRequest(['order' => $order]);

        $request = new Request($httpRequest);

        $this->assertEquals($expected, $request->order());
    }

    public function columns_data_provider()
    {
        $data = [];

        $data['valid only data'] = [
            null, // we don't need order here
            [['data' => 'title']],
            [['data' => 'title', 'name' => 'title', 'searchable' => false, 'orderable' => false, 'search' => null, 'order' => null]],
        ];

        $data['valid with empty name'] = [
            null, // we don't need order here
            [['data' => 'title', 'name' => '']],
            [['data' => 'title', 'name' => 'title', 'searchable' => false, 'orderable' => false, 'search' => null, 'order' => null]],
        ];

        $data['valid without search and order'] = [
            null, // we don't need order here
            [['data' => 'title', 'name' => 'heading', 'searchable' => 'true', 'orderable' => 'false']],
            [['data' => 'title', 'name' => 'heading', 'searchable' => true, 'orderable' => false, 'search' => null, 'order' => null]],
        ];

        $data['valid with search and order'] = [
            [['column' => '0', 'dir' => 'asc']],
            [['data' => 'title', 'name' => 'heading', 'searchable' => 'false', 'orderable' => 'true', 'search' => ['value' => 'match', 'regex' => 'true']]],
            [['data' => 'title', 'name' => 'heading', 'searchable' => false, 'orderable' => true, 'search' => ['value' => 'match', 'regex' => true], 'order' => ['dir' => 'asc', 'pri' => 0]]],
        ];

        $data['invalid different type'] = [
            null, // we don't need order here
            'xxxx',
            [], // expected
        ];

        $data['invalid different children type'] = [
            null, // we don't need order here
            [['xxxx']],
            [], // expected
        ];

        $data['invalid empty data'] = [
            null, // we don't need order here
            [['data' => '']],
            [], // expected
        ];

        $data['empty'] = [
            null,
            null,
            [],
        ];

        return $data;
    }

    /**
     * @dataProvider columns_data_provider
     */
    public function test_cen_detect_columns($order, $columns, $expected)
    {
        $httpRequest = $this->cerateHttpRequest(compact('order', 'columns'));

        $request = new Request($httpRequest);

        $this->assertEquals($expected, $request->columns());
    }

    public function test_can_detect_searchable_columns()
    {
        $columns = [];
        $columns[] = ['data' => 'id', 'searchable' => 'false'];
        $columns[] = ['data' => 'id', 'searchable' => 'true'];
        $columns[] = ['data' => 'id', 'searchable' => 'true'];

        $httpRequest = $this->cerateHttpRequest(['columns' => $columns]);

        $request = new Request($httpRequest);

        $this->assertEquals(
            [1, 2],
            array_keys($request->searchableColumns()),
        );
    }

    public function test_can_detect_orderable_columns()
    {
        $columns = [];
        $columns[] = ['data' => 'id', 'orderable' => 'true'];
        $columns[] = ['data' => 'id', 'orderable' => 'false'];
        $columns[] = ['data' => 'id', 'orderable' => 'true'];

        $httpRequest = $this->cerateHttpRequest(['columns' => $columns]);

        $request = new Request($httpRequest);

        $this->assertEquals(
            [0, 2],
            array_keys($request->orderableColumns()),
        );
    }

    public function test_can_detect_search_columns()
    {
        $columns = [];
        $columns[] = ['data' => 'id', 'searchable' => 'true', 'search' => ['value' => 'match', 'regex' => 'true']];
        $columns[] = ['data' => 'id', 'searchable' => 'false', 'search' => ['value' => 'match', 'regex' => 'true']];
        $columns[] = ['data' => 'id', 'searchable' => 'true', 'search' => ['value' => 'match']];
        $columns[] = ['data' => 'id', 'searchable' => 'true', 'search' => ['regex' => 'true']];
        $columns[] = ['data' => 'id', 'searchable' => 'true'];

        $httpRequest = $this->cerateHttpRequest(['columns' => $columns]);

        $request = new Request($httpRequest);

        $this->assertEquals(
            [0],
            array_keys($request->searchColumns()),
        );
    }

    public function test_can_detect_order_columns()
    {
        $order = [];
        $order[] = ['column' => '0', 'dir' => 'asc'];
        $order[] = ['column' => '1', 'dir' => 'desc'];
        $order[] = ['column' => '2', 'dir' => 'asc'];
        $order[] = ['column' => '3', 'dir' => 'xxxx'];
        $order[] = ['column' => '4'];

        $columns = [];
        $columns[0] = ['data' => 'id', 'orderable' => 'true'];
        $columns[1] = ['data' => 'id', 'orderable' => 'false'];
        $columns[2] = ['data' => 'id', 'orderable' => 'true'];
        $columns[3] = ['data' => 'id', 'orderable' => 'true'];
        $columns[4] = ['data' => 'id', 'orderable' => 'true'];

        $httpRequest = $this->cerateHttpRequest(['order' => $order, 'columns' => $columns]);

        $request = new Request($httpRequest);

        $this->assertEquals(
            [0, 2],
            array_keys($request->orderColumns()),
        );
    }
}
