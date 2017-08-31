<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Provider;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategiesRegistry;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class OrderWarehousesProvider implements OrderWarehousesProviderInterface
{
    /**
     * @var WFAStrategiesRegistry
     */
    protected $strategiesRegistry;

    /**
     * @var RuleFiltrationServiceInterface
     */
    protected $rulesFiltrationService;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param WFAStrategiesRegistry $strategiesRegistry
     * @param RuleFiltrationServiceInterface $rulesFiltrationService
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        WFAStrategiesRegistry $strategiesRegistry,
        RuleFiltrationServiceInterface $rulesFiltrationService,
        DoctrineHelper $doctrineHelper
    ) {
        $this->strategiesRegistry = $strategiesRegistry;
        $this->rulesFiltrationService = $rulesFiltrationService;
        $this->doctrineHelper = $doctrineHelper;
    }

    public function getWarehousesForOrder(Order $order)
    {
        /** @var WFARule[] $filteredRules */
        $filteredRules = $this->rulesFiltrationService->getFilteredRuleOwners($this->getRules());
        foreach ($filteredRules as $rule) {
            $results = $this->strategiesRegistry->getStrategy($rule->getStrategy())->getWarehouses($order);
            return reset($results);
        }
        
        return null;
    }

    /**
     * @return Warehouse[]
     */
    protected function getRules()
    {
        return $this->doctrineHelper
            ->getEntityManagerForClass(WFARule::class)
            ->getRepository(WFARule::class  )
            ->findAll();
    }
}
