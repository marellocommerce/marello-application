<?php

namespace Marello\Bundle\SalesBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SalesChannelGroupHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, ObjectManager $manager)
    {
        $this->form = $form;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  SalesChannelGroup $entity
     * @param Request $request
     * @return bool True on successful processing, false otherwise
     */
    public function process(SalesChannelGroup $entity, Request $request)
    {
        $channelsBefore = $entity->getSalesChannels()->toArray();
        $this->form->setData($entity);

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity, $channelsBefore);

                return true;
            }
        }

        return false;
    }

    /**
     * Returns form instance
     *
     * @return FormInterface
     */
    public function getFormView()
    {
        $config = $this->form->getConfig();

        /** @var FormInterface $form */
        $form = $config->getFormFactory()->createNamed(
            $this->form->getName(),
            $config->getType()->getName(),
            $this->form->getData()
        );

        return $form->createView();
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
        return $this->manager->getRepository(SalesChannelGroup::class)->findOneBy(['system' => true]);
    }
}
