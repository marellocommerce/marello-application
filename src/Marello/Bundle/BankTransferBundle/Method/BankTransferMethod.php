<?php

namespace Marello\Bundle\BankTransferBundle\Method;

use Marello\Bundle\BankTransferBundle\Form\Type\BankTransferOptionsType;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodIconAwareInterface;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;

class BankTransferMethod implements PaymentMethodInterface, PaymentMethodIconAwareInterface
{
    const INSTRUCTIONS_OPTION = 'instructions';
    
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
    public function getOptionsConfigurationFormType()
    {
        return BankTransferOptionsType::class;
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
