<?php
namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;

class LoadApiUserRole extends AbstractFixture implements DependentFixtureInterface
{
    const ROLE_INSTORE_ASSISTANT_API_USER = 'ROLE_INSTORE_ASSISTANT_API';

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrganizationAndBusinessUnitData::class
        ];
    }

    /**
     * Load roles
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $roleInstoreAssistantApi = new Role(self::ROLE_INSTORE_ASSISTANT_API_USER);
        $roleInstoreAssistantApi->setLabel('Instore Assistant API User');

        $manager->persist($roleInstoreAssistantApi);
        $manager->flush();
    }
}
