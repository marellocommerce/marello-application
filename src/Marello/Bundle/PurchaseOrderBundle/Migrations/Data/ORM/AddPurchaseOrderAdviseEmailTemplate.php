<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;

class AddPurchaseOrderAdviseEmailTemplate extends AbstractEmailFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadEmailTemplatesData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function findExistingTemplate(ObjectManager $manager, array $template)
    {
        $name = $template['params']['name'];
        if (empty($name) || 'marello_purchase_order_advise' !== $name) {
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
            if ($name === 'marello_purchase_order_advise') {
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
}
