<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Provider;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategiesRegistry;

class OrderWarehousesProvider implements OrderWarehousesProviderInterface
{
    /**
     * @var bool
     */
    private $estimation = false;
    
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
     * {@inheritDoc}
     */
    public function setEstimation($estimation = false)
    {
        $this->estimation = $estimation;
    }

    /**
     * {@inheritdoc}
     */
    public function getWarehousesForOrder(Order $order, Allocation $allocation = null): array
    {
        /** @var WFARule[] $filteredRules */
        $filteredRules = $this->rulesFiltrationService
            ->getFilteredRuleOwners($this->wfaRuleRepository->findAllWFARules());
        $results = [];

        foreach ($filteredRules as $rule) {
            $strategy = $this->strategiesRegistry->getStrategy($rule->getStrategy());
            $strategy->setEstimation($this->estimation);
            $results = $strategy->getWarehouseResults($order, $allocation, $results);
        }

        if (count($results) > 0) {
            return $results;
        }

        return [];
    }
}
