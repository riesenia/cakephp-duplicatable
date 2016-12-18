<?php
namespace Duplidraft\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association;
use Cake\Utility\Inflector;
use Cake\ORM\TableRegistry;

/**
 * Behavior for duplicating entities (including related entities)
 *
 * Configurable options
 * - contain: related entities to duplicate
 * - includeTranslations: set true to duplicate translations
 * - remove: fields to remove
 * - set: fields and their default value
 * - prepend: fields and text to prepend
 * - append: fields and text to append
 */
class DuplicatableBehavior extends Behavior
{
    /**
     * Default options
     *
     * @var array
     */
    protected $_defaultConfig = [
        'contain' => [],
        'includeTranslations' => false,
        'remove' => [],
        'set' => [],
        'prepend' => [],
        'append' => [],
        'saveOptions' => []
    ];

    /**
     * Duplicate
     *
     * @param mixed id of duplicated entity
     * @return mixed id of new entity or false on failure
     */
    public function duplicate($id)
    {
        $entity = $this->duplicateEntity($id);

        return $this->_table->save($entity, array_merge($this->config('saveOptions'), ['associated' => $this->config('contain')])) ? $entity->{$this->_table->primaryKey()} : false;
    }

    /**
     * Duplicate a record and returns the Entity without saving it.
     *
     * @param mixed id of duplicated entity
     * @return mixed id of new entity or false on failure
     */
    public function duplicateEntity($id)
    {
        $entity = $this->_table->get($id, [
            'contain' => $this->_getContain(),
            'finder' => $this->_includeTranslation($this->_table->alias()) ? 'translations' : null,
        ]);

        $this->_modifyEntity($entity);

        return $entity;
    }

    /**
     * Check if translations must be included in an entity
     *
     * @param string $tableName support dot notation for contain table names. E.g. Invoices.InvoiceItems
     * @return array
     */
    protected function _includeTranslation($tableName)
    {
        $tableNameParts = explode('.', $tableName);

        return ($this->config('includeTranslations') && TableRegistry::get(end($tableNameParts))->behaviors()->has('Translate'));
    }

    /**
     * Return the contain array for the get method
     *
     * @return array
     */
    protected function _getContain()
    {
        $contain = [];
        foreach ($this->config('contain') as $table) {
            if ($this->_includeTranslation($table)) {
                $contain[$table] = function ($query) {
                    return $query->find('translations');
                };
            } else {
                $contain[] = $table;
            }
        }

        return $contain;
    }

    /**
     * Modify entity
     *
     * @param \Cake\Datasource\EntityInterface entity
     * @param \Cake\ORM\Association table
     * @param string path prefix
     * @return void
     */
    protected function _modifyEntity(EntityInterface $entity, Association $table = null, $pathPrefix = '')
    {
        if (is_null($table)) {
            $table = $this->_table;
        }

        // set / prepend / append
        foreach (['set', 'prepend', 'append'] as $action) {
            foreach ($this->config($action) as $field => $value) {
                $field = $this->_fieldByPath($field, $pathPrefix);

                if ($field) {
                    if ($action == 'prepend') {
                        $value .= $entity->{$field};
                    }

                    if ($action == 'append') {
                        $value = $entity->{$field} . $value;
                    }

                    if ($action == 'set') {
                        if (is_callable($value)) {
                            $value = $value($entity);
                        }
                    }

                    $entity->{$field} = $value;
                }
            }
        }

        // unset primary key
        unset($entity->{$table->primaryKey()});

        // unset foreign key
        if ($table instanceof Association) {
            unset($entity->{$table->foreignKey()});
        }

        // unset configured
        foreach ($this->config('remove') as $field) {
            $field = $this->_fieldByPath($field, $pathPrefix);

            if ($field) {
                unset($entity->{$field});
            }
        }



        // set translations as new
        if (!empty($entity->_translations)) {
            foreach ($entity->_translations as $translation) {
                $translation->isNew(true);
            }
        }

        // set as new
        $entity->isNew(true);

        // modify related entities
        foreach ($this->config('contain') as $contain) {
            if (preg_match('/^' . preg_quote($pathPrefix, '/') . '([^.]+)/', $contain, $matches)) {
                foreach ($entity->{Inflector::tableize($matches[1])} as $related) {
                    if ($related->isNew()) {
                        continue;
                    }

                    $this->_modifyEntity($related, $table->{$matches[1]}, $pathPrefix . $matches[1] . '.');
                }
            }
        }
    }

    /**
     * Return field matching path prefix or false if in the scope
     *
     * @param string field
     * @param string path prefix
     * @return string|bool
     */
    protected function _fieldByPath($field, $pathPrefix)
    {
        if (!$pathPrefix) {
            return $field;
        }

        return strpos($field, $pathPrefix) === 0 ? substr($field, strlen($pathPrefix)) : false;
    }
}
