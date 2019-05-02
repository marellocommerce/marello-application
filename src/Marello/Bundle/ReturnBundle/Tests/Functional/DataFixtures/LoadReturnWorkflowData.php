<?php

namespace Marello\Bundle\ReturnBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;

class LoadReturnWorkflowData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadReturnData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $returns = $manager->getRepository(ReturnEntity::class)->findAll();
        $workflowItemRepo = $manager->getRepository(WorkflowItem::class);
        $i = 0;
        foreach ($returns as $return) {
            $workflowItems = $workflowItemRepo->findBy(['entityId' => $return->getId()]);
            /** @var WorkflowItem $workflowItem */
            $workflowItem = array_shift($workflowItems);
            $this->setReference(sprintf('marello_return_workflow_item_%s', $i++), $workflowItem);
        }
    }
}
