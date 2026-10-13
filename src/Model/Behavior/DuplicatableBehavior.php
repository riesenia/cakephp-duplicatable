<?php
declare(strict_types=1);

namespace Duplicatable\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Association;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Behavior;
use Cake\ORM\Table;

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
 * - preserveJoinData: if _joinData on BelongsToMany relations should be preserved
 */
class DuplicatableBehavior extends Behavior
{
    /**
     * Default options
     *
     * @var array<string, mixed>
     */
    protected array $defaultConfig = [
        'finder' => 'all',
        'contain' => [],
        'includeTranslations' => false,
        'remove' => [],
        'set' => [],
        'prepend' => [],
        'append' => [],
        'saveOptions' => [],
        'preserveJoinData' => false,
    ];

    /**
     * Duplicate record.
     *
     * @param string|int $id Id of entity to duplicate.
     * @return \Cake\Datasource\EntityInterface|false New entity or false on failure
     */
    public function duplicate(int|string $id): EntityInterface|false
    {
        return $this->table->save(
            $this->duplicateEntity($id),
            $this->getConfig('saveOptions') + ['associated' => $this->getConfig('contain')],
        );
    }

    /**
     * Creates duplicate Entity for given record id without saving it.
     *
     * @param string|int $id Id of entity to duplicate.
     * @return \Cake\Datasource\EntityInterface
     */
    public function duplicateEntity(int|string $id): EntityInterface
    {
        $query = $this->table->find();
        foreach ($this->getFinder() as $finder) {
            $query = $query->find($finder);
        }

        $contain = $this->getContain();

        if ($contain) {
            $query = $query->contain($contain);
        }

        /** @var string $primaryKey */
        $primaryKey = $this->table->getPrimaryKey();

        /** @var \Cake\Datasource\EntityInterface $entity */
        $entity = $query
            ->where([$this->table->getAlias() . '.' . $primaryKey => $id])
            ->firstOrFail();
        $original = clone $entity;

        // process entity
        foreach ($this->getConfig('contain') as $contain) {
            $parts = explode('.', $contain);
            $this->drillDownAssoc($entity, $this->table, $parts);
        }

        $this->modifyEntity($entity, $this->table);

        foreach ($this->getConfig('remove') as $field) {
            $parts = explode('.', $field);
            $this->drillDownEntity('remove', $entity, $original, $parts);
        }

        foreach (['set', 'prepend', 'append'] as $action) {
            foreach ($this->getConfig($action) as $field => $value) {
                $parts = explode('.', $field);
                $this->drillDownEntity($action, $entity, $original, $parts, $value);
            }
        }

        return $entity;
    }

    /**
     * Return finder to use for fetching entities.
     *
     * @param string|null $assocPath Dot separated association path. E.g. Invoices.InvoiceItems
     * @return array
     */
    protected function getFinder(?string $assocPath = null): array
    {
        $finders = $this->getConfig('finder');

        if (!is_array($finders)) {
            $finders = [$finders];
        }

        // for backward compatibility
        if ($this->getConfig('includeTranslations')) {
            $finders[] = 'translations';
        }

        if ($finders === ['all']) {
            return $finders;
        }

        $object = $this->table;
        if ($assocPath !== null) {
            $parts = explode('.', $assocPath);
            foreach ($parts as $prop) {
                /** @var \Cake\ORM\Association $object */
                $object = $object->{$prop};
            }
        }

        $tmp = [];
        foreach ($finders as $finder) {
            if ($object->hasFinder($finder)) {
                $tmp[] = $finder;
            }
        }

        if ($tmp === []) {
            $tmp = ['all'];
        }

        return array_unique($tmp);
    }

    /**
     * Return the contain array modified to use custom finder as required.
     *
     * @return array
     */
    protected function getContain(): array
    {
        $contain = [];
        foreach ($this->getConfig('contain') as $assocPath) {
            $finders = $this->getFinder($assocPath);
            if ($finders === ['all']) {
                $contain[] = $assocPath;
            } else {
                $contain[$assocPath] = function ($query) use ($finders) {
                    foreach ($finders as $finder) {
                        $query->find($finder);
                    }

                    return $query;
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
     * @return void
     */
    protected function modifyEntity(EntityInterface $entity, Table|Association $object): void
    {
        // belongs to many is tricky
        if ($object instanceof BelongsToMany && !$this->getConfig('preserveJoinData')) {
            unset($entity->_joinData);
        } elseif (!$object instanceof BelongsToMany) {
            // unset primary key
            unset($entity->{$object->getPrimaryKey()});

            // unset foreign key
            if ($object instanceof Association) {
                unset($entity->{$object->getPrimaryKey()});
            }
        }

        // set translations as new
        if (!empty($entity->_translations)) {
            foreach ($entity->_translations as $translation) {
                $translation->setNew(true);
            }
        }

        // set as new
        $entity->setNew(true);
    }

    /**
     * Drill down the related properties based on containments and modify each entity.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @param \Cake\ORM\Table|\Cake\ORM\Association $object Table or association instance.
     * @param non-empty-list<string> $parts Related properties chain.
     * @return void
     */
    protected function drillDownAssoc(EntityInterface $entity, Table|Association $object, array $parts): void
    {
        $assocName = array_shift($parts);
        $prop = $object->{$assocName}->getProperty();
        $associated = $entity->{$prop};

        if (!$associated || $object->{$assocName} instanceof BelongsTo) {
            return;
        }

        if ($associated instanceof EntityInterface) {
            $associated = [$associated];
        }

        /** @var array<\Cake\Datasource\EntityInterface> $associated */
        foreach ($associated as $e) {
            if ($parts) {
                $this->drillDownAssoc($e, $object->{$assocName}, $parts);
            }

            if (!$e->isNew()) {
                $this->modifyEntity($e, $object->{$assocName});
            }
        }
    }

    /**
     * Drill down the properties and modify the leaf property.
     *
     * @param string $action Action to perform.
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @param \Cake\Datasource\EntityInterface $original Entity
     * @param non-empty-list<string> $parts Related properties chain.
     * @param mixed|null $value Value to set or use for modification.
     * @return void
     */
    protected function drillDownEntity(
        string $action,
        EntityInterface $entity,
        EntityInterface $original,
        array $parts,
        mixed $value = null,
    ): void {
        $prop = array_shift($parts);
        if (!$parts) {
            $this->doAction($action, $entity, $original, $prop, $value);

            return;
        }

        if ($entity->{$prop} instanceof EntityInterface) {
            $this->drillDownEntity($action, $entity->{$prop}, $original, $parts, $value);

            return;
        }

        if (is_iterable($entity->{$prop})) {
            foreach ($entity->{$prop} as $e) {
                $this->drillDownEntity($action, $e, $original, $parts, $value);
            }
        }
    }

    /**
     * Perform specified action.
     *
     * @param string $action Action to perform.
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @param \Cake\Datasource\EntityInterface $original Entity
     * @param string $prop Property name.
     * @param mixed|null $value Value to set or use for modification.
     * @return void
     */
    protected function doAction(
        string $action,
        EntityInterface $entity,
        EntityInterface $original,
        string $prop,
        mixed $value = null,
    ): void {
        switch ($action) {
            case 'remove':
                $entity->unset($prop);

                if (!empty($entity->_translations)) {
                    foreach ($entity->_translations as &$translation) {
                        $translation->unset($prop);
                    }
                }
                break;

            case 'set':
                if (!is_string($value) && is_callable($value)) {
                    $value = $value($entity, $original);
                }
                $entity->set($prop, $value);

                if (!empty($entity->_translations)) {
                    foreach ($entity->_translations as &$translation) {
                        $translation->set($prop, $value);
                    }
                }
                break;

            case 'prepend':
                $entity->set($prop, $value . $entity->get($prop));

                if (!empty($entity->_translations)) {
                    foreach ($entity->_translations as &$translation) {
                        if (!is_null($translation->get($prop))) {
                            $translation->set($prop, $value . $translation->get($prop));
                        }
                    }
                }
                break;

            case 'append':
                $entity->set($prop, $entity->get($prop) . $value);

                if (!empty($entity->_translations)) {
                    foreach ($entity->_translations as &$translation) {
                        if (!is_null($translation->get($prop))) {
                            $translation->set($prop, $translation->get($prop) . $value);
                        }
                    }
                }
                break;
        }
    }
}
