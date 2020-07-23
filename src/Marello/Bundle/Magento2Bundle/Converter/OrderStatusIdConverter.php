<?php

namespace Marello\Bundle\Magento2Bundle\Converter;

use Marello\Bundle\OrderBundle\Model\OrderStatusesInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class OrderStatusIdConverter implements OrderStatusIdConverterInterface
{
    /** @var string */
    private $defaultMarelloOrderStatusCode;

    /** @var string */
    private $defaultMagentoOrderStatusCode = 'processing';

    /**
     * @var array
     * [
     *    <string> Magento Order Status => <string> Marello Order Status ID
     * ]
     */
    protected $magentoOrderStatusMappingToMarelloStatusId = [];

    /**
     * @var array
     * [
     *    <string> Marello Order Status ID => <string> Magento Order Status
     * ]
     */
    protected $marelloOrderStatusMappingToMagentoStatusId = [];

    public function __construct()
    {
        $this->initializeMappings();
        $this->defaultMarelloOrderStatusCode = ExtendHelper::buildEnumCode(
            OrderStatusesInterface::OS_PROCESSING
        );
    }

    /**
     * @param string|null $statusId
     * @return string|null
     */
    public function convertMarelloStatusId(string $statusId = null): ?string
    {
        if (null === $statusId) {
            return $this->defaultMagentoOrderStatusCode;
        }

        return $this->marelloOrderStatusMappingToMagentoStatusId[$statusId] ??
            $this->defaultMagentoOrderStatusCode;
    }

    /**
     * @param string|null $statusId
     * @return string|null
     */
    public function convertMagentoStatusId(string $statusId = null): ?string
    {
        if (null === $statusId) {
            return $this->defaultMarelloOrderStatusCode;
        }

        return $this->magentoOrderStatusMappingToMarelloStatusId[$statusId] ??
            $this->defaultMarelloOrderStatusCode;
    }

    protected function initializeMappings(): void
    {
        $this->magentoOrderStatusMappingToMarelloStatusId = [
            'processing' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_PROCESSING),
            'processing_ogone' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_PROCESSING),
            'pending' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_PENDING),
            'pending_ogone' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_PENDING),
            'pending_payment' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_PENDING),
            'pending_paypal' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_PENDING),
            'cancelled' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_CANCELLED),
            'cancel_ogone' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_CANCELLED),
            'holded' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_ON_HOLD),
            'complete' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_SHIPPED),
            'closed' =>  ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_CLOSED)
        ];

        $this->marelloOrderStatusMappingToMagentoStatusId = [
            ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_PROCESSING) => 'processing',
            ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_PENDING) => 'pending',
            ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_CANCELLED) => 'cancelled',
            ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_ON_HOLD) => 'holded',
            ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_SHIPPED) => 'complete',
            ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_CLOSED) => 'closed'
        ];
    }
}
