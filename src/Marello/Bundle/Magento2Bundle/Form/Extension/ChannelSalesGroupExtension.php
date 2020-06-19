<?php

namespace Marello\Bundle\Magento2Bundle\Form\Extension;

use Marello\Bundle\Magento2Bundle\Form\Type\TransportSettingFormType;
use Marello\Bundle\Magento2Bundle\Provider\Magento2ChannelType;
use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelGroupRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelGroupSelectType;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
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
            FormEvents::PRE_SUBMIT,
            array($this, 'onPreSubmit')
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            array($this, 'onPostSubmit')
        );
    }

    /**
     * Case when integration exists in DB
     *
     * @param FormEvent $event
     */
    public function addSalesChannelGroupField(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data ||
            ($data instanceof Channel && $data->getType() !== Magento2ChannelType::TYPE)) {
            return;
        }

        $this->addSalesGroupField($event->getForm(), $data);
    }

    /**
     * Case when user switch type of integration
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $type = $data['type'] ?? false;
        if ($type === Magento2ChannelType::TYPE && !$form->has('salesGroup')) {
            $this->addSalesGroupField($form, $form->getData());

            return;
        }

        if ($form->has('salesGroup')) {
            $form->remove('salesGroup');
        }
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
        if ($data->getType() !== Magento2ChannelType::TYPE) {
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

    /**
     * @param FormInterface $form
     * @param Channel $channel
     */
    protected function addSalesGroupField(FormInterface $form, Channel $channel)
    {
        $currentSalesChannelGroup = null;
        if ($channel->getId()) {
            $currentSalesChannelGroup = $this->salesChannelGroupRepository->findSalesChannelGroupAttachedToIntegration(
                $channel
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
                'disabled' => $currentSalesChannelGroup !== null,
                'constraints' => [
                    new NotBlank()
                ],
                'attr' => ['data-role' => TransportSettingFormType::ELEMENT_DATA_ROLE_SALES_CHANNEL_GROUP]
            ]
        );
    }
}
