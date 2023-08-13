<?php

namespace Marello\Bundle\PdfBundle\Provider\Render;

use Marello\Bundle\PdfBundle\Provider\LogoPathProvider;
use Marello\Bundle\PdfBundle\Provider\RenderParameterProviderInterface;
use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;

class LogoRenderParameterProvider implements RenderParameterProviderInterface
{
    const OPTION_KEY = 'sales_channel';

    protected $logoProvider;

    public function __construct(LogoPathProvider $logoProvider)
    {
        $this->logoProvider = $logoProvider;
    }

    public function supports($entity, array $options)
    {
        return $entity instanceof SalesChannelAwareInterface
            || isset($options[self::OPTION_KEY]);
    }

    public function getParams($entity, array $options)
    {
        if ($entity instanceof SalesChannelAwareInterface) {
            $salesChannel = $entity->getSalesChannel();
        } else {
            $salesChannel = $options[self::OPTION_KEY];
        }

        return [
            'logo' => $this->logoProvider->getLogo($salesChannel, true),
            'logo_width' => $this->logoProvider->getLogoWidth($salesChannel),
        ];
    }
}
