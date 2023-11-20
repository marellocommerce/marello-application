<?php

namespace Marello\Bundle\InvoiceBundle\Pdf\Request;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\Translation\TranslatorInterface;
use Marello\Bundle\InvoiceBundle\Entity\Creditmemo;
use Marello\Bundle\PdfBundle\Provider\Render\ConfigValuesProvider;
use Marello\Bundle\PdfBundle\Provider\RenderParametersProvider;
use Marello\Bundle\PdfBundle\Renderer\TwigRenderer;
use Marello\Bundle\PdfBundle\Request\PdfRequestHandlerInterface;

class CreditmemoPdfRequestHandler implements PdfRequestHandlerInterface
{
    const ENTITY_ALIAS = 'creditmemo';

    public function __construct(
        protected ManagerRegistry $doctrine,
        protected TranslatorInterface $translator,
        protected RenderParametersProvider $parametersProvider,
        protected TwigRenderer $renderer
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Request $request)
    {
        $id = $request->attributes->get('id');
        if (!$id) {
            return false;
        }
        $entity = $request->attributes->get('entity');
        if (!$entity) {
            return false;
        }
        if ($entity !== self::ENTITY_ALIAS) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function handle(Request $request)
    {
        $response = new Response();
        $entity = $this->doctrine
            ->getManagerForClass(Creditmemo::class)
            ->getRepository(Creditmemo::class)
            ->find($request->attributes->get('id'));
        if (!$entity) {
            // either throw an error that the entity cannot be found or
            // null and handle the response somewhere else (i.e. the Downloadcontroller)
            return $response;
        }
        if ($request->query->get('download')) {
            $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT;
        } else {
            $disposition = ResponseHeaderBag::DISPOSITION_INLINE;
        }

        $filename = $this->translator->trans(
            'marello.invoice.pdf.filename.label',
            [
                '%invoiceType%' => $entity->getInvoiceType(),
                '%entityNumber%' => $entity->getInvoiceNumber()
            ]
        );

        $params = $this->parametersProvider
            ->getParams($entity, [ConfigValuesProvider::SCOPE_IDENTIFIER_KEY => $entity->getSalesChannel()])
        ;
        $pdf = $this->renderer
            ->render('@MarelloInvoice/Pdf/creditmemo.html.twig', $params)
        ;

        $response = new Response();
        $response->setContent($pdf);
        $response->headers->set('Content-Type', 'application/pdf');

        $contentDisposition = $response->headers->makeDisposition($disposition, $filename);
        $response->headers->set('Content-Disposition', $contentDisposition);

        return $response;
    }
}
