<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Functional\Cron;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Oro\Bundle\CronBundle\Entity\Schedule;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand;
use Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData;

class PurchaseOrderAdviceCronTest extends WebTestCase
{
    use MessageQueueExtension;

    /**
     * @var Application
     */
    protected $application;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->initClient();

        $this->application = new Application($this->client->getKernel());
        $this->application->setAutoExit(false);
        $this->application->add(new PurchaseOrderAdviceCommand($this->getContainer()));
    }

    /**
     * {@inheritdoc}
     */
    public function testAdviceCommandWillNotRunBecauseNoAdvisedItemsFound()
    {
        $command = $this->application->find(PurchaseOrderAdviceCommand::COMMAND_NAME);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command]);

        self::assertStringContainsString(
            'There are no advised items for PO notification. The command will not run.',
            $commandTester->getDisplay()
        );
        self::assertEquals(PurchaseOrderAdviceCommand::EXIT_CODE, $commandTester->getStatusCode());
    }

    /**
     * {@inheritdoc}
     */
    public function testAdviceCommandWillNotRunBecauseFeatureIsNotEnabled()
    {
        /** @var ConfigManager $configManager */
        $configManager = self::getContainer()->get('oro_config.manager');
        $configManager->set('marello_purchaseorder.purchaseorder_notification', false);

        $command = $this->application->find(PurchaseOrderAdviceCommand::COMMAND_NAME);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command]);

        self::assertStringContainsString(
            'This cron command is not active.',
            $commandTester->getDisplay()
        );
        self::assertEquals(PurchaseOrderAdviceCommand::EXIT_CODE, $commandTester->getStatusCode());
    }

    /**
     * {@inheritdoc}
     */
    public function testAdviceCommandWillSendNotification()
    {
        // setup inventory so po candidates are available
        $this->loadFixtures(
            [
                LoadInventoryData::class
            ]
        );
        /** @var ProductRepository $productRepository */
        $productRepository = self::getContainer()
            ->get('doctrine')
            ->getRepository(Product::class);

        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $results = $productRepository->getPurchaseOrderItemsCandidates($aclHelper);
        static::assertCount(1, $results);

        /** @var ConfigManager $configManager */
        $configManager = self::getContainer()->get('oro_config.manager');
        // enabled po notification setting again :')
        $configManager->set('marello_purchaseorder.purchaseorder_notification', true);

        $command = $this->application->find(PurchaseOrderAdviceCommand::COMMAND_NAME);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command]);

        self::assertEmpty($commandTester->getDisplay());
        self::assertEquals(PurchaseOrderAdviceCommand::EXIT_CODE, $commandTester->getStatusCode());
    }

    /**
     * {@inheritdoc}
     */
    public function testAdviceCommandIsRegisteredCorrectly()
    {
        /** @var EntityRepository $scheduleRepository */
        $scheduleRepository = self::getContainer()
            ->get('doctrine')
            ->getRepository(Schedule::class);
        $crons = $scheduleRepository->findBy(['command' => PurchaseOrderAdviceCommand::COMMAND_NAME]);
        self::assertCount(1, $crons);
    }
}
