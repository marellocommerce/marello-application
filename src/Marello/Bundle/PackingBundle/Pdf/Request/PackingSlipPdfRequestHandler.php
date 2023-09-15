<?php

namespace Marello\Bundle\PackingBundle\Pdf\Request;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PdfBundle\Provider\Render\ConfigValuesProvider;
use Marello\Bundle\PdfBundle\Provider\Render\LogoRenderParameterProvider;
use Marello\Bundle\PdfBundle\Provider\RenderParametersProvider;
use Marello\Bundle\PdfBundle\Renderer\TwigRenderer;
use Marello\Bundle\PdfBundle\Request\PdfRequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\Translation\TranslatorInterface;

class PackingSlipPdfRequestHandler implements PdfRequestHandlerInterface
{
    const ENTITY_ALIAS = 'packingslip';

    public function __construct(
        private ManagerRegistry $doctrine,
        private TranslatorInterface $translator,
        private RenderParametersProvider $parametersProvider,
        private TwigRenderer $renderer
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
        /** @var PackingSlip $entity */
        $entity = $this->doctrine
            ->getManagerForClass(PackingSlip::class)
            ->getRepository(PackingSlip::class)
            ->find($request->attributes->get('id'));

        if (!$entity) {
            return $response;
        }

        if ($request->query->get('download')) {
            $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT;
        } else {
            $disposition = ResponseHeaderBag::DISPOSITION_INLINE;
        }

        $filename = sprintf('%s.pdf', $this->translator->trans(
            'marello.packing.pdf.filename.label',
            [
                '%entityNumber%' => $entity->getPackingSlipNumber()
            ]
        ));

        $params = $this->parametersProvider->getParams(
            $entity,
            [
                ConfigValuesProvider::SCOPE_IDENTIFIER_KEY => $entity->getSalesChannel(),
                LogoRenderParameterProvider::OPTION_KEY => $entity->getSalesChannel(),
            ]
        );
        $pdf = $this->renderer->render('@MarelloPacking/Pdf/packingSlip.html.twig', $params);

        $response->setContent($pdf);
        $response->headers->set('Content-Type', 'application/pdf');

        $contentDisposition = $response->headers->makeDisposition($disposition, $filename);
        $response->headers->set('Content-Disposition', $contentDisposition);

        return $response;
    }
}
