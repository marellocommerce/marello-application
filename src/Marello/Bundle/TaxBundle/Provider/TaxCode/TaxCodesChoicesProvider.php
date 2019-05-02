<?php

namespace Marello\Bundle\TaxBundle\Provider\TaxCode;

use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class TaxCodesChoicesProvider implements TaxCodesChoicesProviderInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxCodes()
    {
        return array_reduce(
            $this->doctrineHelper
                ->getEntityManagerForClass(TaxCode::class)
                ->getRepository(TaxCode::class)
                ->findAll(),
            function (array $result, TaxCode $taxCode) {
                $label = $taxCode->getCode();
                $result[$label] = $taxCode->getId();

                return $result;
            },
            []
        );
    }
}
