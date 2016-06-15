<?php
namespace Duplicatable\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association;
use Cake\Utility\Inflector;

/**
 * Behavior for duplicating entities (including related entities)
 *
 * Configurable options
 * - contain: related entities to duplicate
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
        $entity = $this->_table->get($id, ['contain' => $this->config('contain')]);

        $this->_modifyEntity($entity);

        return $this->_table->save($entity, array_merge($this->config('saveOptions'), ['associated' => $this->config('contain')])) ? $entity->{$this->_table->primaryKey()} : false;
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

                    $entity->{$field} = $value;
                }
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
