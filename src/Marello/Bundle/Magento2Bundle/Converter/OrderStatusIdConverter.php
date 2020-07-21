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
    protected $orderStatusMapping = [];

    public function __construct()
    {
        $this->defaultMarelloOrderStatusCode = ExtendHelper::buildEnumCode(
            OrderStatusesInterface::OS_PROCESSING
        );

        $this->orderStatusMapping = [
            'pending' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_PENDING),
            'cancelled' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_CANCELLED),
            $this->defaultMagentoOrderStatusCode => ExtendHelper::buildEnumCode(
                OrderStatusesInterface::OS_PROCESSING
            ),
            'hold' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_ON_HOLD),
            'complete' => ExtendHelper::buildEnumCode(OrderStatusesInterface::OS_SHIPPED)
        ];
    }

    /**
     * @param string $magentoStatus
     * @param string $marelloStatusId
     */
    public function addOrderStatusMapItem(string $magentoStatus, string $marelloStatusId)
    {
        $this->orderStatusMapping[$magentoStatus] = $marelloStatusId;
    }

    /**
     * @param string|null $statusId
     * @return string|null
     */
    public function convertMarelloStatusId(string $statusId = null): ?string
    {
        $magentoStatusId = \array_search($statusId, $this->orderStatusMapping, true);

        return false !== $magentoStatusId ? $magentoStatusId : $this->defaultMagentoOrderStatusCode;
    }

    /**
     * @param string|null $statusId
     * @return string|null
     */
    public function convertMagentoStatusId(string $statusId = null): ?string
    {
        $marelloStatusId = $this->orderStatusMapping[$statusId] ?? $this->defaultMarelloOrderStatusCode;

        return $marelloStatusId;
    }
}
