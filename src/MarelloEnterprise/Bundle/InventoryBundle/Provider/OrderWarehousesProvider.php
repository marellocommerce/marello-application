<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Provider;

use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategiesRegistry;

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
     * @var WFARuleRepository
     */
    protected $wfaRuleRepository;

    /**
     * @param WFAStrategiesRegistry $strategiesRegistry
     * @param RuleFiltrationServiceInterface $rulesFiltrationService
     * @param WFARuleRepository $wfaRuleRepository
     */
    public function __construct(
        WFAStrategiesRegistry $strategiesRegistry,
        RuleFiltrationServiceInterface $rulesFiltrationService,
        WFARuleRepository $wfaRuleRepository
    ) {
        $this->strategiesRegistry = $strategiesRegistry;
        $this->rulesFiltrationService = $rulesFiltrationService;
        $this->wfaRuleRepository = $wfaRuleRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getWarehousesForOrder(Order $order)
    {
        /** @var WFARule[] $filteredRules */
        $filteredRules = $this->rulesFiltrationService
            ->getFilteredRuleOwners($this->wfaRuleRepository->findAllWFARules());
        $results = [];

        foreach ($filteredRules as $rule) {
            $results = $this->strategiesRegistry
                ->getStrategy($rule->getStrategy())
                ->getWarehouseResults($order, $results);

            if (count($results) === 1) {
                return reset($results);
            }
        }

        if (count($results) >= 1) {
            return reset($results);
        }

        return null;
    }
}
