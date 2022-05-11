<?php

namespace Marello\Bundle\OrderBundle\EventListener;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;

use Marello\Bundle\OrderBundle\Entity\Order;

class OrderViewListener implements EventSubscriberInterface
{
    const DEFAULT_GRID_BLOCK_PRIORITY = 45;

    /** @var TranslatorInterface $translator */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function onBeforeRender(BeforeViewRenderEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Order) {
            $data = $event->getData();
            $allocations = $event->getTwigEnvironment()->render(
                '@MarelloOrder/Order/allocationsGrid.html.twig',
                [
                    'gridParams' => [
                        'orderId' => $entity->getId(),
                    ]
                ]
            );

            $data['dataBlocks'][] = [
                'title' => $this->translator->trans('marello.order.datablock.allocations'),
                'priority' => self::DEFAULT_GRID_BLOCK_PRIORITY,
                'subblocks' => [['data' => [$allocations]]]
            ];
            $event->setData($data);
        }
    }

    /**
     * @return \string[][]
     */
    public static function getSubscribedEvents()
    {
        return [
            'entity_view.render.before' => ['onBeforeRender']
        ];
    }
}
