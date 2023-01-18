<?php

namespace MarelloEnterprise\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

use Marello\Bundle\ProductBundle\Migrations\Data\ORM\AddAttributesToDefaultFamily as BaseAddAttributesToDefaultFamily;
use Marello\Bundle\ProductBundle\Migrations\Data\ORM\MakeProductAttributesTrait;
use Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadDefaultProductFamilyData;

class AddAttributesToDefaultFamily extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface,
    VersionedFixtureInterface
{
    use MakeProductAttributesTrait;

    const ATTRIBUTES = [
        'barcode' => [
            'is_global' => true
        ]
    ];

    /** @var ObjectManager $objectManager */
    protected $objectManager;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->objectManager = $manager;
        $this->updateProductAttributes(self::ATTRIBUTES);
    }

    /**
     * @inheritdoc
     */
    public function getDependencies()
    {
        return [
            LoadDefaultProductFamilyData::class,
            BaseAddAttributesToDefaultFamily::class
        ];
    }

    /**
     * @return string|void
     */
    public function getVersion()
    {
        return '1.0';
    }
}
