<?php

namespace Marello\Bundle\PdfBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CompositePdfRequestHandler implements PdfRequestHandlerInterface
{
    /**
     * @var PdfRequestHandlerInterface[]
     */
    private $handlers = [];

    /**
     * @param PdfRequestHandlerInterface $handler
     */
    public function addHandler(PdfRequestHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Request $request)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function handle(Request $request)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->isApplicable($request)) {
                return $handler->handle($request);
            }
        }

        return new Response();
    }
}
