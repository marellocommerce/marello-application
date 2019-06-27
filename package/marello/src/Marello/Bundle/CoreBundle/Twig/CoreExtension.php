<?php

namespace Marello\Bundle\CoreBundle\Twig;

use Marello\Bundle\CoreBundle\Provider\AdditionalPlaceholderProvider;

class CoreExtension extends \Twig_Extension
{
    const NAME = 'marello_core';

    /** @var AdditionalPlaceholderProvider $provider */
    protected $provider;

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'marello_get_additional_placeholder_data',
                array($this, 'getAdditionalPlaceHolderData')
            ),
        );
    }

    /**
     * {@inheritdoc}
     * @param $section
     * @return array
     */
    public function getAdditionalPlaceHolderData($section)
    {
        return $this->provider->getPlaceHolderProvidersBySection($section);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     * @param AdditionalPlaceholderProvider $provider
     */
    public function setPlaceholderProvider(AdditionalPlaceholderProvider $provider)
    {
        $this->provider = $provider;
    }
}
