<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

class ProductSimpleCreateDTO extends ProductSimpleUpdateDTO
{
    public const TYPE_ID = 'simple';
    public const DEFAULT_ATTR_SET_ID = '4';

    /** @var string */
    protected $typeId = self::TYPE_ID;

    /** @var string */
    protected $attrSetID = self::DEFAULT_ATTR_SET_ID;

    /**
     * @return string
     */
    public function getTypeId(): string
    {
        return $this->typeId;
    }

    /**
     * @return string
     */
    public function getAttrSetID(): string
    {
        return $this->attrSetID;
    }
}
