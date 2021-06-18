<?php

namespace Marello\Bundle\OrderBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;

class UpdateOrderTemplatesForWaitingForSupplyNeeds extends AbstractEmailFixture implements DependentFixtureInterface
{
    const TEMPLATES = [
        'marello_order_accepted_confirmation',
        'marello_order_invoiced',
        'marello_order_paid',
        'marello_order_shipping_prepared',
        'marello_order_shipped_confirmation'
    ];

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
     * Return path to email templates
     *
     * @return string
     */
    public function getEmailsDir()
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@MarelloOrderBundle/Migrations/Data/ORM/data/emails');
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailTemplatesList($dir)
    {
        $templates = parent::getEmailTemplatesList($dir);
        $result = [];
        foreach ($templates as $name => $data) {
            if (in_array($name, self::TEMPLATES)) {
                $result[$name] = $data;
            }
        }

        return $result;
    }

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
            'entityName' => 'Marello\Bundle\OrderBundle\Entity\Order',
        ]);
    }
}
