<?php

namespace Marello\Bundle\PurchaseOrderBundle\Cron;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EmailBundle\Model\From;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\EmailBundle\Manager\EmailTemplateManager;
use Oro\Bundle\EmailBundle\Exception\EmailTemplateException;
use Oro\Bundle\NotificationBundle\Model\NotificationSettings;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\CronBundle\Command\CronCommandScheduleDefinitionInterface;

use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\NotificationMessageBundle\Model\NotificationMessageContext;
use Marello\Bundle\PurchaseOrderBundle\Provider\PurchaseOrderCandidatesProvider;
use Marello\Bundle\NotificationMessageBundle\Event\CreateNotificationMessageEvent;
use Marello\Bundle\NotificationMessageBundle\Event\ResolveNotificationMessageEvent;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface;
use Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageSourceInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;

class PurchaseOrderAdviceCommand extends Command implements CronCommandScheduleDefinitionInterface
{
    const COMMAND_NAME = 'oro:cron:marello:po-advice';
    const EXIT_CODE = 0;

    /**
     * @param ContainerInterface $container
     * @param EmailTemplateManager $emailTemplateManager
     * @param NotificationSettings $notificationSettings
     */
    public function __construct(
        protected ContainerInterface $container,
        protected EmailTemplateManager $emailTemplateManager,
        protected NotificationSettings $notificationSettings
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
        $featureChecker = $this->container->get('oro_featuretoggle.checker.feature_checker');
        $configManager = $this->container->get('oro_config.manager');

        $isActive = $featureChecker->isResourceEnabled(self::COMMAND_NAME, 'cron_jobs') &&
            $configManager->get('marello_purchaseorder.purchaseorder_notification') === true;
        if (!$isActive) {
            $output->writeln('This cron command is not active.');
            return self::EXIT_CODE;
        }

        $configManager = $this->container->get('oro_config.manager');
        if ($configManager->get('marello_purchaseorder.purchaseorder_notification') !== true) {
            $output->writeln('The PO notification feature is disabled. The command will not run.');
            return self::EXIT_CODE;
        }

        $context = $this->createNotificationContext();
        /** @var PurchaseOrderCandidatesProvider $provider */
        $provider = $this
            ->container
            ->get('Marello\Bundle\PurchaseOrderBundle\Provider\PurchaseOrderCandidatesProvider');
        $advisedItems = $provider->getPurchaseOrderCandidates();
        if (empty($advisedItems)) {
            $output->writeln('There are no advised items for PO notification. The command will not run.');
            $this->container
                ->get('event_dispatcher')
                ->dispatch(
                    new ResolveNotificationMessageEvent($context),
                    ResolveNotificationMessageEvent::NAME
                );
            return self::EXIT_CODE;
        }

        if (count($advisedItems) > 0) {
            $this->container
                ->get('event_dispatcher')
                ->dispatch(
                    new CreateNotificationMessageEvent($context),
                    CreateNotificationMessageEvent::NAME
                );
        }

        $recipient = new Customer();
        $recipient->setEmail($configManager->get('marello_purchaseorder.purchaseorder_notification_address'));
        $recipient->setOrganization($this->getOrganization());
        $this->sendNotification(
            'marello_purchase_order_advise',
            $recipient,
            $advisedItems
        );

        return self::EXIT_CODE;
    }

    /**
     * @param $templateName
     * @param $recipient
     * @param $items
     * @param NotificationMessageContext $context
     * @return void
     */
    private function sendNotification($templateName, $recipient, $items)
    {
        try {
            $this->emailTemplateManager
                ->sendTemplateEmail(
                    From::emailAddress($this->notificationSettings->getSender()->toString()),
                    [$recipient],
                    new EmailTemplateCriteria($templateName),
                    ['items' => $items]
                );
        } catch (EmailTemplateException $exception) {
            $errorContext = NotificationMessageContextFactory::createError(
                NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_SYSTEM,
                'marello.notificationmessage.purchaseorder.candidates.error.cron',
                sprintf('Error found, code: %s, Message: %s', $exception->getCode(), $exception->getMessage()),
                null,
                null,
                null,
                null,
                null,
                $exception->getTraceAsString(),
                $this->getOrganization(),
            );
            $this->container
                ->get('event_dispatcher')
                ->dispatch(
                    new CreateNotificationMessageEvent($errorContext),
                    CreateNotificationMessageEvent::NAME
                );
        }
    }

    /**
     * @return OrganizationInterface
     */
    protected function getOrganization()
    {
        return $this->container->get('doctrine')
            ->getManagerForClass(Organization::class)
            ->getRepository(Organization::class)
            ->getFirst();
    }

    /**
     * @return NotificationMessageContext
     */
    protected function createNotificationContext()
    {
        $url = $this
            ->container
            ->get('router')
            ->generate('marello_purchase_order_widget_purchase_order_candidates_grid', [], 302);
        $translation = $this
            ->container
            ->get('translator')
            ->trans(
                'marello.notificationmessage.purchaseorder.candidates.solution',
                ['%url%' => $url],
                'notificationMessage'
            );

        return NotificationMessageContextFactory::create(
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_INFO,
            NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_NO,
            NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_SYSTEM,
            'marello.notificationmessage.purchaseorder.candidates.title',
            'marello.notificationmessage.purchaseorder.candidates.message',
            $translation,
            null,
            null,
            null,
            null,
            null,
            $this->getOrganization()
        );
    }
}
