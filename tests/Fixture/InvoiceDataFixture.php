<?php
namespace Duplicatable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class InvoiceDataFixture extends TestFixture
{
    public array $records = [
        [
            'invoice_id' => 1,
            'data' => 'Data for invoice 1',
        ],
        [
            'invoice_id' => 2,
            'data' => 'Data for invoice 2',
        ],
    ];
}
