<?php
namespace Duplicatable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class InvoiceItemVariationsFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'invoice_item_id' => ['type' => 'integer', 'default' => null, 'null' => true],
        'name' => ['type' => 'string', 'default' => null, 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public $records = [
        [
            'invoice_item_id' => 1,
            'name' => 'Variation 1',
        ],
        [
            'invoice_item_id' => 2,
            'name' => 'Variation 2',
        ],
        [
            'invoice_item_id' => 2,
            'name' => 'Variation 3',
        ],
        [
            'invoice_item_id' => 3,
            'name' => 'Variation 4',
        ],
    ];
}
