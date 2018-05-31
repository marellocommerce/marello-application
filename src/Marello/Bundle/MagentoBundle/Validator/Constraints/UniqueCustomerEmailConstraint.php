<?php

namespace Marello\Bundle\MagentoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueCustomerEmailConstraint extends Constraint
{
    /**
     * @var string
     */
    public $message = 'marello.magento.unique_customer_email.message';

    /**
     * @var string
     */
    public $transportMessage = 'marello.magento.unique_customer_email.transport_message';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT];
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'marello_magento.validator.unique_customer_email';
    }
}
