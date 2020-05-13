<?php

namespace Marello\Bundle\TaxBundle;

use Marello\Bundle\TaxBundle\DependencyInjection\Compiler\ResolverEventConnectorPass;
use Marello\Bundle\TaxBundle\DependencyInjection\Compiler\TaxRuleMatcherPass;
use Marello\Bundle\TaxBundle\DependencyInjection\Compiler\TaxMapperPass;
use Marello\Bundle\TaxBundle\DependencyInjection\MarelloTaxExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloTaxBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new MarelloTaxExtension();
    }
    
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TaxRuleMatcherPass());
        $container->addCompilerPass(new TaxMapperPass());
        $container->addCompilerPass(new ResolverEventConnectorPass());
        parent::build($container);
    }
}
