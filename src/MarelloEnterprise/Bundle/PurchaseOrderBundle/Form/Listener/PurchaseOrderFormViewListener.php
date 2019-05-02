<?php

namespace MarelloEnterprise\Bundle\PurchaseOrderBundle\Form\Listener;

use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;
use Symfony\Component\Translation\TranslatorInterface;

class PurchaseOrderFormViewListener
{
    const WAREHOUSE_BLOCK_NAME = 'warehouse';

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
            'MarelloEnterprisePurchaseOrderBundle:PurchaseOrder:warehouse.html.twig',
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
        $blockLabel = $this->translator->trans('marelloenterprise.purchaseorder.warehouse.delivery_location.label');
        $scrollData->addNamedBlock(self::WAREHOUSE_BLOCK_NAME, $blockLabel, 10);
        $subBlockId = $scrollData->addSubBlock(self::WAREHOUSE_BLOCK_NAME);
        $scrollData->addSubBlockData(self::WAREHOUSE_BLOCK_NAME, $subBlockId, $html, 'warehouse');
    }
}
