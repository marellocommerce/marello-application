<?php

namespace Marello\Bundle\InvoiceBundle\Twig;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Marello\Bundle\PaymentBundle\Entity\Payment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class InvoiceExtension extends AbstractExtension
{
    const NAME = 'marello_invoice';

    public function __construct(
        protected ManagerRegistry $doctrine
    ) {
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'marello_get_payment_source',
                [$this, 'getPaymentSource']
            )
        ];
    }

    /**
     * {@inheritdoc}
     * @param Payment $payment
     * @return AbstractInvoice
     */
    public function getPaymentSource(Payment $payment)
    {
        return $this->doctrine
            ->getManagerForClass(AbstractInvoice::class)
            ->getRepository(AbstractInvoice::class)
            ->findOneByPayment($payment);
    }
}
