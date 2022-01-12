<?php

namespace Marello\Bundle\WorkflowBundle\Controller;

use Gaufrette\Filesystem;
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
        $filesystem = $this->get('knp_gaufrette.filesystem_map')->get('importexport');
        if (!$filesystem->has($hash)) {
            throw $this->createNotFoundException('There is no such log file');
        }

        $content = $filesystem->get($hash)->getContent();

        return new Response($content, 200, ['Content-Type' => 'text/x-log']);
    }
}
