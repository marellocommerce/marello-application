<?php

namespace Marello\Bundle\PdfBundle\Controller;

use Marello\Bundle\PdfBundle\Request\CompositePdfRequestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DownloadController extends AbstractController
{
    /**
     * @Route(path="/{entity}/{id}", name="marello_pdf_download", requirements={"id"="\d+"})
     */
    public function downloadAction(Request $request)
    {
        return $this->container->get(CompositePdfRequestHandler::class)->handle($request);
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                CompositePdfRequestHandler::class,
            ]
        );
    }
}
