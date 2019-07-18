<?php
namespace Duplicatable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class InvoiceTypesFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string', 'default' => null, 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public $records = [
        [
            'id' => 1,
            'name' => 'Type 1',
        ],
        [
            'id' => 2,
            'name' => 'Type 2',
        ],
    ];
}
