<?php

namespace Marello\Bundle\CoreBundle\Tree\Tests\Unit\Stubs\Handler;

use Marello\Bundle\CoreBundle\Tree\Handler\AbstractTreeHandler;
use Marello\Bundle\CoreBundle\Tree\Tests\Unit\Stubs\EntityStub;

class TreeHandlerStub extends AbstractTreeHandler
{
    /**
     * {@inheritdoc}
     */
    protected function moveProcessing($entityId, $parentId, $position)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param EntityStub $entity
     */
    protected function formatEntity($entity)
    {
        return [
            'id'     => $entity->id,
            'parent' => $entity->parent,
            'text'   => $entity->text,
        ];
    }
}
