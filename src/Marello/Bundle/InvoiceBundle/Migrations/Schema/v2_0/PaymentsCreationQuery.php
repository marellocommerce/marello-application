<?php

namespace Marello\Bundle\InvoiceBundle\Migrations\Schema\v2_0;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class PaymentsCreationQuery extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->createPayments($logger, true);

        return $logger->getMessages();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->createPayments($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function createPayments(LoggerInterface $logger, $dryRun = false)
    {
        $invoices = $this->loadInvoices($logger);
        foreach ($invoices as $invoice) {
            $query  = "
INSERT INTO marello_payment_payment 
(payment_method, payment_reference, payment_details, total_paid, organization_id, payment_date, created_at)
VALUES 
('" . $invoice['payment_method'] . "', '" . $invoice['payment_reference'] . "', '" .$invoice['payment_details'] . "', '" . $invoice['grand_total'] . "', '" . $invoice['organization_id'] . "', '" . $invoice['created_at'] . "', '" . $invoice['created_at'] . "')";
            $this->logQuery($logger, $query);
            if (!$dryRun) {
                $this->connection->executeQuery($query);

                $paymentId = $this->connection->lastInsertId();
                $query2 = "INSERT INTO marello_invoice_payments (invoice_id, payment_id) VALUES ('" . $invoice['id'] . "', '" . $paymentId . "')";
                $this->connection->executeQuery($query2);
            }
        }
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return array
     */
    protected function loadInvoices(LoggerInterface $logger)
    {
        $sql = 'SELECT id, payment_method, payment_reference, payment_details, grand_total, organization_id, created_at FROM marello_invoice_invoice';
        $this->logQuery($logger, $sql);

        return $this->connection->fetchAll($sql);
    }
}
