<?php

namespace Marello\Bundle\Magento2Bundle\Form\Extension;

use Marello\Bundle\Magento2Bundle\Provider\Magento2ChannelType;
use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelGroupRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelGroupSelectType;
use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Adds field salesChannelGroup to Integration form
 */
class ChannelSalesGroupExtension extends AbstractTypeExtension
{
    /**
     * @var SalesChannelGroupRepository
     */
    protected $salesChannelGroupRepository;

    /**
     * @param SalesChannelGroupRepository $salesChannelGroupRepository
     */
    public function __construct(SalesChannelGroupRepository $salesChannelGroupRepository)
    {
        $this->salesChannelGroupRepository = $salesChannelGroupRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            array($this, 'addSalesChannelGroupField')
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            array($this, 'onPostSubmit')
        );
    }

    /**
     * @param FormEvent $event
     */
    public function addSalesChannelGroupField(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (!$data || $data->getType() !== Magento2ChannelType::TYPE) {
            return;
        }

        $currentSalesChannelGroup = null;
        if ($data->getId()) {
            $currentSalesChannelGroup = $this->salesChannelGroupRepository->findSalesChannelGroupAttachedToIntegration(
                $data
            );
        }

        $form->add(
            'salesGroup',
            SalesChannelGroupSelectType::class,
            [
                'autocomplete_alias' => 'magento2_group_saleschannels',
                'grid_name' => 'marello-magento2-sales-channel-groups',
                'mapped' => false,
                'required' => true,
                'data' => $currentSalesChannelGroup,
                'disabled' => $data->getId() !== null,
                'constraints' => [
                    new NotBlank()
                ]
            ]
        );
    }

    /**
     * Saves sales channel group
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (!$data || $data->getType() !== Magento2ChannelType::TYPE) {
            return;
        }

        /**
         * Disable of changing salesChannelGroup once it was created
         */
        if ($data->getId()) {
            return;
        }

        $salesGroup = $form->get('salesGroup')->getData();
        if (!$salesGroup instanceof SalesChannelGroup) {
            return;
        }

        $salesGroup->setIntegrationChannel($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ChannelType::class;
    }
}
