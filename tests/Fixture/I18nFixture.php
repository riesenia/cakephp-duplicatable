<?php
namespace Duplicatable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * I18nFixture
 */
class I18nFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'i18n';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer'],
        'locale' => ['type' => 'string', 'length' => 6, 'default' => null],
        'model' => ['type' => 'string', 'length' => 255, 'default' => null],
        'foreign_key' => ['type' => 'integer', 'length' => 10, 'default' => null],
        'field' => ['type' => 'string', 'length' => 255, 'default' => null],
        'content' => ['type' => 'text', 'default' => null],
        '_indexes' => [
            'model' => ['type' => 'index', 'columns' => ['model', 'foreign_key', 'field'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'locale' => ['type' => 'unique', 'columns' => ['locale', 'model', 'foreign_key', 'field'], 'length' => []],
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'locale' => 'es',
            'model' => 'Invoices',
            'foreign_key' => 1,
            'field' => 'name',
            'content' => 'Invoice name - es',
        ],
        [
            'locale' => 'es',
            'model' => 'InvoiceItemProperties',
            'foreign_key' => 1,
            'field' => 'name',
            'content' => 'Property 1 - es',
        ],
    ];
}
