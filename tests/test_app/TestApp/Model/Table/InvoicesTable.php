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
                'InvoiceItems.InvoiceItemVariations',
                'InvoiceTypes',
                'Tags'
            ],
            'remove' => [
                'created',
                'items.created'
            ],
            'set' => [
                'copied' => true
            ],
            'prepend' => [
                'items.invoice_item_properties.name' => 'NEW '
            ],
            'append' => [
                'name' => ' - copy'
            ]
        ]);

        $this->belongsTo('InvoiceTypes');
        $this->belongsToMany('Tags');
        $this->hasMany('InvoiceItems', [
            'className' => 'TestApp\Model\Table\InvoiceItemsTable',
            'propertyName' => 'items'
        ]);
    }
}
