<?php

namespace Marello\Bundle\PaymentBundle\Method;

interface PaymentMethodInterface
{
    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return string
     */
    public function getOptionsConfigurationFormType();

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @return array
     */
    public function getOptions();
}
