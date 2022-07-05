<?php

namespace Marello\Bundle\WorkflowBundle\Controller;

use Gaufrette\Filesystem;
use Oro\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WorkflowController extends AbstractController
{
    /**
     * @Route(
     *     "/mass-action-log/{hash}.log",
     *     name="marello_workflow_mass_action_log"
     * )
     */
    public function massActionLogAction(string $hash)
    {
        /** @var Filesystem $filesystem */
        $filesystem = $this->container->get(FilesystemMap::class)->get('importexport');
        if (!$filesystem->has($hash)) {
            throw $this->createNotFoundException('There is no such log file');
        }

        $content = $filesystem->get($hash)->getContent();

        return new Response($content, 200, ['Content-Type' => 'text/x-log']);
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                FilesystemMap::class,
            ]
        );
    }
}
