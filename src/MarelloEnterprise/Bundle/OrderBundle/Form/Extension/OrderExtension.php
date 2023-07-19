<?php

namespace MarelloEnterprise\Bundle\OrderBundle\Form\Extension;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Form\Type\OrderType;

class OrderExtension extends AbstractTypeExtension
{
    public function __construct(
        protected ConfigManager $configManager
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [OrderType::class];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->addEventListener(
                FormEvents::POST_SET_DATA,
                [$this, 'postSetDataListener']
            );
    }

    /**
     * @param FormEvent $event
     */
    public function postSetDataListener(FormEvent $event)
    {
        /** @var Order $order */
        $order = $event->getData();
        $form = $event->getForm();
        $featureEnabled = $this->configManager->get('marello_enterprise_order.enable_order_consolidation');

        if (!$featureEnabled) {
            $form->remove('consolidation_enabled');
        }

        $consolidationEnabled = $this->configManager->get(
            'marello_enterprise_order.set_consolidation_for_scope',
            false,
            false,
            $order->getSalesChannel()
        );

        $consolidationEnabledSystem = $this->configManager
            ->get('marello_enterprise_order.set_consolidation_for_scope');
        if (($consolidationEnabled || $consolidationEnabledSystem) && $featureEnabled) {
            FormUtils::replaceField(
                $form,
                'consolidation_enabled',
                [
                    'data' => 1,
                    'attr' => ['readonly' => true]
                ]);
        }
    }
}
