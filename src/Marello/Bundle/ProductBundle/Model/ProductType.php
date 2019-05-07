<?php

namespace Marello\Bundle\ProductBundle\Model;

use Symfony\Component\HttpFoundation\ParameterBag;

class ProductType extends ParameterBag implements ProductTypeInterface
{
    const NAME_FIELD = 'name';
    const LABEL_FIELD = 'label';
    const ATTRIBUTE_FAMILY_CODE = 'attribute_family_code';

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->get(self::NAME_FIELD);
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return $this->get(self::LABEL_FIELD);
    }

    /**
     * @inheritDoc
     */
    public function getAttributeFamilyCode()
    {
        return $this->get(self::ATTRIBUTE_FAMILY_CODE);
    }
}
