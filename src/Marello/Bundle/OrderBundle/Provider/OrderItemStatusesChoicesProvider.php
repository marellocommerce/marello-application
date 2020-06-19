<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class OrderItemStatusesChoicesProvider
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
     * @return array
     */
    public function getOrderItemStatusesChoices()
    {
        $statusClass = ExtendHelper::buildEnumValueClassName(LoadOrderItemStatusData::ITEM_STATUS_ENUM_CLASS);
        $statuses = $this->doctrineHelper
            ->getEntityManagerForClass($statusClass)
            ->getRepository($statusClass)
            ->findAll();
        $choices = [];
        foreach ($statuses as $status) {
            $choices[$status->getName()] = $status->getId();
        }

        return $choices;
    }
}
