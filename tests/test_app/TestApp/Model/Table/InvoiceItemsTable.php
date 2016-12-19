<?php
namespace TestApp\Model\Table;

use Cake\ORM\Table;

class InvoiceItemsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->hasMany('InvoiceItemProperties');
        $this->hasMany('InvoiceItemVariations');
    }
}
