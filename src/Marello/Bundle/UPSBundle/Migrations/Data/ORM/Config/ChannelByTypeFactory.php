<?php

namespace Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config;

use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Provider\ChannelType;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ChannelByTypeFactory
{
    /**
     * @var ChannelType
     */
    private $channelType;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param ChannelType $channelType
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ChannelType $channelType,
        TranslatorInterface $translator
    ) {
        $this->channelType = $channelType;
        $this->translator = $translator;
    }

    /**
     * @param OrganizationInterface $organization
     * @param UPSSettings           $settings
     * @param bool                  $isEnabled
     *
     * @return Channel
     */
    public function createChannel(
        OrganizationInterface $organization,
        UPSSettings $settings,
        $isEnabled
    ) {
        $name = $this->getChannelTypeTranslatedLabel();

        $channel = new Channel();
        $channel->setType(ChannelType::TYPE)
            ->setName($name)
            ->setEnabled($isEnabled)
            ->setOrganization($organization)
            ->setTransport($settings);

        return $channel;
    }

    /**
     * @return string
     */
    private function getChannelTypeTranslatedLabel()
    {
        return $this->translator->trans($this->channelType->getLabel());
    }
}
