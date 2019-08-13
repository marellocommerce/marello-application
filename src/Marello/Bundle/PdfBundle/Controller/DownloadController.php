<?php

namespace Marello\Bundle\PdfBundle\Controller;

use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Marello\Bundle\PdfBundle\Provider\Render\ConfigValuesProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadController extends Controller
{
    /**
     * @Route("/invoice/{id}", name="marello_pdf_download_invoice", requirements={"id"="\d+"})
     */
    public function invoiceAction(Request $request, Invoice $entity)
    {
        if ($request->query->has('download') && $request->query->get('download')) {
            $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT;
        } else {
            $disposition = ResponseHeaderBag::DISPOSITION_INLINE;
        }

        $filename = sprintf('%s.pdf', $this->container->get('translator')->trans(
            'marello.pdf.filename.invoice',
            ['%entityNumber%' => $entity->getInvoiceNumber()]
        ));

        $params = $this->container
            ->get('marello_pdf.provider.render_parameters')
            ->getParams($entity, [ConfigValuesProvider::SCOPE_IDENTIFIER_KEY => $entity->getSalesChannel()])
        ;
        $pdf = $this->container
            ->get('marello_pdf.renderer.twig')
            ->render('MarelloPdfBundle:Download:invoice.html.twig', $params)
        ;

        $response = new Response();
        $response->setContent($pdf);
        $response->headers->set('Content-Type', 'application/pdf');

        $contentDisposition = $response->headers->makeDisposition($disposition, $filename);
        $response->headers->set('Content-Disposition', $contentDisposition);

        return $response;
    }
}
