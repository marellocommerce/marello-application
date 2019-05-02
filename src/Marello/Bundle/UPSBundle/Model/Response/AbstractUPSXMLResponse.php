<?php

namespace Marello\Bundle\UPSBundle\Model\Response;

abstract class AbstractUPSXMLResponse implements UPSResponseInterface
{
    /**
     * @param \SimpleXMLElement $result
     * @param string $responseType
     *
     * @throws \Exception
     */
    protected function handelError(\SimpleXMLElement $result, $responseType)
    {
        $statusCode = $result->xpath("/{$responseType}/Response/ResponseStatusCode");
        $statusCode = reset($statusCode);
        $statusCode = (string)$statusCode;

        /*
         * If response status is "1" do nothing (1 means success).
         */
        if ($statusCode === '1') {
            return;
        }

        $errors = $result->xpath("/{$responseType}/Response/Error");

        foreach ($errors as $error) {
            $severity = (string)$error->ErrorSeverity;

            if ($severity !== 'Warning') {
                $exception = new \Exception(
                    (string)$error->ErrorDescription,
                    (string)$error->ErrorCode
                );

                throw $exception;
            }
        }
    }

    /**
     * @param \SimpleXMLElement $parentElement
     * @param string $path
     * @return \SimpleXMLElement
     */
    protected function getElement(\SimpleXMLElement $parentElement, $path)
    {
        $element = $parentElement->xpath($path);
        $element = reset($element);

        return $element;
    }
}
