<?php
namespace Duplicatable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class InvoicesTagsFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'invoice_id' => ['type' => 'integer', 'default' => null, 'null' => true],
        'tag_id' => ['type' => 'integer', 'default' => null, 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public $records = [
        [
            'id' => 1,
            'invoice_id' => 1,
            'tag_id' => 1,
        ],
        [
            'id' => 2,
            'invoice_id' => 1,
            'tag_id' => 2,
        ],
        [
            'id' => 3,
            'invoice_id' => 2,
            'tag_id' => 2,
        ],
    ];
}
