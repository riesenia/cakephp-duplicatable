<?php
namespace TestApp\Model\Table;

use Cake\ORM\Table;

class InvoicesTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->addBehavior('Duplicatable.Duplicatable', [
            'contain' => [
                'InvoiceItems.InvoiceItemProperties',
                'InvoiceItems.InvoiceItemVariations'
            ],
            'remove' => [
                'created',
                'InvoiceItems.created'
            ],
            'set' => [
                'copied' => true
            ],
            'prepend' => [
                'InvoiceItems.InvoiceItemProperties.name' => 'NEW '
            ],
            'append' => [
                'name' => ' - copy'
            ]
        ]);

        $this->hasMany('InvoiceItems', [
            'className' => 'TestApp\Model\Table\InvoiceItemsTable'
        ]);
    }
}
