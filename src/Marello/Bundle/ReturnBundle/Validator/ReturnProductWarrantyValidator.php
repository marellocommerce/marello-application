<?php

namespace Marello\Bundle\ReturnBundle\Validator;

use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\ReturnBundle\Util\ReturnHelper;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReturnProductWarrantyValidator extends ConstraintValidator
{
    protected $warrantyReason = 'warranty';

    /** @var ConfigManager $configManager */
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

        $returnItems    = $value->getReturnItems();

        /*
         * Get items which should be validated for the Ror constraint
         */
        $items = [];
        $returnItems->map(function (ReturnItem $returnItem) use (&$items) {
            if ($returnItem->getReason() === $this->warrantyReason) {
                $items[] = $returnItem->getOrderItem()->getProduct();
            }
        });

        /**
         * no items to validate for the Warranty constraint
         */
        if (count($items) <= 0) {
            return;
        }

        /** @var Order $order */
        $order          = $value->getOrder();
        $orderCreatedAt = $order->getCreatedAt()->format('Y-m-d');
        $currentDate    = new \DateTime(date('Y-m-d'));

        /**
         * interval in days
         * @var \DateInterval $interval
         */
        $interval           = $currentDate->diff($orderCreatedAt);
        // take in account that months portion of interval cannot be greater than 12
        // so add the year into the equation
        $intervalInMonths   = ($interval->m + ($interval->y * 12));

        $systemWarrantyInMonths   = $this->configManager->get('marello_return.warranty_period');
        $errors = [];
        foreach ($items as $product) {
            $productWarrantyInMonths = $product->getWarranty();
            if (!$productWarrantyInMonths) {
                $productWarrantyInMonths = $systemWarrantyInMonths;
            }

            if ($intervalInMonths > $productWarrantyInMonths) {
                $errors[] = true;
            }
        }

        /*
         * If there are products which are not allowed to be returned, create constraint violation.
         */
        if (count($errors) > 0) {
            $this->context->buildViolation($constraint->message)
                ->atPath('returnItems')
                ->addViolation();
        }
    }
}
