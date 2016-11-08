<?php
namespace Duplicatable\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * DuplicatableBehavior Test Case
 */
class DuplicatableBehaviorTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Duplicatable.invoices',
        'plugin.Duplicatable.invoice_items',
        'plugin.Duplicatable.invoice_item_properties',
        'plugin.Duplicatable.invoice_item_variations',
        'plugin.Duplicatable.i18n'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Invoices = TableRegistry::get('Invoices', ['className' => 'Duplicatable\Test\Fixture\InvoicesTable']);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Invoices);

        parent::tearDown();
    }

    /**
     * Test duplicating with deeply nested associations
     *
     * @return void
     */
    public function testDuplicate()
    {
        $new = $this->Invoices->duplicate(1);
        $invoice = $this->Invoices->get($new, ['contain' => ['InvoiceItems.InvoiceItemProperties', 'InvoiceItems.InvoiceItemVariations']]);

        $this->assertEquals('Invoice name - copy', $invoice->name);
        $this->assertEquals('Contact name', $invoice->contact_name);
        $this->assertEquals(1, $invoice->copied);
        $this->assertEquals(null, $invoice->created);

        $this->assertEquals('Item 1', $invoice->invoice_items[0]->name);
        $this->assertEquals(null, $invoice->invoice_items[0]->created);
        $this->assertEquals('Item 2', $invoice->invoice_items[1]->name);
        $this->assertEquals(null, $invoice->invoice_items[1]->created);

        $this->assertEquals('NEW Property 1', $invoice->invoice_items[0]->invoice_item_properties[0]->name);
        $this->assertEquals('NEW Property 2', $invoice->invoice_items[0]->invoice_item_properties[1]->name);
        $this->assertEquals('NEW Property 3', $invoice->invoice_items[1]->invoice_item_properties[0]->name);

        $this->assertEquals('Variation 1', $invoice->invoice_items[0]->invoice_item_variations[0]->name);
        $this->assertEquals('Variation 2', $invoice->invoice_items[1]->invoice_item_variations[0]->name);
        $this->assertEquals('Variation 3', $invoice->invoice_items[1]->invoice_item_variations[1]->name);
    }

    /**
     * Test duplicating with translations
     *
     * @return void
     */
    public function testDuplicateWithTranslations()
    {
        $this->Invoices->behaviors()->get('Duplicatable')->config('includeTranslations', true);
        $this->Invoices->addBehavior('Translate', ['fields' => ['name']]);
        $this->Invoices->InvoiceItems->InvoiceItemProperties->addBehavior('Translate', ['fields' => ['name']]);

        $new = $this->Invoices->duplicate(1);
        $invoice = $this->Invoices->get($new, [
            'contain' => [
                'InvoiceItems',
                'InvoiceItems.InvoiceItemProperties' => function ($query) {
                    return $query->find('translations');
                },
                'InvoiceItems.InvoiceItemVariations'
            ],
            'finder' => 'translations',
        ]);

        $this->assertEquals('Invoice name - copy', $invoice->name);
        $this->assertEquals('Invoice name - es', $invoice->_translations['es']->name);

        $this->assertEquals('NEW Property 1', $invoice->invoice_items[0]->invoice_item_properties[0]->name);
        $this->assertEquals('Property 1 - es', $invoice->invoice_items[0]->invoice_item_properties[0]->_translations['es']->name);
    }

    /**
     * Test duplicating with the `set` param defined as a callable
     *
     * @return void
     */
    public function testDuplicateWithSetCallable()
    {
        $this->Invoices->behaviors()->get('Duplicatable')->config([
            'set' => [
                'name' => function($value) {
                    return $value . ' ' . md5($value);
                }
            ]
        ]);
        $new = $this->Invoices->duplicate(1);
        $invoice = $this->Invoices->get($new, ['contain' => ['InvoiceItems.InvoiceItemProperties', 'InvoiceItems.InvoiceItemVariations']]);
        $this->assertEquals('Invoice name 09ceae7acef129ed179da25bed1d8e5e - copy', $invoice->name);

        $this->Invoices->behaviors()->get('Duplicatable')->config([
            'set' => [
                'name' => [$this, 'setModifier']
            ]
        ]);
        $new = $this->Invoices->duplicate(1);
        $invoice = $this->Invoices->get($new);
        $this->assertEquals('Invoice name 09ceae7acef129ed179da25bed1d8e5e - copy', $invoice->name);
    }

    /**
     * Modifier method to be used as a callable in the tests
     * 
     * @param string $value Value to be set
     * @return string
     */
    public function setModifier($value) {
        return $value . ' ' . md5($value);
    }
}
