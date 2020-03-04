<?php

namespace Marello\Bundle\CustomerBundle\JsTree;

use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\CoreBundle\Tree\Handler\AbstractTreeHandler;

class CompanyTreeHandler extends AbstractTreeHandler
{
    /**
     * @param Company $root
     * @param bool $includeRoot
     * @return array
     */
    protected function getNodes($root, $includeRoot)
    {
        $entities = [];
        if ($includeRoot) {
            $entities[] = $root;
        }
        return array_merge($entities, $this->buildTreeRecursive($root));
    }

    /**
     * @param Company $entity
     * @return array
     */
    protected function formatEntity($entity)
    {
        return [
            'id'     => $entity->getId(),
            'parent' => $entity->getParent() ? $entity->getParent()->getId() : null,
            'text'   => $entity->getName(),
            'state'  => [
                'opened' => !$entity->getChildren()->isEmpty()
            ]
        ];
    }

    /**
     * @param Company $entity
     * @return array
     */
    protected function buildTreeRecursive(Company $entity)
    {
        $entities = [];

        $children = $entity->getChildren();

        foreach ($children->toArray() as $child) {
            $entities[] = $child;

            $entities = array_merge($entities, $this->buildTreeRecursive($child));
        }

        return $entities;
    }

    /**
     * {@inheritdoc}
     */
    protected function moveProcessing($entityId, $parentId, $position)
    {
        throw new \LogicException('Company moving is not supported');
    }
}
