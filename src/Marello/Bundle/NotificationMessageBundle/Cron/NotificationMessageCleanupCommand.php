<?php

namespace Marello\Bundle\NotificationMessageBundle\Cron;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage;
use Oro\Bundle\CronBundle\Command\CronCommandScheduleDefinitionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NotificationMessageCleanupCommand extends Command implements CronCommandScheduleDefinitionInterface
{
    private const COMMAND_NAME = 'oro:cron:marello:notification-message:cleanup';

    public function __construct(
        protected ManagerRegistry $registry
    ) {
        parent::__construct();
    }

    public function getDefaultDefinition()
    {
        return '0 12 * * *';
    }

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Cleanup outdated resolved Notification Messages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $em = $this->registry->getManagerForClass(NotificationMessage::class);
        $outdatedMessages = $em
            ->getRepository(NotificationMessage::class)
            ->getOutdatedNotificationMessages();
        if (!$outdatedMessages) {
            $symfonyStyle->note('There are no outdated notification messages.');

            return self::SUCCESS;
        }

        foreach ($outdatedMessages as $message) {
            $em->remove($message);
        }
        $em->flush();
        $symfonyStyle->success(sprintf(
            '%d outdated notification message(s) was successfully deleted.',
            count($outdatedMessages)
        ));

        return self::SUCCESS;
    }
}
