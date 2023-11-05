<?php

namespace Marello\Bundle\BankTransferBundle\Method\Factory;

use Marello\Bundle\BankTransferBundle\Method\BankTransferMethod;
use Marello\Bundle\ManualShippingBundle\Entity\ManualShippingSettings;
use Marello\Bundle\PaymentBundle\Method\Factory\IntegrationPaymentMethodFactoryInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Oro\Bundle\IntegrationBundle\Provider\IntegrationIconProviderInterface;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;

class BankTransferMethodFromChannelFactory implements IntegrationPaymentMethodFactoryInterface
{
    /**
     * @var IntegrationIdentifierGeneratorInterface
     */
    private $identifierGenerator;

    /**
     * @var LocalizationHelper
     */
    private $localizationHelper;

    /**
     * @var IntegrationIconProviderInterface
     */
    private $integrationIconProvider;

    /**
     * @param IntegrationIdentifierGeneratorInterface $identifierGenerator
     * @param LocalizationHelper                      $localizationHelper
     * @param IntegrationIconProviderInterface        $integrationIconProvider
     */
    public function __construct(
        IntegrationIdentifierGeneratorInterface $identifierGenerator,
        LocalizationHelper $localizationHelper,
        IntegrationIconProviderInterface $integrationIconProvider
    ) {
        $this->identifierGenerator = $identifierGenerator;
        $this->localizationHelper = $localizationHelper;
        $this->integrationIconProvider = $integrationIconProvider;
    }

    /**
     * @param Channel $channel
     *
     * @return BankTransferMethod
     */
    public function create(Channel $channel)
    {
        $id = $this->identifierGenerator->generateIdentifier($channel);
        $label = $this->getChannelLabel($channel);
        $icon = $this->getIcon($channel);

        return new BankTransferMethod($id, $label, $icon, $channel->isEnabled());
    }

    /**
     * @param Channel $channel
     *
     * @return string
     */
    private function getChannelLabel(Channel $channel)
    {
        /** @var ManualShippingSettings $transport */
        $transport = $channel->getTransport();

        return (string) $this->localizationHelper->getLocalizedValue($transport->getLabels());
    }

    /**
     * @param Channel $channel
     *
     * @return string|null
     */
    private function getIcon(Channel $channel)
    {
        return $this->integrationIconProvider->getIcon($channel);
    }
}
