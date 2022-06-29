<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Provider;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\InventoryBundle\Strategy\WFA\WFAStrategiesRegistry;
use Marello\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;

class OrderWarehousesProvider implements OrderWarehousesProviderInterface
{
    /**
     * @var bool
     */
    private $estimation = false;

    /**
     * OrderWarehousesProvider constructor.
     * @param WFAStrategiesRegistry $strategiesRegistry
     * @param RuleFiltrationServiceInterface $rulesFiltrationService
     * @param WFARuleRepository $wfaRuleRepository
     * @param AclHelper $aclHelper
     */
    public function __construct(
        protected WFAStrategiesRegistry $strategiesRegistry,
        protected RuleFiltrationServiceInterface $rulesFiltrationService,
        protected WFARuleRepository $wfaRuleRepository,
        protected AclHelper $aclHelper
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getWarehousesForOrder(Order $order, Allocation $allocation = null): array
    {
        /** @var WFARule[] $filteredRules */
        $filteredRules = $this->rulesFiltrationService
            ->getFilteredRuleOwners($this->wfaRuleRepository->findAllWFARules($this->aclHelper));
        $results = [];

        foreach ($filteredRules as $rule) {
            $strategy = $this->strategiesRegistry->getStrategy($rule->getStrategy());
            $results = $strategy->getWarehouseResults($order, $allocation, $results);
        }

        if (count($results) > 0) {
            return $results;
        }

        return [];
    }
}
