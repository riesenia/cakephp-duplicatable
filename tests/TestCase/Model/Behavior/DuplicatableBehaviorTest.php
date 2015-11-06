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
        'plugin.Duplicatable.invoice_item_properties'
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
     * Test duplicating
     *
     * @return void
     */
    public function testDuplicate()
    {
        $new = $this->Invoices->duplicate(1);
        $invoice = $this->Invoices->get($new, ['contain' => ['InvoiceItems', 'InvoiceItems.InvoiceItemProperties']]);

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
    }
}
