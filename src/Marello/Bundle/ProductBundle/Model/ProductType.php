<?php

namespace Marello\Bundle\ProductBundle\Model;

use Symfony\Component\HttpFoundation\ParameterBag;

class ProductType extends ParameterBag implements ProductTypeInterface
{
    const NAME_FIELD = 'name';
    const LABEL_FIELD = 'label';

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
}
