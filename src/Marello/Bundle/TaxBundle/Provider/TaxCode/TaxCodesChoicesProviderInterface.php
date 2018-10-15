<?php

namespace Marello\Bundle\TaxBundle\Provider\TaxCode;

interface TaxCodesChoicesProviderInterface
{
    /**
     * @return array
     */
    public function getTaxCodes();
}
