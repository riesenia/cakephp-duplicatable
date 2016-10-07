# Duplicatable behavior for CakePHP

[![Build Status](https://img.shields.io/travis/riesenia/cakephp-duplicatable/master.svg?style=flat-square)](https://travis-ci.org/riesenia/cakephp-duplicatable)
[![Latest Version](https://img.shields.io/packagist/v/riesenia/cakephp-duplicatable.svg?style=flat-square)](https://packagist.org/packages/riesenia/cakephp-duplicatable)
[![Total Downloads](https://img.shields.io/packagist/dt/riesenia/cakephp-duplicatable.svg?style=flat-square)](https://packagist.org/packages/riesenia/cakephp-duplicatable)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This plugin is for CakePHP 3.x and contains behavior that handles duplicating entities
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

This behavior provides *duplicate* method for the table. Argument is primary key to duplicate.
Configuration options:
* *contain* - set related entities that will be duplicated
* *includeTranslations* - set true to duplicate translations
* *remove* - fields that will be removed from the entity
* *set* - fields that will be set to provided value
* *prepend* - fields that will have value prepended by provided text
* *append* - fields that will have value appended by provided text
* *saveOptions* - options for save on primary table

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
            'set' => ['copied' => true],
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
