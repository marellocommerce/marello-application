<?php

namespace Marello\Bundle\PaymentBundle\Method;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class RemotePaymentMethod implements PaymentMethodInterface
{
    /** @var string */
    protected $identifier;

    /**
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled()
    {
        return false;
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
    public function getLabel()
    {
        return $this->identifier;
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
    public function getSortOrder()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return [];
    }
}
