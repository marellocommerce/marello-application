<?php

namespace Marello\Bundle\PackingBundle\Migrations\Data\ORM;

use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;

class SendPackingSlipEmailTemplate extends AbstractEmailFixture
{
    public function getEmailsDir()
    {
        return __DIR__.'/data/email_templates/';
    }
}
