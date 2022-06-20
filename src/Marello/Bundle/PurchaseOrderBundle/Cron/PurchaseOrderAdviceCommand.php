<?php

namespace Marello\Bundle\PurchaseOrderBundle\Cron;

use Marello\Bundle\NotificationBundle\Email\SendProcessor;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PurchaseOrderAdviceCommand extends Command implements CronCommandInterface
{
    const COMMAND_NAME = 'oro:cron:marello:po-advice';
    const EXIT_CODE = 0;

    public function __construct(
        protected ContainerInterface $container
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function getDefaultDefinition()
    {
        return '0 13 * * *';
    }

    /**
     * @inheritDoc
     */
    public function isActive()
    {
        $featureChecker = $this->container->get('oro_featuretoggle.checker.feature_checker');
        $configManager = $this->container->get('oro_config.manager');

        return $featureChecker->isResourceEnabled(self::COMMAND_NAME, 'cron_jobs') &&
        $configManager->get('marello_purchaseorder.purchaseorder_notification') === true;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Sending Purchase Orders advice notification');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configManager = $this->container->get('oro_config.manager');
        if ($configManager->get('marello_purchaseorder.purchaseorder_notification') !== true) {
            $output->writeln('The PO notification feature is disabled. The command will not run.');
            return self::EXIT_CODE;
        }

        $doctrine = $this->container->get('doctrine');
        $aclHelper = $this->container->get('oro_security.acl_helper');
        $advisedItems = $doctrine
            ->getManagerForClass(Product::class)
            ->getRepository(Product::class)
            ->getPurchaseOrderItemsCandidates($aclHelper);
        if (empty($advisedItems)) {
            $output->writeln('There are no advised items for PO notification. The command will not run.');
            return self::EXIT_CODE;
        }

        $entity = new PurchaseOrder();
        $organization = $doctrine
            ->getManagerForClass(Organization::class)
            ->getRepository(Organization::class)
            ->getFirst();
        $entity->setOrganization($organization);

        foreach ($advisedItems as $advisedItem) {
            $poItem = new PurchaseOrderItem();
            $poItem
                ->setSupplier($advisedItem['supplier'])
                ->setProductSku($advisedItem['sku'])
                ->setOrderedAmount((double)$advisedItem['orderAmount']);
            $entity->addItem($poItem);
        }

        $recipient = new Customer();
        $recipient->setEmail($configManager->get('marello_purchaseorder.purchaseorder_notification_address'));
        /** @var SendProcessor $sendProcessor */
        $sendProcessor = $this->container->get('marello_order.provider.send_email_template_attachment_processor');
        $sendProcessor->sendNotification(
            'marello_purchase_order_advise',
            [$recipient],
            $entity,
            []
        );

        return self::EXIT_CODE;
    }
}
