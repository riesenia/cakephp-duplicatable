# Duplidraft behavior for CakePHP

This plugin (forked from riesenia/cakephp-duplicatable) is for CakePHP 3.x and contains behavior that handles duplicating entities
including related data.

## Installation

Update *composer.json* file to include this plugin

```json
{
    "require": {
        "riesenia/cakephp-duplicatable": "~1.0"
    }
}
```

Load plugin in *config/bootstrap.php*

```php
Plugin::load('Duplicatable');
```

## Usage

This behavior provides multiple methods for your `Table` objects.

### Method `duplicate`

This behavior provides a `duplicate` method for the table. It takes the primary key of the record to duplicate as its only argument.
Using this method will clone the record defined by the primary key provided as well as all related records as defined in the configuration.

### Method `duplicateEntity`

This behavior provides a `duplicateEntity` method for the table. It mainly acts as the `duplicate` method except it does not save the duplicated record but returns the Entity to be saved instead. This is useful if you need to manipulate the Entity before saving it.

## Configuration options:

* *contain* - set related entities that will be duplicated
* *includeTranslations* - set true to duplicate translations
* *remove* - fields that will be removed from the entity
* *set* - fields that will be set to provided value or callable to modify the value. If you provide a callable, it will take the entity being cloned as the only argument
* *prepend* - fields that will have value prepended by provided text
* *append* - fields that will have value appended by provided text
* *saveOptions* - options for save on primary table

## Examples

```php
class InvoicesTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        // add Duplicatable behavior
        $this->addBehavior('Duplicatable.Duplicatable', [
            // duplicate also items and their properties
            'contain' => ['InvoiceItems.InvoiceItemProperties'],
            // duplicate the translations if TranslateBehavior is loaded (also include related entities translations)
            'includeTranslations' => true,
            // remove created field from both invoice and items
            'remove' => ['created', 'InvoiceItems.created'],
            // mark invoice as copied
            'set' => [
                'name' => function($entity) {
                    return md5($entity->name) . ' ' . $entity->name;
                },
                'copied' => true
            ],
            // prepend properties name
            'prepend' => ['InvoiceItems.InvoiceItemProperties.name' => 'NEW '],
            // append copy to the name
            'append' => ['name' => ' - copy']
        ]);

        // associations (InvoiceItems table hasMany InvoiceItemProperties)
        $this->hasMany('InvoiceItems', [
            'foreignKey' => 'invoice_id',
            'className' => 'InvoiceItems'
        ]);
    }
}

// ... somewhere in the controller
$this->Invoices->duplicate(4);
```
