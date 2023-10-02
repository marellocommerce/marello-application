<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Marello\Bundle\PricingBundle\Subtotal\Provider\TotalAwareSubtotalProviderInterface;

class OrderTotalsProvider implements FormChangesProviderInterface
{
    const TOTAL = 'total';
    const SUBTOTALS = 'subtotals';

    /**
     * @var TotalAwareSubtotalProviderInterface
     */
    protected $totalsProvider;

    /**
     * @param TotalAwareSubtotalProviderInterface $totalsProvider
     */
    public function __construct(TotalAwareSubtotalProviderInterface $totalsProvider)
    {
        $this->totalsProvider = $totalsProvider;
    }
    
    /**
     * {@inheritdoc}
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        $form = $context->getForm();
        $order = $form->getData();
        $result = $context->getResult();
        $result['totals'] = $this->getTotalWithSubtotalsValues($order);
        $context->setResult($result);
    }

    /**
     * Calculate and return total with subtotals
     * and with values in base currency converted to Array
     * Used by Orders
     *
     * @param Order $order
     * @return array
     */
    protected function getTotalWithSubtotalsValues(Order $order)
    {
        $subtotals = $this->totalsProvider->getSubtotal($order);
        $total = $this->totalsProvider->getTotal($order, $subtotals);
        
        return [
            self::TOTAL => $total->toArray(),
            self::SUBTOTALS => $subtotals
                ->map(
                    function (Subtotal $subtotal) {
                        return $subtotal->toArray();
                    }
                )
                ->toArray(),
        ];
    }
}
