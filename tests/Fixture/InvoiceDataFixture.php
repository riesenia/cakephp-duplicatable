<?php
namespace Duplicatable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class InvoiceDataFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'invoice_id' => ['type' => 'integer'],
        'data' => ['type' => 'string', 'default' => null, 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']]
        ]
    ];

    public $records = [
        [
            'id' => 1,
            'invoice_id' => 1,
            'data' => 'Data for invoice 1'
        ],
        [
            'id' => 2,
            'invoice_id' => 2,
            'data' => 'Data for invoice 2'
        ],
    ];
}
