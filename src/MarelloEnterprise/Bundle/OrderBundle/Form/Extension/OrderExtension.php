<?php

namespace MarelloEnterprise\Bundle\OrderBundle\Form\Extension;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;

use Oro\Bundle\FormBundle\Utils\FormUtils;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Form\Type\OrderType;
use MarelloEnterprise\Bundle\OrderBundle\Provider\OrderConsolidationProvider;

class OrderExtension extends AbstractTypeExtension
{

    public function __construct(
        protected OrderConsolidationProvider $consolidationProvider
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

        if (!$this->consolidationProvider->isConsolidationFeatureEnabled()) {
            $form->remove('consolidation_enabled');
        }

        if ($this->consolidationProvider->isConsolidationEnabledForOrder($order)) {
            FormUtils::replaceField(
                $form,
                'consolidation_enabled',
                [
                    'data' => 1,
                    'attr' => ['readonly' => true]
                ]
            );
        }
    }
}
