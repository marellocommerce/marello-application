<?php
namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Migrations\Data\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

class LoadApiUserAclRole extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var ContainerInterface $container */
    protected $container;

    /** @var ObjectManager $objectManager */
    protected $objectManager;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadApiUserRole::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load roles
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->objectManager = $manager;

        /** @var AclManager $manager */
        $manager = $this->container->get('oro_security.acl.manager');

        if ($manager->isAclEnabled()) {
            $this->createAclForRole($manager);
            $manager->flush();
        }
    }

    /**
     * @param AclManager $manager
     */
    private function createAclForRole(AclManager $manager)
    {
        $role = $this->getRole(LoadApiUserRole::ROLE_INSTORE_ASSISTANT_API_USER);

        if ($role) {
            $sid = $manager->getSid($role);

            // grant to only view the User entity
            $oid = $manager->getOid('entity:Oro\Bundle\UserBundle\Entity\User');
            $extension = $manager->getExtensionSelector()->select($oid);
            $maskBuilders = $extension->getAllMaskBuilders();

            foreach ($maskBuilders as $maskBuilder) {
                foreach (['VIEW_SYSTEM'] as $permission) {
                    if ($maskBuilder->hasMask('MASK_' . $permission)) {
                        $maskBuilder->add($permission);
                    }
                }

                $manager->setPermission($sid, $oid, $maskBuilder->get());
            }
        }
    }

    /**
     * @param string $roleName
     * @return Role
     */
    protected function getRole($roleName)
    {
        return $this->objectManager->getRepository('OroUserBundle:Role')->findOneBy(['role' => $roleName]);
    }
}
