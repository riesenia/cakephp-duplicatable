<?php
declare(strict_types=1);

namespace Duplicatable\Model\Table;

use Cake\Datasource\EntityInterface;

/**
 * Provides duplication methods on tables using the Duplicatable behavior.
 *
 * @mixin \Cake\ORM\Table
 */
trait DuplicatableTrait
{
    /**
     * Duplicate and save a record including its configured associations.
     *
     * @param string|int $id Id of the entity to duplicate.
     * @return \Cake\Datasource\EntityInterface|false
     */
    public function duplicate(int|string $id): EntityInterface|false
    {
        /** @var \Duplicatable\Model\Behavior\DuplicatableBehavior $behavior */
        $behavior = $this->getBehavior('Duplicatable');

        return $behavior->duplicate($id);
    }

    /**
     * Create a duplicate entity without saving it.
     *
     * @param string|int $id Id of the entity to duplicate.
     * @return \Cake\Datasource\EntityInterface
     */
    public function duplicateEntity(int|string $id): EntityInterface
    {
        /** @var \Duplicatable\Model\Behavior\DuplicatableBehavior $behavior */
        $behavior = $this->getBehavior('Duplicatable');

        return $behavior->duplicateEntity($id);
    }
}
