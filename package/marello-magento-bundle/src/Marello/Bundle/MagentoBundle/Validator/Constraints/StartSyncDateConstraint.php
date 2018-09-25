<?php

namespace Marello\Bundle\MagentoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class StartSyncDateConstraint extends Constraint
{
    /**
     * @var string
     */
    public $message = 'marello.magento.start_sync_date.message';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'marello_magento.validator.start_sync_date';
    }
}
