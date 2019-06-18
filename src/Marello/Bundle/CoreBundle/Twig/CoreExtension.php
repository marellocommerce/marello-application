<?php

namespace Marello\Bundle\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class CoreExtension extends \Twig_Extension implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const NAME = 'marello_core';

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'bundleExists',
                array($this, 'bundleExists')
            ),
        );
    }

    /**
     * @param string $bundle
     * @return bool
     */
    public function bundleExists($bundle){
        return array_key_exists(
            $bundle,
            $this->container->getParameter('kernel.bundles')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}