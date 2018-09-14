<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\RuleBundle\Entity\Rule;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumDistance\MinimumDistanceWFAStrategy;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\MinimumQuantityWFAStrategy;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadSystemWFARules extends AbstractFixture implements ContainerAwareInterface
{
    const RULES_DATA = [
        MinimumQuantityWFAStrategy::IDENTIFIER =>
            [
                'enabled'          => true,
                'name'             => 'WFA Rule #1',
                'sortOrder'        => 0,
                'stopProcessing'   => false,
                'strategy'         => MinimumQuantityWFAStrategy::IDENTIFIER
            ],
        MinimumDistanceWFAStrategy::IDENTIFIER =>
            [
                'enabled'          => true,
                'name'             => 'WFA Rule #2',
                'sortOrder'        => 10,
                'stopProcessing'   => false,
                'strategy'         => MinimumDistanceWFAStrategy::IDENTIFIER
            ],
    ];
    
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getOrganizations() as $organization) {
            foreach (self::RULES_DATA as $ruleData) {
                $rule = (new Rule())
                    ->setEnabled($ruleData['enabled'])
                    ->setName($ruleData['name'])
                    ->setSortOrder($ruleData['sortOrder'])
                    ->setStopProcessing($ruleData['stopProcessing'])
                    ->setSystem(true);
                $wfaRule = new WFARule();
                $wfaRule
                    ->setRule($rule)
                    ->setOrganization($organization)
                    ->setStrategy($ruleData['strategy']);

                $manager->persist($rule);
                $manager->persist($wfaRule);
            }
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->doctrineHelper = $container->get('oro_entity.doctrine_helper');
    }

    /**
     * @return Organization[]
     */
    protected function getOrganizations()
    {
        return $this->doctrineHelper
            ->getEntityManagerForClass(Organization::class)
            ->getRepository(Organization::class)
            ->findAll();
    }
}
