<?php

namespace Marello\Bundle\LocaleBundle\Tests\Functional\Provider;

use Marello\Bundle\LocaleBundle\Provider\EntityLocalizationProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\Unit\EntityTrait;

class ChainEntityLocalizationProviderTest extends WebTestCase
{
    use EntityTrait;
    
    /**
     * @var EntityLocalizationProviderInterface
     */
    protected $chainEntityLocalizationProvider;
    
    /**
     * @var  ConfigManager
     */
    private $configManager;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->initClient();
        
        $container = $this->getContainer();
        $this->chainEntityLocalizationProvider = $container->get('marello_locale.entity_localization_provider.chain');
        $this->configManager = $container->get('oro_config.manager');
        $this->doctrineHelper = $container->get('oro_entity.doctrine_helper');
    }

    public function testDefaultLocalization()
    {
        $salesChannel = $this->getEntity(SalesChannel::class, ['code' => 'channel1', 'name' => 'Channel1']);
        /** @var Order $order */
        $order = $this->getEntity(Order::class, ['salesChannel' => $salesChannel]);
        
        $actualLocalization = $this->chainEntityLocalizationProvider->getLocalization($order);

        $expectedLocalization = $this->doctrineHelper
                ->getEntityManagerForClass(Localization::class)
                ->getRepository(Localization::class)
                ->find($this->configManager->get('oro_locale.default_localization'));
        
        $this->assertEquals($expectedLocalization, $actualLocalization);
    }
    
    public function testSalesChannelLocalization()
    {
        $localization = $this->getEntity(Localization::class, [
            'id' => 123,
            'name' => 'German',
            'formattingCode' => 'de_DE'
        ]);
        $salesChannel = $this->getEntity(SalesChannel::class, [
            'code' => 'channel1',
            'name' => 'Channel1',
            'localization' => $localization
        ]);
        /** @var Order $order */
        $order = $this->getEntity(Order::class, ['salesChannel' => $salesChannel]);

        $actualLocalization = $this->chainEntityLocalizationProvider->getLocalization($order);

        $this->assertEquals($localization, $actualLocalization);
    }

    public function testOrderLocalization()
    {
        $salesChannellocalization = $this->getEntity(Localization::class, [
            'id' => 123,
            'name' => 'German',
            'formattingCode' => 'de_DE'
        ]);
        $orderlocalization = $this->getEntity(Localization::class, [
            'id' => 456,
            'name' => 'French',
            'formattingCode' => 'fr_FR'
        ]);
        $salesChannel = $this->getEntity(SalesChannel::class, [
            'code' => 'channel1',
            'name' => 'Channel1',
            'localization' => $salesChannellocalization
        ]);
        /** @var Order $order */
        $order = $this->getEntity(Order::class, [
            'salesChannel' => $salesChannel,
            'localization' => $orderlocalization
        ]);

        $actualLocalization = $this->chainEntityLocalizationProvider->getLocalization($order);

        $this->assertEquals($orderlocalization, $actualLocalization);
    }
}
