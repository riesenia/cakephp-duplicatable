<?php
namespace Duplicatable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class InvoicesTagsFixture extends TestFixture
{
    public array $records = [
        [
            'invoice_id' => 1,
            'tag_id' => 1,
            'is_preserved' => true,
        ],
        [
            'invoice_id' => 1,
            'tag_id' => 2,
            'is_preserved' => true,
        ],
        [
            'invoice_id' => 2,
            'tag_id' => 2,
            'is_preserved' => true,
        ],
    ];
}
