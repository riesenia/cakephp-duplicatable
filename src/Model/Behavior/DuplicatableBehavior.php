<?php
namespace Duplicatable\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Association;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Behavior;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

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
     * Duplicate record.
     *
     * @param int|string $id Id of entity to duplicate.
     * @return \Cake\Datasource\EntityInterface New entity or false on failure
     */
    public function duplicate($id)
    {
        $entity = $this->duplicateEntity($id);
        $config = $this->config();

        return $this->_table->save(
            $entity,
            $config['saveOptions'] + ['associated' => $config['contain']]
        );
    }

    /**
     * Creates duplicate Entity for given record id without saving it.
     *
     * @param int|string $id Id of entity to duplicate.
     * @return \Cake\Datasource\EntityInterface
     */
    public function duplicateEntity($id)
    {
        $entity = $this->_table->get($id, [
            'contain' => $this->_getContain(),
            'finder' => $this->_includeTranslation($this->_table->alias()) ? 'translations' : 'all',
        ]);

        $this->_modifyEntity($entity);

        return $entity;
    }

    /**
     * Check if translations must be included in an entity
     *
     * @param string $tableName support dot notation for contain table names. E.g. Invoices.InvoiceItems
     * @return bool
     */
    protected function _includeTranslation($tableName)
    {
        if (!$this->config('includeTranslations')) {
            return false;
        }

        $tableNameParts = explode('.', $tableName);
        $table = TableRegistry::get(end($tableNameParts));

        return $table->behaviors()->hasFinder('translations');
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
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @param \Cake\ORM\Association $table Association
     * @param string $pathPrefix Path prefix
     * @return void
     */
    protected function _modifyEntity(EntityInterface $entity, Association $table = null, $pathPrefix = '')
    {
        if (is_null($table)) {
            $table = $this->_table;
        }

        // belongs to many is tricky
        if ($table instanceof BelongsToMany) {
            unset($entity->_joinData);
        } else {
            // unset primary key
            unset($entity->{$table->primaryKey()});

            // unset foreign key
            if ($table instanceof Association) {
                unset($entity->{$table->foreignKey()});
            }
        }

        // unset configured
        foreach ($this->config('remove') as $field) {
            $field = $this->_fieldByPath($field, $pathPrefix);

            if ($field) {
                unset($entity->{$field});
            }
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
                $assocName = $matches[1];
                $propertyName = $table->{$assocName}->property();

                foreach ($entity->{$propertyName} as $related) {
                    if ($related->isNew()) {
                        continue;
                    }

                    $this->_modifyEntity($related, $table->{$assocName}, $pathPrefix . $assocName . '.');
                }
            }
        }
    }

    /**
     * Return field matching path prefix or false if in the scope
     *
     * @param string $field Field
     * @param string $pathPrefix Path prefix
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
