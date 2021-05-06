<?php
namespace Duplicatable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class TagsFixture extends TestFixture
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
            'name' => 'Tag 1',
        ],
        [
            'name' => 'Tag 2',
        ],
    ];
}
