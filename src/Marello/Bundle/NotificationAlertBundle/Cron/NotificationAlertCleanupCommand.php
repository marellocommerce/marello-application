<?php

namespace Marello\Bundle\NotificationAlertBundle\Cron;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\NotificationAlertBundle\Entity\NotificationAlert;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NotificationAlertCleanupCommand extends Command implements CronCommandInterface
{
    private const COMMAND_NAME = 'oro:cron:marello:notification-alert:cleanup';

    public function __construct(
        protected ManagerRegistry $registry
    ) {
        parent::__construct();
    }

    public function getDefaultDefinition()
    {
        return '0 12 * * *';
    }

    public function isActive()
    {
        return true;
    }

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Cleanup outdated resolved Notification Alerts');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $em = $this->registry->getManagerForClass(NotificationAlert::class);
        $outdatedAlerts = $em
            ->getRepository(NotificationAlert::class)
            ->getOutdatedNotificationAlerts();
        if (!$outdatedAlerts) {
            $symfonyStyle->note('There are no outdated notification alerts.');

            return self::SUCCESS;
        }

        foreach ($outdatedAlerts as $alert) {
            $em->remove($alert);
        }
        $em->flush();
        $symfonyStyle->success(sprintf(
            '%d outdated notification alert(s) was successfully deleted.',
            count($outdatedAlerts)
        ));

        return self::SUCCESS;
    }
}
