<?php
declare(strict_types=1);

return [
    'invoice_types' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string', 'default' => null, 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'invoices' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'invoice_type_id' => ['type' => 'integer', 'default' => null, 'null' => true],
            'name' => ['type' => 'string', 'default' => null, 'null' => true],
            'contact_name' => ['type' => 'string', 'default' => null, 'null' => true],
            'copied' => ['type' => 'boolean', 'null' => false],
            'created' => ['type' => 'datetime', 'default' => null, 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'invoice_data' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'invoice_id' => ['type' => 'integer'],
            'data' => ['type' => 'string', 'default' => null, 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'invoice_items' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'invoice_id' => ['type' => 'integer', 'default' => null, 'null' => true],
            'name' => ['type' => 'string', 'default' => null, 'null' => true],
            'amount' => ['type' => 'float', 'default' => null, 'null' => true],
            'created' => ['type' => 'datetime', 'default' => null, 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'invoice_item_properties' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'invoice_item_id' => ['type' => 'integer', 'default' => null, 'null' => true],
            'name' => ['type' => 'string', 'default' => null, 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'invoice_item_variations' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'invoice_item_id' => ['type' => 'integer', 'default' => null, 'null' => true],
            'name' => ['type' => 'string', 'default' => null, 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'invoices_tags' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'invoice_id' => ['type' => 'integer', 'default' => null, 'null' => true],
            'tag_id' => ['type' => 'integer', 'default' => null, 'null' => true],
            'is_preserved' => ['type' => 'boolean', 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'i18n' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'locale' => ['type' => 'string', 'length' => 6, 'default' => null],
            'model' => ['type' => 'string', 'length' => 255, 'default' => null],
            'foreign_key' => ['type' => 'integer', 'length' => 10, 'default' => null],
            'field' => ['type' => 'string', 'length' => 255, 'default' => null],
            'content' => ['type' => 'text', 'default' => null],
        ],
        'indexes' => [
            'model' => ['type' => 'index', 'columns' => ['model', 'foreign_key', 'field'], 'length' => []],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'locale' => ['type' => 'unique', 'columns' => ['locale', 'model', 'foreign_key', 'field'], 'length' => []],
        ],
    ],
    'tags' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string', 'default' => null, 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],

];
