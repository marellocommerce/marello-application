<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class WarehouseChannelGroupLinkHandler implements FormHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($data, FormInterface $form, Request $request)
    {
        if (!$data instanceof WarehouseChannelGroupLink) {
            throw new \InvalidArgumentException('Argument data should be instance of WarehouseChannelGroupLink entity');
        }
        
        $channelGroupsBefore = $data->getSalesChannelGroups()->toArray();
        $form->setData($data);

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->onSuccess($data, $channelGroupsBefore);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param WarehouseChannelGroupLink $entity
     * @param SalesChannelGroup[] $channelGroupsBefore
     */
    protected function onSuccess(WarehouseChannelGroupLink $entity, $channelGroupsBefore)
    {
        $channelGroupsAfter = $entity->getSalesChannelGroups()->toArray();
        $systemLink = $this->getSystemLink();

        $removeFromSystemLink = array_filter($channelGroupsAfter, function ($entity) use ($channelGroupsBefore) {
            return !in_array($entity, $channelGroupsBefore);
        });
        $addToSystemLink = array_filter($channelGroupsBefore, function ($entity) use ($channelGroupsAfter) {
            return !in_array($entity, $channelGroupsAfter);
        });

        if (!empty($removeFromSystemLink)) {
            /** @var SalesChannelGroup $channelGroup */
            foreach ($removeFromSystemLink as $channelGroup) {
                $systemLink->removeSalesChannelGroup($channelGroup);
            }
            $this->manager->persist($systemLink);
            $this->manager->flush($systemLink);
        }

        $this->manager->persist($entity);
        $this->manager->flush();

        if (!empty($addToSystemLink)) {
            /** @var SalesChannelGroup $channelGroup */
            foreach ($addToSystemLink as $channelGroup) {
                $systemLink->addSalesChannelGroup($channelGroup);
            }
            $this->manager->persist($systemLink);
            $this->manager->flush();
        }
    }

    /**
     * @return WarehouseChannelGroupLink
     */
    private function getSystemLink()
    {
        return $this->manager
            ->getRepository(WarehouseChannelGroupLink::class)
            ->findSystemLink();
    }
}
