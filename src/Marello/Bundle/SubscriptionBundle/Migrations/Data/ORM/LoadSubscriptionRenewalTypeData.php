<?php

namespace Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM;

class LoadSubscriptionRenewalTypeData extends AbstractLoadSubscriptionEnumData
{
    const ENUM_CLASS = 'marello_subsrenewtype';
    
    const AUTO_RENEW = 'auto_renew';
    const REQUEST_BY_EMAIL_FOR_RENEWAL = 'request_by_email_for_renewal';
    const AUTO_TERMINATE = 'auto_terminate';

    /** @var array */
    protected $data = [
        self::AUTO_RENEW => 'Auto renew',
        self::REQUEST_BY_EMAIL_FOR_RENEWAL => 'Request (by email) for renewal',
        self::AUTO_TERMINATE => 'Auto terminate'
    ];
}
