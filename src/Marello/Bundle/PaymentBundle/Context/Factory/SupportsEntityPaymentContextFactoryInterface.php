<?php

namespace Marello\Bundle\PaymentBundle\Context\Factory;

use Marello\Bundle\PaymentBundle\Context\Factory\Exception\UnsupportedEntityException;
use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;

interface SupportsEntityPaymentContextFactoryInterface
{
    /**
     * @param string $entityClass
     * @param int $entityId
     *
     * @throws UnsupportedEntityException
     *
     * @return PaymentContextInterface
     */
    public function create($entityClass, $entityId);

    /**
     * @param string $entityClass
     * @param int $entityId
     *
     * @return bool
     */
    public function supports($entityClass, $entityId);
}
