<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\RuleBundle\Entity\Rule;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumDistance\MinimumDistanceWFAStrategy;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadMinDistanceWFARule extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            LoadSystemWFARules::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $ruleData = LoadSystemWFARules::RULES_DATA[MinimumDistanceWFAStrategy::IDENTIFIER];
        foreach ($this->getOrganizations() as $organization) {
            $existingWFARule = $manager
                ->getRepository(WFARule::class)
                ->findOneBy([
                    'strategy' => $ruleData['strategy'],
                    'organization' => $organization
                ]);
            if ($existingWFARule) {
                $rule = $existingWFARule->getRule();
                $rule
                    ->setEnabled($ruleData['enabled'])
                    ->setName($ruleData['name'])
                    ->setSortOrder($ruleData['sortOrder'])
                    ->setStopProcessing($ruleData['stopProcessing'])
                    ->setSystem(true);

                $existingWFARule->setRule($rule);

                $manager->persist($rule);
                $manager->persist($existingWFARule);
            } else {
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
