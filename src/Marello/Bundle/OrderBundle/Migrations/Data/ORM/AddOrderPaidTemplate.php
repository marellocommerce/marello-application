<?php

namespace Marello\Bundle\OrderBundle\Migrations\Data\ORM;

use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;

class AddOrderPaidTemplate extends AbstractEmailFixture
{
    const ORDER_PAID = 'marello_order_paid';
    
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
            if ($name === self::ORDER_PAID) {
                $result[$name] = $data;
                break;
            }
        }

        return $result;
    }
}
