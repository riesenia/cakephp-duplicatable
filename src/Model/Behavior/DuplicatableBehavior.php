<?php
namespace Duplicatable\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Association;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Behavior;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * Behavior for duplicating entities (including related entities)
 *
 * Configurable options:
 * - finder: Finder to use. Defaults to 'all'.
 * - contain: related entities to duplicate
 * - includeTranslations: set true to duplicate translations.
 *   This option is deprecated, instead set "finder" to "translations".
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
        'finder' => 'all',
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
            'finder' => $this->_getFinder(),
        ]);

        $this->_modifyEntity($entity, $this->_table);

        return $entity;
    }

    /**
     * Return finder to use for fetching entities.
     *
     * @param string|null $assocPath Dot separated association path. E.g. Invoices.InvoiceItems
     * @return string
     */
    protected function _getFinder($assocPath = null)
    {
        $finder = $this->config('finder');
        if ($this->config('includeTranslations')) {
            $finder = 'translations';
        }

        if ($finder === 'all') {
            return $finder;
        }

        $object = $this->_table;
        if ($assocPath) {
            $parts = explode('.', $assocPath);
            foreach ($parts as $prop) {
                $object = $object->{$prop};
            }
        }

        if (!$object->hasFinder($finder)) {
            $finder = 'all';
        }

        return $finder;
    }

    /**
     * Return the contain array modified to use custom finder as required.
     *
     * @return array
     */
    protected function _getContain()
    {
        $contain = [];
        foreach ($this->config('contain') as $assocPath) {
            $finder = $this->_getFinder($assocPath);
            if ($finder === 'all') {
                $contain[] = $assocPath;
            } else {
                $contain[$assocPath] = function ($query) use ($finder) {
                    return $query->find($finder);
                };
            }
        }

        return $contain;
    }

    /**
     * Modify entity
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @param \Cake\ORM\Table|\Cake\ORM\Association $object Table or association instance.
     * @param string $pathPrefix Path prefix
     * @return void
     */
    protected function _modifyEntity(EntityInterface $entity, $object, $pathPrefix = '')
    {
        // belongs to many is tricky
        if ($object instanceof BelongsToMany) {
            unset($entity->_joinData);
        } else {
            // unset primary key
            unset($entity->{$object->primaryKey()});

            // unset foreign key
            if ($object instanceof Association) {
                unset($entity->{$object->foreignKey()});
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
                $propertyName = $object->{$assocName}->property();

                foreach ($entity->{$propertyName} as $related) {
                    if ($related->isNew()) {
                        continue;
                    }

                    $this->_modifyEntity($related, $object->{$assocName}, $pathPrefix . $assocName . '.');
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
