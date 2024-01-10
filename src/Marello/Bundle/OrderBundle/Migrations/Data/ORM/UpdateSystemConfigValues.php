<?php

namespace Marello\Bundle\OrderBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\ConfigBundle\Entity\ConfigValue;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\ConfigBundle\Entity\Repository\ConfigValueRepository;

class UpdateSystemConfigValues extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var bool
     */
    protected $installed;

    protected $data = [
        'marello_inventory' => [
            'inventory_on_demand_enabled',
            'inventory_on_demand'
        ]
    ];

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->doctrine = $container->get('doctrine');
        $this->installed = $container->get(ApplicationState::class)->isInstalled();
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        if ($this->installed) {
            // copy values into new names and delete the old ones
            $repo = $this->getConfigValueRepository();
            foreach ($this->data as $section => $names) {
                foreach ($names as $name) {
                    /** @var ConfigValue $result */
                    $result = $repo->findOneBy(['section' => $section, 'name' => $name]);
                    if ($result) {
                        $result->setSection(str_replace('inventory', 'order', $section));
                        $result->setName(str_replace('inventory', 'order', $name));
                        $this->doctrine->getManagerForClass(ConfigValue::class)->persist($result);
                        $this->doctrine->getManagerForClass(ConfigValue::class)->flush();
                    }
                }
            }
        }
    }

    /**
     * @return ConfigValueRepository
     */
    protected function getConfigValueRepository()
    {
        return $this->doctrine->getManagerForClass(ConfigValue::class)->getRepository(ConfigValue::class);
    }
}
