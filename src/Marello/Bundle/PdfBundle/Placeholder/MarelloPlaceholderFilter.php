<?php

namespace Marello\Bundle\PdfBundle\Placeholder;

use Doctrine\Common\Util\ClassUtils;

use Ibnab\Bundle\PmanagerBundle\Placeholder\PlaceholderFilter;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Ibnab\Bundle\PmanagerBundle\Provider\ConfigurationProvider;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate;

class MarelloPlaceholderFilter extends PlaceholderFilter
{

    public function getAllowedSection()
    {
        $values = parent::getAllowedSection();
        array_push($values['allowed'],
                            'Marello\Bundle\OrderBundle\Entity\Order',
                            'Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder',
                            'Marello\Bundle\ReturnBundle\Entity\ReturnEntity'
        );
        return $values;
    }

}
