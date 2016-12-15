<?php

namespace Marello\Bundle\InventoryBundle\Model;

class InventoryUpdateContext
{
    /** @var array $values */
    private $values = [];

    /**
     * {@inheritdoc}
     * @param $name
     * @param $value
     */
    public function setValue($name, $value)
    {
        $this->values[$name] = $value;
    }

    /**
     * {@inheritdoc}
     * @param $name
     * @return mixed|null
     */
    public function getValue($name)
    {
        return isset($this->values[$name])
            ? $this->values[$name]
            : null;
    }
}