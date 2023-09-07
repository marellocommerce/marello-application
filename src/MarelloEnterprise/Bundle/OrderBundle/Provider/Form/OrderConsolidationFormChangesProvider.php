<?php

namespace MarelloEnterprise\Bundle\OrderBundle\Provider\Form;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use MarelloEnterprise\Bundle\OrderBundle\Provider\OrderConsolidationProvider;

class OrderConsolidationFormChangesProvider implements FormChangesProviderInterface
{
    const CONSOLIDATION_FIELD = 'consolidation_enabled';

    public function __construct(
        protected OrderConsolidationProvider $consolidationProvider
    ) {
    }

    /**
     * {@inheritdoc}
     */
    /**
     * {@inheritdoc}
     * @param FormChangeContextInterface $context
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        $form = $context->getForm();
        $order = $form->getData();
        if ($order instanceof Order) {
            $submittedData = $context->getSubmittedData();
            if (!array_key_exists(self::CONSOLIDATION_FIELD, $submittedData)) {
                return;
            }

            $result = $context->getResult();
            $result[self::CONSOLIDATION_FIELD] = (int)$this->consolidationProvider->isConsolidationEnabledForOrder($order);
            $context->setResult($result);
        }
    }
}
