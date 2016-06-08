<?php

namespace Marello\Bundle\PdfBundle\Migrations\Data\ORM;

use Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

abstract class AbstractPdfFixture extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $adminUser = $this->getAdminUser($manager);
        $organization = $this->getOrganization($manager);

        $pdfTemplates = $this->getPdfTemplatesList($this->getPdfsDir());

        $templateParams = array(
            'marginleft',
            'marginright',
            'margintop',
            'marginbottom',
            'autobreak',
            'unit',
            'format',
            'orientation'
        );

        foreach ($pdfTemplates as $fileName => $file) {
            $template = file_get_contents($file['path']);
            $pdfTemplate = new PDFTemplate($fileName, $template, $file['format']);

            $content = $pdfTemplate->getContent();

            foreach ($templateParams as $templateParam) {
                if (preg_match('#@' . $templateParam . '\s?=\s?(.*)\n#i', $template, $match)) {
                    $val = trim($match[1]);
                    $setterFunc = "set" . ucwords($templateParam);
                    $pdfTemplate->$setterFunc($val);
                    $content = trim(str_replace($match[0], '', $content));
                }
            }
            $pdfTemplate->setContent($content);
            $pdfTemplate->setOwner($adminUser);
            $pdfTemplate->setOrganization($organization);
            $manager->persist($pdfTemplate);
        }

        $manager->flush();
    }

    /**
     * @param string $dir
     * @return array
     */
    public function getPdfTemplatesList($dir)
    {
        if (is_dir($dir)) {
            $finder = new Finder();
            $files = $finder->files()->in($dir);
        } else {
            $files = array();
        }

        $templates = array();
        /** @var \Symfony\Component\Finder\SplFileInfo $file  */
        foreach ($files as $file) {
            $fileName = str_replace(array('.html.twig', '.html', '.txt.twig', '.txt'), '', $file->getFilename());
            if (preg_match('#[/\\\]([\w]+Bundle)[/\\\]#', $file->getPath(), $match)) {
                $fileName = $match[1] . ':' . $fileName;
            }

            $format = 'html';
            if (preg_match('#\.(html|txt)(\.twig)?#', $file->getFilename(), $match)) {
                $format = $match[1];
            }

            $templates[$fileName] = array(
                'path'   => $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename(),
                'format' => $format,
            );
        }

        return $templates;
    }

    /**
     * @param ObjectManager $manager
     * @return Organization
     */
    protected function getOrganization(ObjectManager $manager)
    {
        return $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
    }

    /**
     * Get administrator user
     *
     * @param ObjectManager $manager
     *
     * @return User
     *
     * @throws \RuntimeException
     */
    protected function getAdminUser(ObjectManager $manager)
    {
        $repository = $manager->getRepository('OroUserBundle:Role');
        $role       = $repository->findOneBy(['role' => User::ROLE_ADMINISTRATOR]);

        if (!$role) {
            throw new \RuntimeException('Administrator role should exist.');
        }

        $user = $repository->getFirstMatchedUser($role);

        if (!$user) {
            throw new \RuntimeException(
                'Administrator user should exist to load pdf templates.'
            );
        }

        return $user;
    }

    /**
     * Return path to pdf templates
     *
     * @return string
     */
    abstract public function getPdfsDir();
}
