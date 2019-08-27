<?php

namespace Marello\Bundle\PaymentTermBundle\Provider;

use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class PaymentTermDeletePermissionProvider
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var PaymentTermProvider
     */
    protected $paymentTermProvider;

    /**
     * PaymentTermDeletePermissionProvider constructor.
     * @param DoctrineHelper $doctrineHelper
     * @param PaymentTermProvider $paymentTermProvider
     */
    public function __construct(DoctrineHelper $doctrineHelper, PaymentTermProvider $paymentTermProvider)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->paymentTermProvider = $paymentTermProvider;
    }

    /**
     * @param PaymentTerm $paymentTerm
     * @return bool
     */
    public function isDeleteAllowed(PaymentTerm $paymentTerm)
    {
        return $this->isDefaultPaymentTerm($paymentTerm) === false
            && $this->hasLinkedEntities($paymentTerm) === false
        ;
    }

    /**
     * @param PaymentTerm $paymentTerm
     * @return bool
     */
    protected function isDefaultPaymentTerm(PaymentTerm $paymentTerm)
    {
        /** @var PaymentTerm|null $defaultPaymentTerm */
        $defaultPaymentTerm = $this->paymentTermProvider->getDefaultPaymentTerm();
        return ($defaultPaymentTerm && $paymentTerm->getId() === $defaultPaymentTerm->getId());
    }

    /**
     * @param PaymentTerm $paymentTerm
     * @return bool
     */
    protected function hasLinkedEntities(PaymentTerm $paymentTerm)
    {
        ## TODO check for linked entities e.g. orders, invoices, customers
        return false;
    }
}
