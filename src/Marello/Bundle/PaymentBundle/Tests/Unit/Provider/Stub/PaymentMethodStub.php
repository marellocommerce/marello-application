<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Provider\Stub;

use Oro\Bundle\FormBundle\Form\Type\OroUnstructuredHiddenType;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;

class PaymentMethodStub implements PaymentMethodInterface
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var int
     */
    protected $sortOrder;

    /**
     * @var string
     */
    protected $optionsConfigurationFormType = OroUnstructuredHiddenType::class;

    /**
     * @var bool
     */
    protected $isEnabled = true;

    /**
     * @var bool
     */
    protected $options = [];

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label ?: $this->identifier . '.label';
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * @return string
     */
    public function getOptionsConfigurationFormType()
    {
        return $this->optionsConfigurationFormType;
    }

    /**
     * @param string $optionsConfigurationFormType
     * @return $this
     */
    public function setOptionsConfigurationFormType($optionsConfigurationFormType)
    {
        $this->optionsConfigurationFormType = $optionsConfigurationFormType;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @param boolean $isEnabled
     * @return PaymentMethodStub
     */
    public function setEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param boolean $options
     * @return PaymentMethodStub
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }
}
