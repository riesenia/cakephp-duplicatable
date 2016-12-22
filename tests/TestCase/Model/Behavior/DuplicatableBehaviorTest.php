<?php
namespace Duplicatable\Test\TestCase\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

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
        'plugin.Duplicatable.invoice_types',
        'plugin.Duplicatable.invoices',
        'plugin.Duplicatable.invoice_items',
        'plugin.Duplicatable.invoice_item_properties',
        'plugin.Duplicatable.invoice_item_variations',
        'plugin.Duplicatable.invoices_tags',
        'plugin.Duplicatable.i18n',
        'plugin.Duplicatable.tags'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Invoices = TableRegistry::get('Invoices', [
            'className' => 'TestApp\Model\Table\InvoicesTable'
        ]);
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
        $result = $this->Invoices->duplicate(1);
        $this->assertInstanceOf('Cake\Datasource\EntityInterface', $result);

        $invoice = $this->Invoices->get($result->id, [
            'contain' => [
                'InvoiceItems.InvoiceItemProperties',
                'InvoiceItems.InvoiceItemVariations',
                'Tags'
            ]
        ]);

        // entity
        $this->assertEquals('Invoice name - copy', $invoice->name);
        $this->assertEquals('Contact name', $invoice->contact_name);
        $this->assertEquals(1, $invoice->copied);
        $this->assertEquals(null, $invoice->created);

        // has many
        $this->assertEquals('Item 1', $invoice->items[0]->name);
        $this->assertEquals(null, $invoice->items[0]->created);
        $this->assertEquals('Item 2', $invoice->items[1]->name);
        $this->assertEquals(null, $invoice->items[1]->created);

        // double has many
        $this->assertEquals('NEW Property 1', $invoice->items[0]->invoice_item_properties[0]->name);
        $this->assertEquals('NEW Property 2', $invoice->items[0]->invoice_item_properties[1]->name);
        $this->assertEquals('NEW Property 3', $invoice->items[1]->invoice_item_properties[0]->name);
        $this->assertEquals('Variation 1', $invoice->items[0]->invoice_item_variations[0]->name);
        $this->assertEquals('Variation 2', $invoice->items[1]->invoice_item_variations[0]->name);
        $this->assertEquals('Variation 3', $invoice->items[1]->invoice_item_variations[1]->name);

        // belongs to
        $this->assertEquals(2, $invoice->invoice_type_id);
        $this->assertEquals(2, $this->Invoices->InvoiceTypes->find()->count());

        // belongs to many
        $this->assertEquals(1, $invoice->tags[0]->id);
        $this->assertEquals('Tag 1', $invoice->tags[0]->name);
        $this->assertEquals(2, $invoice->tags[1]->id);
        $this->assertEquals('Tag 2', $invoice->tags[1]->name);

        $this->assertEquals(2, $this->Invoices->Tags->find()->count());
        $this->assertEquals(2, count($this->Invoices->get(1, ['contain' => ['Tags']])->tags));
    }

    /**
     * Test duplicating
     *
     * @return void
     */
    public function testDuplicateEntity()
    {
        $beforeDuplicateInvoices = $this->Invoices->find()->all()->toArray();

        $invoice = $this->Invoices->duplicateEntity(1);

        $invoices = $this->Invoices->find()->all()->toArray();
        $this->assertEquals(count($beforeDuplicateInvoices), count($invoices));

        $this->assertEquals(2, $invoice->invoice_type_id);
        $this->assertEquals('Invoice name - copy', $invoice->name);
        $this->assertEquals('Contact name', $invoice->contact_name);
        $this->assertEquals(1, $invoice->copied);
        $this->assertEquals(null, $invoice->created);

        $this->assertEquals('Item 1', $invoice->items[0]->name);
        $this->assertEquals(null, $invoice->items[0]->created);
        $this->assertEquals('Item 2', $invoice->items[1]->name);
        $this->assertEquals(null, $invoice->items[1]->created);

        $this->assertEquals('NEW Property 1', $invoice->items[0]->invoice_item_properties[0]->name);
        $this->assertEquals('NEW Property 2', $invoice->items[0]->invoice_item_properties[1]->name);
        $this->assertEquals('NEW Property 3', $invoice->items[1]->invoice_item_properties[0]->name);

        $this->assertEquals('Variation 1', $invoice->items[0]->invoice_item_variations[0]->name);
        $this->assertEquals('Variation 2', $invoice->items[1]->invoice_item_variations[0]->name);
        $this->assertEquals('Variation 3', $invoice->items[1]->invoice_item_variations[1]->name);

        $this->assertEquals(1, $invoice->tags[0]->id);
        $this->assertEquals('Tag 1', $invoice->tags[0]->name);
        $this->assertEquals(2, $invoice->tags[1]->id);
        $this->assertEquals('Tag 2', $invoice->tags[1]->name);
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
        $invoice = $this->Invoices->get($new->id, [
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

        $this->assertEquals('NEW Property 1', $invoice->items[0]->invoice_item_properties[0]->name);
        $this->assertEquals('Property 1 - es', $invoice->items[0]->invoice_item_properties[0]->_translations['es']->name);
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
                'name' => function ($entity) {
                    return $entity->name . ' ' . md5($entity->name);
                }
            ]
        ]);
        $new = $this->Invoices->duplicate(1);
        $invoice = $this->Invoices->get($new->id);
        $this->assertEquals('Invoice name 09ceae7acef129ed179da25bed1d8e5e - copy', $invoice->name);

        $this->Invoices->behaviors()->get('Duplicatable')->config([
            'set' => [
                'name' => [$this, 'setModifier']
            ]
        ]);
        $new = $this->Invoices->duplicate(1);
        $invoice = $this->Invoices->get($new->id);
        $this->assertEquals('Invoice name 09ceae7acef129ed179da25bed1d8e5e - copy', $invoice->name);
    }

    /**
     * Modifier method to be used as a callable in the tests
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity being cloned
     * @return string
     */
    public function setModifier($entity)
    {
        return $entity->name . ' ' . md5($entity->name);
    }
}
