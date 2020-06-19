<?php

namespace Marello\Bundle\Magento2Bundle\Handler;

use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class TransportEntityHandler
{
    private const FORM_NAME = 'check';

    /** @var FormFactoryInterface */
    protected $formFactory;

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Update transport entity with actual data or create new one
     *
     * @param Request $request
     * @param TransportInterface $transport
     * @param Magento2Transport|null $transportEntity
     * @return Magento2Transport
     */
    public function getHandledTransportEntity(
        Request $request,
        TransportInterface $transport,
        Magento2Transport $transportEntity = null
    ): Magento2Transport {
        $form = $this->formFactory
            ->createNamed(
                self::FORM_NAME,
                $transport->getSettingsFormType(),
                $transportEntity,
                ['csrf_protection' => false]
            );

        $form->handleRequest($request);

        return $form->getData();
    }
}
