<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow;

use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class ReplenishmentOrderTransitionAction extends AbstractAction
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param ContextAccessor           $contextAccessor
     * @param EventDispatcherInterface  $eventDispatcher
     * @param TranslatorInterface       $translator
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator
    ) {
        parent::__construct($contextAccessor);

        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
    }

    /**
     * Initialize action based on passed options.
     *
     * @param array $options
     *
     * @return ActionInterface
     * @throws InvalidParameterException
     */
    public function initialize(array $options)
    {
        $this->options = $options;

        return $this;
    }
}
