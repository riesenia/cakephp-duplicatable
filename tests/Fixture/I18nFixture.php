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
    public string $table = 'i18n';

    /**
     * Records
     *
     * @var array
     */
    public array $records = [
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
