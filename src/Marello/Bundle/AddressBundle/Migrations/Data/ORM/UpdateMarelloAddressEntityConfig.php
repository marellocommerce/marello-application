<?php

namespace Marello\Bundle\AddressBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CoreBundle\Migration\UpdateEntityConfigTrait;

class UpdateMarelloAddressEntityConfig extends AbstractFixture implements
    ContainerAwareInterface,
    VersionedFixtureInterface
{
    use UpdateEntityConfigTrait;

    /** @var ObjectManager $objectManager */
    protected $objectManager;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->objectManager = $manager;
        $data['importexport']['identity'] = -1;
        $fieldsToUpdate = [
            'firstName' => $data,
            'middleName' => $data,
            'lastName' => $data,
            'company' => $data,
            'street2' => $data
        ];
        $this->updateEntityConfigFields($fieldsToUpdate, MarelloAddress::class);
    }

    /**
     * @return string|void
     */
    public function getVersion()
    {
        return '1.0';
    }
}
