<?php

namespace Marello\Bundle\RefundBundle\Workflow\Actions;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;
use Oro\Component\ConfigExpression\ContextAccessor;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\OrderBundle\Entity\Order;

class CreateRefundAction extends AbstractAction
{
    /** @var array */
    protected $options;

    public function __construct(
        ContextAccessor $contextAccessor,
        protected ManagerRegistry $doctrine
    ) {
        parent::__construct($contextAccessor);
    }

    /**
     * @param mixed $context
     */
    protected function executeAction($context)
    {
        /** @var ReturnEntity|Order $creditedEntity */
        $creditedEntity = $context->getEntity();
        if ($creditedEntity instanceof ReturnEntity) {
            $refund = Refund::fromReturn($creditedEntity);
        } else {
            $refund = Refund::fromOrder($creditedEntity);
        }

        $this->doctrine->getManager()->persist($refund);
        $this->doctrine->getManager()->flush();
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
