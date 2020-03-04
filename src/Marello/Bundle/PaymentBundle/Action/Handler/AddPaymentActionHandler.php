<?php

namespace Marello\Bundle\PaymentBundle\Action\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Marello\Bundle\InvoiceBundle\Provider\InvoicePaidAmountProvider;
use Marello\Bundle\PaymentBundle\Entity\Payment;
use Marello\Bundle\PaymentBundle\Migrations\Data\ORM\LoadPaymentStatusData;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class AddPaymentActionHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var InvoicePaidAmountProvider
     */
    private $invoicePaidAmountProvider;

    /**
     * @param EntityManagerInterface $entityManager
     * @param InvoicePaidAmountProvider $invoicePaidAmountProvider
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        InvoicePaidAmountProvider $invoicePaidAmountProvider
    ) {
        $this->entityManager = $entityManager;
        $this->invoicePaidAmountProvider = $invoicePaidAmountProvider;
    }

    /**
     * @param AbstractInvoice $entity
     * @param string $paymentMethod
     * @param \DateTime $paymentDate
     * @param string $paymentReference
     * @param string $paymentDetails
     * @param float $paidTotal
     * @return array
     */
    public function handleAction(
        AbstractInvoice $entity,
        $paymentMethod,
        \DateTime $paymentDate,
        $paymentReference,
        $paymentDetails,
        $paidTotal
    ) {
        $paidTotalBefore = $this->invoicePaidAmountProvider->getPaidAmount($entity);
        $paidTotalAfter = $paidTotalBefore + $paidTotal;
        if ($paidTotalAfter > $entity->getGrandTotal()) {
            return [
                'type' => 'error',
                'message' => 'marello.payment.message.add_payment.error.paid_total_exceed'
            ];
        }
        $payment = new Payment();
        $payment
            ->setPaymentMethod($paymentMethod)
            ->setPaymentDate($paymentDate)
            ->setPaymentReference($paymentReference)
            ->setPaymentDetails($paymentDetails)
            ->setTotalPaid($paidTotal)
            ->setCurrency($entity->getCurrency())
            ->setStatus($this->findStatusByName(LoadPaymentStatusData::ASSIGNED))
            ->setOrganization($entity->getOrganization());
        $order = $entity->getOrder();
        if ($order && $paymentMethod === $order->getPaymentMethod()) {
            $payment->setPaymentMethodOptions($order->getPaymentMethodOptions());
        }
        $entity->addPayment($payment);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return [
            'type' => 'success',
            'message' => 'marello.payment.message.add_payment.success'
        ];
    }

    /**
     * @param string $name
     * @return null|object
     */
    private function findStatusByName($name)
    {
        $statusClass = ExtendHelper::buildEnumValueClassName(
            LoadPaymentStatusData::PAYMENT_STATUS_ENUM_CLASS
        );
        $status = $this->entityManager
            ->getRepository($statusClass)
            ->find($name);

        if ($status) {
            return $status;
        }

        return null;
    }
}