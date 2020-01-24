<?php

namespace Marello\Bundle\UPSBundle\Connection\Validator;

use Marello\Bundle\UPSBundle\Connection\Validator\Result\UpsConnectionValidatorResultInterface;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;

interface UpsConnectionValidatorInterface
{
    /**
     * @param UPSSettings $transport
     *
     * @return UpsConnectionValidatorResultInterface
     */
    public function validateConnectionByUpsSettings(UPSSettings $transport);
}
