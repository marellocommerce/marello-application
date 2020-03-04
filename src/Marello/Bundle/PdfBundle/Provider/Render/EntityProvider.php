<?php

namespace Marello\Bundle\PdfBundle\Provider\Render;

use Marello\Bundle\PdfBundle\Provider\RenderParameterProviderInterface;

class EntityProvider implements RenderParameterProviderInterface
{
    public function supports($entity, array $options)
    {
        return true;
    }

    public function getParams($entity, array $options)
    {
        return ['entity' => $entity];
    }
}
