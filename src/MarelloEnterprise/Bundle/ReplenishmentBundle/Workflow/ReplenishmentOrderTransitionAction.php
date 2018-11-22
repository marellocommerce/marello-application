<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow;

use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;

abstract class ReplenishmentOrderTransitionAction extends AbstractAction
{
    /** @var array */
    protected $options;

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
