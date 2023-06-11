<?php
namespace Duplicatable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class InvoiceItemVariationsFixture extends TestFixture
{
    public array $records = [
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
