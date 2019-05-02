<?php

namespace Oro\Bundle\CalendarBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

class UpdatePurchaseOrderSupplierEmailTemplates extends AbstractEmailFixture implements VersionedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    protected function findExistingTemplate(ObjectManager $manager, array $template)
    {
        $name = $template['params']['name'];
        if (empty($name) || 'marello_purchase_order_supplier' !== $name) {
            return null;
        }

        return $manager->getRepository('OroEmailBundle:EmailTemplate')->findOneBy([
            'name' => $template['params']['name'],
            'entityName' => 'Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder',
        ]);
    }

    /**
     * Return path to email templates
     *
     * @return string
     */
    public function getEmailsDir()
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@MarelloPurchaseOrderBundle/Migrations/Data/ORM/data/emails');
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '1.1';
    }
}
