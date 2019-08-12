<?php

namespace Marello\Cli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class RemoteSyncCommand extends Command
{
    const NAME = 'remote:sync';

    protected $configurationFile = __DIR__ . '/../config/remote-repo-configuration.yml';

    protected $defaultBranch = 'master';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('sync remote repositories to local from configuration');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>start processing config file</info>');
        $values = $this->getValuesFromYmlFile();
        if (!array_key_exists('repositories', $values)) {
            throw new RuntimeException('Cannot initialise remote repositories without proper configuration key');
        }

        foreach ($values['repositories'] as $repoType => $repos) {
            $output->writeln(sprintf('<info>repo type: %s</info>', $repoType));
            foreach ($repos as $remoteName => $remoteUrl) {
                $output->writeln(sprintf('<info>pulling from downstream remote %s %s</info>', $remoteName, $remoteUrl));
                $prefix = sprintf('%s/%s', $repoType, $remoteName);
                $branch = $this->defaultBranch;
                $process = new Process(sprintf('git subtree pull --prefix %s %s %s', $prefix, $remoteName, $branch));
                $process->setTimeout(60);
                $process->run(
                    function ($type, $buffer) use ($output) {
                        $output->write((Process::ERR === $type) ? 'ERR:' . $buffer : $buffer);
                    }
                );
            }
        }

        $output->writeln('<info>done</info>');
    }



    /**
     * Get array with values for remote repositories from pre configured file
     * @return mixed
     */
    private function getValuesFromYmlFile()
    {
        return Yaml::parseFile($this->configurationFile);
    }
}
