<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
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
     * @var WFARuleRepository
     */
    protected $wfaRuleRepository;

    public function __construct(
        protected WFAStrategiesRegistry $strategiesRegistry,
        protected RuleFiltrationServiceInterface $rulesFiltrationService,
        protected ManagerRegistry $registry
    ) {}

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
    public function getWarehousesForOrder(Order $order)
    {
        /** @var WFARule[] $filteredRules */
        $filteredRules = $this->rulesFiltrationService
            ->getFilteredRuleOwners($this->getRepository()->findAllWFARules());
        $results = [];

        foreach ($filteredRules as $rule) {
            $strategy = $this->strategiesRegistry->getStrategy($rule->getStrategy());
            $strategy->setEstimation($this->estimation);
            $results = $strategy->getWarehouseResults($order, $results);
        }

        if (count($results) >= 1) {
            return reset($results);
        }

        return [];
    }

    protected function getRepository(): WFARuleRepository
    {
        if (!$this->wfaRuleRepository) {
            $this->wfaRuleRepository = $this->registry->getRepository(WFARule::class);
        }

        return $this->wfaRuleRepository;
    }
}
