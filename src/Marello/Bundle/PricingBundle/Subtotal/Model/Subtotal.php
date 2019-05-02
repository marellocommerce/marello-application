<?php

namespace Marello\Bundle\PricingBundle\Subtotal\Model;

use Symfony\Component\HttpFoundation\ParameterBag;

class Subtotal extends ParameterBag
{
    const OPERATION_ADD = 1;
    const OPERATION_SUBTRACTION = 2;
    const OPERATION_IGNORE = 3;

    const TYPE_FIELD = 'type';
    const LABEL_FIELD ='label';
    const AMOUNT_FIELD = 'amount';
    const CURRENCY_FIELD = 'currency';
    const OPERATION_FIELD = 'operation';
    const VISIBLE_FIELD = 'visible';
    const SORT_ORDER_FIELD = 'sortOrder';

    public function __construct(array $parameters)
    {
        parent::__construct($parameters);
        if (null === $this->getOperation()) {
            $this->setOperation(self::OPERATION_ADD);
        }
        if (null === $this->getSortOrder()) {
            $this->setSortOrder(0);
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->get(self::TYPE_FIELD);
    }

    /**
     * @param string $type
     *
     * @return Subtotal
     */
    public function setType($type)
    {
        $this->set(self::TYPE_FIELD, $type);

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->get(self::LABEL_FIELD);
    }

    /**
     * @param string $label
     *
     * @return Subtotal
     */
    public function setLabel($label)
    {
        $this->set(self::LABEL_FIELD, $label);

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->get(self::AMOUNT_FIELD);
    }

    /**
     * @param float $amount
     *
     * @return Subtotal
     */
    public function setAmount($amount)
    {
        $this->set(self::AMOUNT_FIELD, $amount);

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->get(self::CURRENCY_FIELD);
    }

    /**
     * @param string $currency
     *
     * @return Subtotal
     */
    public function setCurrency($currency)
    {
        $this->set(self::CURRENCY_FIELD, $currency);

        return $this;
    }

    /**
     * Get operation type
     *
     * @return integer
     */
    public function getOperation()
    {
        return $this->get(self::OPERATION_FIELD);
    }

    /**
     * Set operation type
     *
     * @param integer $operation
     *
     * @return Subtotal
     */
    public function setOperation($operation)
    {
        $this->set(self::OPERATION_FIELD, $operation);

        return $this;
    }

    /**
     * Check visibility in total block
     *
     * @return boolean
     */
    public function isVisible()
    {
        return $this->get(self::VISIBLE_FIELD);
    }

    /**
     * Set operation type
     *
     * @param boolean $visible
     *
     * @return Subtotal
     */
    public function setVisible($visible)
    {
        $this->set(self::VISIBLE_FIELD, $visible);

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->get(self::SORT_ORDER_FIELD);
    }

    /**
     * @param int $order
     * @return Subtotal
     */
    public function setSortOrder($order)
    {
        $this->set(self::SORT_ORDER_FIELD, $order);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = $this->all();
        
        return $data;
    }
}
