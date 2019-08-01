<?php

namespace Marello\Cli\Command\Phpstorm;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Prepares PHPStorm configuration from config folder to root .idea folder so proper application settings
 * could be activated.
 *
 * Add or update application config files if any PHPStorm configuration changes should be done.
 */
class InitApplication extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('phpstorm:init-application')
            ->addArgument('application', InputArgument::OPTIONAL, 'Application name')
            ->setDescription('Switch PHPStorm settings and optimize developer experience for requested application.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $input->getArgument('application');
        $rootSrcDir = __DIR__ . DIRECTORY_SEPARATOR . "config";

        if ($application) {
            $srcDir = $rootSrcDir . DIRECTORY_SEPARATOR . $application;
            if (is_dir($srcDir)) {
                $this->updateConfigs(
                    $output,
                    $srcDir,
                    $this->getRootDir() . DIRECTORY_SEPARATOR . '.idea'
                );

                $output->writeln("Configuration updated. Please restart PHPStorm.");
            } else {
                $output->writeln("Configuration for application \"{$application}\" doesn't exist");
            }
        } else {
            $output->writeln('Existing applications:');
            $files = scandir($rootSrcDir);
            foreach ($files as $name) {
                if (!in_array($name, array('.', '..'), true) && is_dir($rootSrcDir . DIRECTORY_SEPARATOR . $name)) {
                    $output->writeln('  ' . $name);
                }
            }
        }
    }

    /**
     * @param OutputInterface $output
     * @param string          $srcDir
     * @param string          $destDir
     */
    protected function updateConfigs(OutputInterface $output, $srcDir, $destDir)
    {
        $parts = explode(DIRECTORY_SEPARATOR, $this->getRootDir());
        $projectName = end($parts);

        $this->updateProjectStructure($output, $srcDir, $destDir, $projectName.'.iml');
        $this->mergeFile($output, $srcDir, $destDir, 'php.xml', false);
        $this->updateSymfony2PluginConfig($output, $srcDir, $destDir);
        $this->updateModulesFile($output, $srcDir, $destDir, $projectName);
    }

    /**
     * @param OutputInterface $output
     * @param string $srcDir
     * @param string $destDir
     * @param string $projectName
     */
    protected function updateModulesFile(OutputInterface $output, $srcDir, $destDir, $projectName)
    {
        $fileName = 'modules.xml';
        $srcFile = __DIR__.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$fileName;
        $destFile = $destDir.DIRECTORY_SEPARATOR.$fileName;

        $sourceXmlDoc = $this->loadXmlDocument($srcFile);

        $destElement = $this->findByXPath(
            $sourceXmlDoc,
            "/project/component[@name='ProjectModuleManager']/modules/module"
        );

        foreach (['fileurl', 'filepath'] as $attribute) {
            $item = $destElement->item(0)->attributes->getNamedItem($attribute);
            $item->nodeValue = str_replace('dev.iml', $projectName.'.iml', $item->nodeValue);
        }

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln("Copying {$srcFile} to {$destFile}");
        }

        $sourceXmlDoc->save($destFile);
    }

    /**
     * @param OutputInterface $output
     * @param string          $srcDir
     * @param string          $destDir
     * @param string          $fileName
     * @param bool            $override
     *
     * @return bool TRUE if the file was successfully merged;
     *              FALSE if the destination file already exists and override was not requested
     */
    protected function mergeFile(OutputInterface $output, $srcDir, $destDir, $fileName, $override = false)
    {
        $srcFile = $srcDir . DIRECTORY_SEPARATOR . $fileName;
        $destFile = $destDir . DIRECTORY_SEPARATOR . $fileName;
        if ($override || !is_file($destFile)) {
            return $this->copyFile($output, $srcFile, $destFile, $override);
        }

        if (!is_file($srcFile)) {
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln("The source file {$srcFile} does not exist");
            }

            return true;
        }

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln("Merge {$srcFile} with {$destFile}");
        }

        $destXmlDoc = $this->loadXmlDocument($destFile);
        $sourceXmlDoc = $this->loadXmlDocument($srcFile);

        foreach ($this->findByXPath($sourceXmlDoc, "/project/component") as $srcElement) {
            $project = $destXmlDoc->getElementsByTagName('project')->item(0);
            $destElement = $this->findByXPath(
                $destXmlDoc,
                sprintf("/project/component[@name='%s']", $srcElement->attributes->getNamedItem('name')->nodeValue)
            );

            $srcElement = $destXmlDoc->importNode($srcElement, true);

            if ($destElement->length) {
                $project->replaceChild($srcElement, $destElement->item(0));
            } else {
                $project->appendChild($srcElement);
            }
        }

        $destXmlDoc->save($destFile);

        return true;
    }

    /**
     * @param OutputInterface $output
     * @param string          $srcFile
     * @param string          $destFile
     * @param bool            $override
     *
     * @return bool TRUE if the file was successfully copied or source file does not exist;
     *              FALSE if the destination file already exists and override was not requested
     */
    protected function copyFile(OutputInterface $output, $srcFile, $destFile, $override = false)
    {
        if (!$override && is_file($destFile)) {
            return false;
        }

        if (is_file($srcFile)) {
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln("Copying {$srcFile} to {$destFile}");
            }
            copy($srcFile, $destFile);
        } elseif ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln("The source file {$srcFile} does not exist");
        }

        return true;
    }

    /**
     * Updates "dev.iml" file that describes the project structure
     *
     * @param OutputInterface $output
     * @param string          $srcDir
     * @param string          $destDir
     * @param string          $targetFileName
     */
    protected function updateProjectStructure(OutputInterface $output, $srcDir, $destDir, $targetFileName = 'dev.iml')
    {
        $srcFile = $srcDir . DIRECTORY_SEPARATOR .'dev.iml';
        $destFile = $destDir . DIRECTORY_SEPARATOR . $targetFileName;
        if (!is_file($destFile)) {
            $this->copyFile($output, $srcFile, $destFile, true);

            return;
        }

        // load existing "app/cache" exclude folders that should be moved to new "dev.iml" file
        $excludeFoldersRootPath = "/module/component/content[@url='file://\$MODULE_DIR\$']";
        $excludeAppCacheFolderNodes = [];
        $excludeFolderNodes = $this->findByXPath(
            $this->loadXmlDocument($destFile),
            $excludeFoldersRootPath . '/excludeFolder'
        );
        if ($excludeFolderNodes) {
            foreach ($excludeFolderNodes as $excludeFolderNode) {
                $excludeFolder = $excludeFolderNode->attributes->getNamedItem('url')->nodeValue;
                if ($this->endsWith($excludeFolder, '/var/cache')
                    ||  false !== strpos($excludeFolder, '/var/cache/')
                ) {
                    $excludeAppCacheFolderNodes[] = $excludeFolderNode;
                }
            }
        }
        // replace "dev.iml" file with the file from the config
        if ($this->copyFile($output, $srcFile, $destFile, true) && !empty($excludeAppCacheFolderNodes)) {
            // add "app/cache" exclude folders that that existed in the previous "dev.iml" file
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(
                    "Adding the following excludeFolder nodes that existed in the previous {$targetFileName}:"
                );
            }
            $destXmlDoc = $this->loadXmlDocument($destFile);
            $destExcludeFoldersRootNodes = $this->findByXPath($destXmlDoc, $excludeFoldersRootPath);
            if (1 === $destExcludeFoldersRootNodes->length) {
                $destExcludeFoldersRootNode = $destExcludeFoldersRootNodes->item(0);
                foreach ($excludeAppCacheFolderNodes as $excludeFolderNode) {
                    if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        $output->writeln('  ' . $excludeFolderNode->attributes->getNamedItem('url')->nodeValue);
                    }
                    $destExcludeFoldersRootNode->appendChild($destXmlDoc->importNode($excludeFolderNode, false));
                }
                $destXmlDoc->save($destFile);
            }
        }
    }

    /**
     * @param OutputInterface $output
     * @param string          $srcDir
     * @param string          $destDir
     */
    protected function updateSymfony2PluginConfig(OutputInterface $output, $srcDir, $destDir)
    {
        $fileName = 'symfony2.xml';
        $srcFile = $srcDir . DIRECTORY_SEPARATOR . $fileName;
        $destFile = $destDir . DIRECTORY_SEPARATOR . $fileName;
        if ($this->copyFile($output, $srcFile, $destFile)) {
            return;
        }

        if (!is_file($srcFile)) {
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln("The source file {$srcFile} does not exist");
            }

            return;
        }

        $destFile = $destDir . DIRECTORY_SEPARATOR . $fileName;
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln("Updating {$destFile}");
        }

        $rootNodePath = "/project/component[@name='Symfony2PluginSettings']";
        $optionNames = ['directoryToApp', 'directoryToWeb', 'pathToUrlGenerator', 'pathToTranslation'];
        $xPathExpr = sprintf(
            $rootNodePath . "/option[%s]",
            implode(
                ' or ',
                array_map(
                    function ($name) {
                        return "@name='{$name}'";
                    },
                    $optionNames
                )
            )
        );

        $destXmlDoc = $this->loadXmlDocument($destFile);
        $destNode = $this->findByXPath($destXmlDoc, $rootNodePath)->item(0);
        foreach ($this->findByXPath($destXmlDoc, $xPathExpr) as $element) {
            $destNode->removeChild($element);
        }
        foreach ($this->findByXPath($this->loadXmlDocument($srcFile), $xPathExpr) as $element) {
            $destNode->appendChild($destXmlDoc->importNode($element, false));
        }
        $destXmlDoc->save($destFile);
    }

    /**
     * @param string $file
     *
     * @return \DOMDocument
     */
    protected function loadXmlDocument($file)
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->formatOutput = true;
        $xmlDoc->load($file);

        return $xmlDoc;
    }

    /**
     * @param \DOMDocument $xmlDoc
     * @param string       $expression
     *
     * @return \DOMNodeList|\DOMNode[]
     */
    protected function findByXPath(\DOMDocument $xmlDoc, $expression)
    {
        $xPath = new \DOMXPath($xmlDoc);

        return $xPath->query($expression);
    }

    /**
     * Determines whether the ending of $haystack matches $needle.
     *
     * @param string $haystack The string to check
     * @param string $needle   The string to compare
     *
     * @return bool
     */
    protected function endsWith($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }

    protected function getRootDir()
    {
        return realpath(__DIR__ . '/../../../../../../');
    }
}
