<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class OrderDashboardStatisticProvider
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    /**
     * OrderDashboardStatisticProvider constructor.
     * @param RegistryInterface $doctrine
     * @param AclHelper $aclHelper
     * @param BigNumberDateHelper $dateHelper
     */
    public function __construct(
        RegistryInterface $doctrine,
        AclHelper $aclHelper,
        BigNumberDateHelper $dateHelper
    ) {
        $this->doctrine   = $doctrine;
        $this->aclHelper  = $aclHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getTotalRevenueValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'MarelloOrderBundle:Order', 'createdAt');
        return $this->doctrine
            ->getRepository('MarelloOrderBundle:Order')
            ->getTotalRevenueValue($start, $end, $this->aclHelper);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getTotalOrdersNumberValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'MarelloOrderBundle:Order', 'createdAt');

        return $this->doctrine
            ->getRepository('MarelloOrderBundle:Order')
            ->getTotalOrdersNumberValue($start, $end, $this->aclHelper);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getAverageOrderValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'MarelloOrderBundle:Order', 'createdAt');

        return $this->doctrine
            ->getRepository('MarelloOrderBundle:Order')
            ->getAverageOrderValue($start, $end, $this->aclHelper);
    }
}
