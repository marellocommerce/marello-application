<?php

namespace Marello\Bundle\TaskBundle\EventListener;

use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskViewListener
{
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

    public function onEdit(BeforeListRenderEvent $event)
    {
        $template = $event->getEnvironment()->render(
            '@MarelloTask/Task/type_and_assigned_to_form.html.twig',
            ['form' => $event->getFormView()]
        );

        $scrollData = $event->getScrollData();
        $data = $scrollData->getData();

        // Set our new fields after owner field
        $fields = $data[ScrollData::DATA_BLOCKS][0][ScrollData::SUB_BLOCKS][0][ScrollData::DATA];
        $newFields = array_merge(
            array_slice($fields, 0, 6),
            [$template],
            array_slice($fields, 6, count($fields))
        );
        $data[ScrollData::DATA_BLOCKS][0][ScrollData::SUB_BLOCKS][0][ScrollData::DATA] = $newFields;
        $scrollData->setData($data);
    }
}
