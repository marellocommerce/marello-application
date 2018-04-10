<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\ApiBundle\Controller\AbstractRestApiController;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Options\OptionsContext;

class CorsOptionsController extends AbstractRestApiController
{
    /**
     * {@inheritdoc}
     * @param Request $request
     *
     * @return Response
     */
    public function optionsAction(Request $request)
    {
        $processor = $this->getProcessor($request);
        /** @var OptionsContext $context */
        $context = $this->getContext($processor, $request);
        $processor->process($context);

        return $this->buildResponse($context);
    }
}
