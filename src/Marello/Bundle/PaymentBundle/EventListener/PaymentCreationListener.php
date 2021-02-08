<?php

namespace Marello\Bundle\PaymentBundle\EventListener;

use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PaymentBundle\Entity\Payment;
use Marello\Bundle\PaymentBundle\Migrations\Data\ORM\LoadPaymentStatusData;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Component\Action\Event\ExtendableActionEvent;

class PaymentCreationListener
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param ExtendableActionEvent $event
     */
    public function onOrderPaid(ExtendableActionEvent $event)
    {
        if (!$this->isCorrectOrderContext($event->getContext())) {
            return;
        }
        $data = $event->getContext()->getData();
        /** @var Order $entity */
        $entity = $data->get('order');
        $entityManager = $this->doctrineHelper->getEntityManagerForClass(Invoice::class);
        /** @var Invoice $invoice */
        $invoice = $entityManager
            ->getRepository(Invoice::class)
            ->findOneBy(['order' => $entity]);
        if ($invoice) {
            $totalPaid = $data->get('total_paid');
            if ($totalPaid instanceof Price) {
                $totalPaid = $totalPaid->getValue();
            }
            $payment = new Payment();
            $payment
                ->setPaymentMethod($entity->getPaymentMethod())
                ->setPaymentMethodOptions($entity->getPaymentMethodOptions())
                ->setPaymentReference($data->get('payment_reference'))
                ->setPaymentDetails($data->get('payment_details'))
                ->setTotalPaid($totalPaid)
                ->setCurrency($entity->getCurrency() ? : $invoice->getCurrency())
                ->setPaymentDate(new \DateTime('now', new \DateTimeZone('UTC')))
                ->setOrganization($entity->getOrganization())
                ->setStatus($this->findStatusByName(LoadPaymentStatusData::ASSIGNED));

            $invoice->addPayment($payment);
            $entityManager->persist($invoice);
            $entityManager->flush();
        }
    }

    /**
     * @param mixed $context
     * @return bool
     */
    protected function isCorrectOrderContext($context)
    {
        return ($context instanceof WorkflowItem
            && $context->getData() instanceof WorkflowData
            && $context->getData()->has('order')
            && $context->getData()->get('order') instanceof Order
        );
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
        $status = $this->doctrineHelper
            ->getEntityManagerForClass($statusClass)
            ->getRepository($statusClass)
            ->find($name);

        if ($status) {
            return $status;
        }

        return null;
    }
}
