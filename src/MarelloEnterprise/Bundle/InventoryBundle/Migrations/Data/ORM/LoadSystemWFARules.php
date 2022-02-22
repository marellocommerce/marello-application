<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
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
    /** @var ContainerInterface $container */
    protected $container;

    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    const RULES_DATA = [
        MinimumQuantityWFAStrategy::IDENTIFIER =>
            [
                'enabled'          => true,
                'name'             => 'Minimum Quantity',
                'sortOrder'        => 0,
                'stopProcessing'   => false,
                'strategy'         => MinimumQuantityWFAStrategy::IDENTIFIER
            ],
        MinimumDistanceWFAStrategy::IDENTIFIER =>
            [
                'enabled'          => true,
                'name'             => 'Minimum Distance',
                'sortOrder'        => 10,
                'stopProcessing'   => false,
                'strategy'         => MinimumDistanceWFAStrategy::IDENTIFIER
            ],
    ];

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
        $this->container = $container;
    }

    /**
     * @return Organization[]
     */
    protected function getOrganizations()
    {
        return $this->container
            ->get('oro_entity.doctrine_helper')
            ->getEntityManagerForClass(Organization::class)
            ->getRepository(Organization::class)
            ->findAll();
    }
}
