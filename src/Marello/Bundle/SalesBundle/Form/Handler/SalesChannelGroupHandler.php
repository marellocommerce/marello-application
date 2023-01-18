<?php

namespace Marello\Bundle\SalesBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SalesChannelGroupHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    public function __construct(
        protected ObjectManager $manager,
        protected AclHelper $aclHelper
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process($data, FormInterface $form, Request $request)
    {
        if (!$data instanceof SalesChannelGroup) {
            throw new \InvalidArgumentException('Argument data should be instance of SalesChannelGroup entity');
        }
        
        $channelsBefore = $data->getSalesChannels()->toArray();
        $form->setData($data);

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($form, $request);

            if ($form->isValid()) {
                $this->onSuccess($data, $channelsBefore);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param SalesChannelGroup $entity
     * @param SalesChannel[] $channelsBefore
     */
    protected function onSuccess(SalesChannelGroup $entity, $channelsBefore)
    {
        $channelsAfter = $entity->getSalesChannels()->toArray();
        $diff = array_filter($channelsBefore, function ($entity) use ($channelsAfter) {
            return !in_array($entity, $channelsAfter);
        });

        $systemGroup = $this->getSystemSalesChannelsGroup();
        /** @var SalesChannel $channel */
        foreach ($diff as $channel) {
            $channel->setGroup($systemGroup);
            $this->manager->persist($channel);
        }
        /** @var SalesChannel $salesChannel */
        foreach ($channelsAfter as $salesChannel) {
            $salesChannel->setGroup($entity);
            $this->manager->persist($salesChannel);
        }
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * @return SalesChannelGroup
     */
    private function getSystemSalesChannelsGroup()
    {
        return $this->manager
            ->getRepository(SalesChannelGroup::class)
            ->findSystemChannelGroup($this->aclHelper);
    }
}
