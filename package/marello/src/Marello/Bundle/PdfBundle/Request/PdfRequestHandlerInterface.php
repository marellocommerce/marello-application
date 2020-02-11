<?php

namespace Marello\Bundle\PdfBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface PdfRequestHandlerInterface
{
    /**
     * @param Request $request
     * @return bool
     */
    public function isApplicable(Request $request);

    /**
     * @param Request $request
     * @return Response|null
     */
    public function handle(Request $request);
}