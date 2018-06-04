<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 18/05/2018
 * Time: 09:22
 */

namespace Marello\Bundle\MagentoBundle\ImportExport\Converter;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\ImportExportBundle\Converter\DefaultDataConverter;

use Marello\Bundle\MagentoBundle\Provider\EntityManagerTrait;
use Marello\Bundle\OrderBundle\Entity\Customer;

class OrderDataConverter extends DefaultDataConverter
{
    use EntityManagerTrait;

    /**
     * OrderDataConverter constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->setEntityManager($entityManager);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $result['owner']            = ['id' => 1];
        $result['organization']     = ['id' => 1];
        $result['orderNumber']      = $importedRecord['order_id'];
        $result['orderReference']   = $importedRecord['increment_id'];
        $result['customer']         = $this->getCustomer($importedRecord);
        $result['billingAddress']   = $this->getBillingAddress($importedRecord);
        $result['shippingAddress']   = $this->getShippingAddress($importedRecord);
        return $result;
    }

    /**
     * @param $importedRecord
     */
    protected function getBillingAddress($importedRecord)
    {
    }

    /**
     * @param $email
     * @return null
     */
    protected function getCustomer($importedRecord)
    {
        $customer = $this->getEntityManager()
            ->getRepository(Customer::class)
            ->findOneBy(['email' => $importedRecord['customer_email']]);
        if ($customer) {
            return ['id' => $customer->getId()];
        }
        return [
            'organization'      => ['id' => 1],
            'namePrefix'        => null,
            'firstName'         => $importedRecord['billing_firstname'],
            'lastName'        => $importedRecord['billing_lastname'],
            'email'        => $importedRecord['customer_email'],
        ];
    }
}
