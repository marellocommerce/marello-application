<?php

namespace Marello\Bundle\MagentoBundle\Provider;

use Marello\Bundle\MagentoBundle\Entity\Customer;
use Marello\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use Marello\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class UniqueCustomerEmailSoapProvider
{
    /**
     * @param MagentoTransportInterface $transport
     * @param Customer      $customer
     *
     * @return bool
     */
    public function isCustomerHasUniqueEmail(MagentoTransportInterface $transport, Customer $customer)
    {
        $filters = $this->getPreparedFilters($customer);
        $customers = $this->doRequest($transport, $filters);

        if (false === $customers) {
            return true;
        }

        $filteredCustomer = array_filter(
            $customers,
            function ($customerData) use ($customer) {
                if (is_object($customerData)) {
                    $customerData = (array)$customerData;
                }
                if ($customerData
                    && !empty($customerData['customer_id'])
                    && $customerData['customer_id'] == $customer->getOriginId()
                ) {
                    return false;
                }

                return true;
            }
        );

        return 0 === count($filteredCustomer);
    }

    /**
     * @param MagentoTransportInterface $transport
     * @param array         $filters
     *
     * @return array | false
     */
    protected function doRequest(MagentoTransportInterface $transport, array $filters)
    {
        $customers = $transport->call(SoapTransport::ACTION_CUSTOMER_LIST, $filters);

        if (is_array($customers)) {
            return $customers;
        }

        $customers = (array) $customers;
        if (empty($customers)) {
            return false;
        }

        return [$customers];
    }

    /**
     * @param Customer $customer
     *
     * @return array
     */
    protected function getPreparedFilters(Customer $customer)
    {
        $filter = new BatchFilterBag();
        $filter->addComplexFilter(
            'email',
            [
                'key' => 'email',
                'value' => [
                    'key' => 'eq',
                    'value' => $customer->getEmail()
                ]
            ]
        );
        $filter->addComplexFilter(
            'store_id',
            [
                'key' => 'store_id',
                'value' => [
                    'key' => 'eq',
                    'value' => $customer->getStore()->getId()
                ]
            ]
        );

        return $filter->getAppliedFilters();
    }
}
