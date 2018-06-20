<?php

namespace Marello\Bundle\MagentoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Email;

class EmailAddressListConstraint extends Email
{
    /**
     * @var string
     */
    public $message = 'marello.magento.invalid_email_address.message';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'marello_magento.validator.email_address_list';
    }
}
