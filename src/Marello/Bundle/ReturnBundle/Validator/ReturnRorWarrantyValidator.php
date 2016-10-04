<?php

namespace Marello\Bundle\ReturnBundle\Validator;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Util\ReturnHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReturnRorWarrantyValidator extends ConstraintValidator
{
    protected $warrantyReason = 'warranty';

    /** @var ReturnHelper $returnHelper */
    protected $configManager;

    /**
     * ReturnRorWarrantyValidator constructor.
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof ReturnEntity) {
            return;
        }

        $returnItems = $value->getReturnItems();
        /*
        * Get items which should be validated for the Ror constraint
        */
        $items = [];
        $returnItems->map(function (ReturnItem $returnItem) use (&$items) {
            if ($returnItem->getReason() !== $this->warrantyReason) {
                $items[] = $returnItem;
            }
        });

        /**
         * no items to validate for the Right of Return constraint
         */
        if (count($items) <= 0) {
            return;
        }

        /** @var Order $order */
        $order          = $value->getOrder();
        $orderCreatedAt = $order->getCreatedAt();
        $currentDate    = new \DateTime(date('Y-m-d H:i:s'));

        $interval           = $currentDate->diff($orderCreatedAt);
        $rorPeriodInDays    = $this->configManager->get('marello_return.ror_period');

        if ($interval > $rorPeriodInDays) {
            $this->context->buildViolation($constraint->message)
                ->atPath('returnItems')
                ->addViolation();
        }
    }
}
