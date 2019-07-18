<?php
namespace TestApp\Model\Table;

use Cake\ORM\Table;

class InvoiceItemPropertiesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->addBehavior('Translate', ['fields' => ['name']]);
    }
}
