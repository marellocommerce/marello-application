<?php

namespace Marello\Bundle\PaymentBundle\Context;

interface PaymentContextFactoryInterface
{
    /**
     * @param object $entity
     *
     * @return PaymentContextInterface[]
     */
    public function create($entity);
}
