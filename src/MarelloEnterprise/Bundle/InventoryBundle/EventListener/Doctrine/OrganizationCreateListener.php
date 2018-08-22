<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\RuleBundle\Entity\Rule;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use MarelloEnterprise\Bundle\InventoryBundle\Migrations\Data\ORM\LoadSystemWFARules;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class OrganizationCreateListener
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Installed flag
     *
     * @var bool
     */
    protected $installed;

    /**
     * @param bool $installed
     */
    public function __construct($installed)
    {
        $this->installed = $installed;
    }

    /**
     * @param Organization $organization
     * @param LifecycleEventArgs $args
     */
    public function postPersist(Organization $organization, LifecycleEventArgs $args)
    {
        if ($this->installed) {
            $this->entityManager = $args->getEntityManager();
            $this->createSystemWarehouseGroupForOrganization($organization);
            $this->createSystemWFARulesForOrganization($organization);
        }
    }

    /**
     * @param Organization $organization
     */
    private function createSystemWarehouseGroupForOrganization(Organization $organization)
    {
        $systemWhGroup = new WarehouseGroup();
        $systemWhGroup
            ->setName(sprintf('%s System Group', $organization->getName()))
            ->setDescription(sprintf('System Warehouse Group for %s organization', $organization->getName()))
            ->setSystem(true)
            ->setOrganization($organization);

        $this->entityManager->persist($systemWhGroup);
        $this->entityManager->flush();
    }

    /**
     * @param Organization $organization
     */
    private function createSystemWFARulesForOrganization(Organization $organization)
    {
        $rules = $this->entityManager
            ->getRepository(Rule::class)
            ->findBy(['system' => true]);

        foreach ($rules as $rule) {
            if ($strategyIdentifier = $this->getStrategyIdentifier($rule)) {
                $wfaRule = new WFARule();
                $wfaRule
                    ->setRule($rule)
                    ->setStrategy($strategyIdentifier)
                    ->setOrganization($organization);

                $this->entityManager->persist($wfaRule);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @param Rule $rule
     * @return string|null
     */
    private function getStrategyIdentifier(Rule $rule)
    {
        foreach (LoadSystemWFARules::RULES_DATA as $ruleData) {
            if ($ruleData['name'] === $rule->getName()) {
                return $ruleData['strategy'];
            }
        }
        
        return null;
    }
}
