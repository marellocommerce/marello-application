<?php

namespace Marello\Bundle\ManualShippingBundle\Method;

use Marello\Bundle\ShippingBundle\Method\ShippingMethodIconAwareInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ManualShippingMethod implements ShippingMethodInterface, ShippingMethodIconAwareInterface
{
    /**
     * @var ManualShippingMethodType
     */
    protected $type;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string|null
     */
    protected $icon;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @param string $identifier
     * @param string $label
     * @param string $icon
     * @param bool   $enabled
     */
    public function __construct($identifier, $label, $icon, $enabled)
    {
        $this->identifier = $identifier;
        $this->label = $label;
        $this->icon = $icon;
        $this->type = new ManualShippingMethodType($label);
        $this->enabled = $enabled;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function isGrouped()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritDoc}
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * {@inheritDoc}
     */
    public function getTypes()
    {
        return [$this->type];
    }

    /**
     * {@inheritDoc}
     */
    public function getType($type)
    {
        if ($this->type->getIdentifier() === $type) {
            return $this->type;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsConfigurationFormType()
    {
        return HiddenType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getSortOrder()
    {
        return 10;
    }
}
