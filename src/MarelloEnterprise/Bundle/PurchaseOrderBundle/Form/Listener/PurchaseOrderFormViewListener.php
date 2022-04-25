<?php

namespace MarelloEnterprise\Bundle\PurchaseOrderBundle\Form\Listener;

use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;
use Symfony\Contracts\Translation\TranslatorInterface;

class PurchaseOrderFormViewListener
{
    const WAREHOUSE_BLOCK_NAME = 'warehouse';
    const BILLING_SHIPPING_BLOCK_NAME = 'billing_shipping';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param BeforeListRenderEvent $event
     */
    public function onPurchaseOrderCreateStepTwo(BeforeListRenderEvent $event)
    {
        $template = $event->getEnvironment()->render(
            '@MarelloEnterprisePurchaseOrder/PurchaseOrder/warehouse.html.twig',
            ['form' => $event->getFormView()]
        );
        $this->addWarehouseBlock($event->getScrollData(), $template);
    }

    /**
     * @param ScrollData $scrollData
     * @param string $html
     */
    protected function addWarehouseBlock(ScrollData $scrollData, $html)
    {
        if ($scrollData->hasBlock(self::BILLING_SHIPPING_BLOCK_NAME)) {
            $scrollData->addSubBlockData(self::BILLING_SHIPPING_BLOCK_NAME, 0, $html, 'warehouse');
        }
    }
}
