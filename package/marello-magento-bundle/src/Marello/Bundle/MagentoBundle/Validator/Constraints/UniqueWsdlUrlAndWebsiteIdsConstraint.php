<?php

namespace Marello\Bundle\MagentoBundle\Validator\Constraints;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

class UniqueWsdlUrlAndWebsiteIdsConstraint extends UniqueEntity
{
    /** @var string */
    public $message = 'marello.magento.unique_wsdl_url_and_website_ids.message';

    /** @var string */
    public $repositoryMethod = 'getUniqueByWsdlUrlAndWebsiteIds';
}
