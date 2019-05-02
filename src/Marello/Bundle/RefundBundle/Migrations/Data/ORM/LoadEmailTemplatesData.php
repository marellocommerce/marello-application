<?php

namespace Marello\Bundle\RefundBundle\Migrations\Data\ORM;

use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;

class LoadEmailTemplatesData extends AbstractEmailFixture
{

    /**
     * Return path to email templates
     *
     * @return string
     */
    public function getEmailsDir()
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@MarelloRefundBundle/Migrations/Data/ORM/data/emails');
    }
}
