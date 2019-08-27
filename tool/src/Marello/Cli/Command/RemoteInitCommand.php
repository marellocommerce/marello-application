<?php

namespace Marello\Cli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class RemoteInitCommand extends Command
{
    const NAME = 'remote:init';

    protected $configurationFile = __DIR__ . '/../config/remote-repo-configuration.yml';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('add remote repositories from configuration');
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
            foreach ($repos as $remoteName => $remoteUrl) {
                $output->writeln(sprintf('<info>adding remote %s %s</info>', $remoteName, $remoteUrl));
                $process = new Process(sprintf('git remote add %s %s', $remoteName, $remoteUrl));
                $process->setTimeout(60);
                $process->run(
                    function ($type, $buffer) use ($output) {
                        $output->write((Process::ERR === $type) ? 'ERR:' . $buffer : $buffer);
                    }
                );
            }
        }

        $output->writeln('<info>done processing config file</info>');
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
