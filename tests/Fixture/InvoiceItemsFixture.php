<?php
namespace Duplicatable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class InvoiceItemsFixture extends TestFixture
{
    public array $records = [
        [
            'invoice_id' => 1,
            'name' => 'Item 1',
            'amount' => 10.2,
            'created' => '2015-03-17 01:20:48',
        ],
        [
            'invoice_id' => 1,
            'name' => 'Item 2',
            'amount' => 5.3,
            'created' => '2015-03-17 01:21:20',
        ],
        [
            'invoice_id' => 2,
            'name' => 'Item',
            'amount' => 150,
            'created' => '2015-05-17 03:49:20',
        ],
    ];
}
