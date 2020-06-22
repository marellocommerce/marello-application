<?php

namespace Marello\Bundle\PdfBundle\Provider;

class RenderParametersProvider
{
    protected $providers = [];

    public function addProvider(RenderParameterProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    public function getParams($entity, array $options = [])
    {
        $params = [];

        foreach ($this->providers as $provider) {
            if ($provider->supports($entity, $options)) {
                $params = array_merge($params, $provider->getParams($entity, $options));
            }
        }

        return $params;
    }
}
