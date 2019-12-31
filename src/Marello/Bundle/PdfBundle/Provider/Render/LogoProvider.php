<?php

namespace Marello\Bundle\PdfBundle\Provider\Render;

use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Marello\Bundle\PdfBundle\Provider\RenderParameterProviderInterface;
use Marello\Bundle\PdfBundle\Provider\LogoProvider as LogoPathProvider;

class LogoProvider implements RenderParameterProviderInterface
{
    const OPTION_KEY = 'sales_channel';

    protected $logoProvider;

    public function __construct(LogoPathProvider $logoProvider)
    {
        $this->logoProvider = $logoProvider;
    }

    public function supports($entity, array $options)
    {
        return $entity instanceof AbstractInvoice
            || isset($options[self::OPTION_KEY])
        ;
    }

    public function getParams($entity, array $options)
    {
        if ($entity instanceof AbstractInvoice) {
            $salesChannel = $entity->getSalesChannel();
        } else {
            $salesChannel = $options[self::OPTION_KEY];
        }

        return ['logo' => $this->logoProvider->getInvoiceLogo($salesChannel, true)];
    }
}
