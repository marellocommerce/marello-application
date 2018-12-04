<?php

namespace Marello\Bundle\InvoiceBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Marello\Bundle\InvoiceBundle\Manager\InvoiceManager;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class AutoInvoiceCreationListener
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var InvoiceManager
     */
    protected $invoiceManager;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @param ConfigManager $configManager
     * @param InvoiceManager $invoiceManager
     */
    public function __construct(
        ConfigManager $configManager,
        InvoiceManager $invoiceManager
    )
    {
        $this->configManager = $configManager;
        $this->invoiceManager = $invoiceManager;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em  = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $insertions = $uow->getScheduledEntityInsertions();

        $orderInsertions = array_filter($insertions, function ($entity) {
            return $entity instanceof Order;
        });
        if ($this->order === null) {
            $this->order = !empty($orderInsertions) ? reset($orderInsertions) : null;
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->order === null) {
            return;
        }
        $order = $this->order;
        $this->order = null;
        if ($this->configManager->get('marello_invoice.auto_invoicing')) {
            $em  = $args->getEntityManager();
            $order = $em->getRepository(Order::class)->find($order->getId());
            $this->invoiceManager->createInvoice($order);
        }
    }
}