<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;

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
        if (empty($name)) {
            return null;
        }

        return $manager->getRepository('OroEmailBundle:EmailTemplate')->findOneBy([
            'name' => $template['params']['name'],
            'entityName' => 'Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailTemplatesList($dir)
    {
        $templates = parent::getEmailTemplatesList($dir);
        $result = [];
        foreach ($templates as $name => $data) {
            if ($name === 'marello_purchase_order_supplier') {
                $result[$name] = $data;
                break;
            }
        }

        return $result;
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
