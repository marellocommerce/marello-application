<?php

namespace Marello\Bundle\PdfBundle;

use Marello\Bundle\PdfBundle\DependencyInjection\CompilerPass\DocumentTableProviderPass;
use Marello\Bundle\PdfBundle\DependencyInjection\CompilerPass\RenderParameterProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloPdfBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RenderParameterProviderPass());
        $container->addCompilerPass(new DocumentTableProviderPass());
    }
}
