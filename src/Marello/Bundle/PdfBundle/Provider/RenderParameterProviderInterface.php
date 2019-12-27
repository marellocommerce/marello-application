<?php

namespace Marello\Bundle\PdfBundle\Provider;

interface RenderParameterProviderInterface
{
    public function supports($entity, array $options);
    public function getParams($entity, array $options);
}
