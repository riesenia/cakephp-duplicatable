<?php
namespace Duplicatable\Test\Fixture;

use Cake\ORM\Table;
use Cake\TestSuite\Fixture\TestFixture;

class InvoicesTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        // add Duplicatable behavior
        $this->addBehavior('Duplicatable.Duplicatable', [
            'contain' => ['InvoiceItems.InvoiceItemProperties', 'InvoiceItems.InvoiceItemVariations'],
            'remove' => ['created', 'InvoiceItems.created'],
            'set' => ['copied' => true],
            'prepend' => ['InvoiceItems.InvoiceItemProperties.name' => 'NEW '],
            'append' => ['name' => ' - copy']
        ]);

        // associations
        $this->hasMany('InvoiceItems', ['className' => 'Duplicatable\Test\Fixture\InvoiceItemsTable']);
    }
}

class InvoicesFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string', 'default' => null, 'null' => true],
        'contact_name' => ['type' => 'string', 'default' => null, 'null' => true],
        'copied' => ['type' => 'boolean', 'null' => false],
        'created' => ['type' => 'datetime', 'default' => null, 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']]
        ]
    ];

    public $records = [
        [
            'id'            => 1,
            'name'          => 'Invoice name',
            'contact_name'  => 'Contact name',
            'copied'        => 0,
            'created'       => '2015-03-17 01:20:23'
        ],
        [
            'id'            => 2,
            'name'          => 'Invoice name 2',
            'contact_name'  => 'Contact name 2',
            'copied'        => 0,
            'created'       => '2015-05-17 03:20:54'
        ]
    ];
}
