<?php
namespace Duplicatable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class InvoiceItemPropertiesFixture extends TestFixture
{
    public array $records = [
        [
            'invoice_item_id' => 1,
            'name' => 'Property 1',
        ],
        [
            'invoice_item_id' => 1,
            'name' => 'Property 2',
        ],
        [
            'invoice_item_id' => 2,
            'name' => 'Property 3',
        ],
        [
            'invoice_item_id' => 3,
            'name' => 'Property 4',
        ],
    ];
}
