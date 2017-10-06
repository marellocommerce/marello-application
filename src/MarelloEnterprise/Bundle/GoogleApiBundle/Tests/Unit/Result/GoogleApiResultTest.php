<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Tests\Unit\Result;

use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResult;

class GoogleApiResultTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $params = [
            GoogleApiResult::FIELD_STATUS=> false,
            GoogleApiResult::FIELD_ERROR_CODE => 500,
            GoogleApiResult::FIELD_ERROR_MESSAGE => 'Error',
            GoogleApiResult::FIELD_ERROR_TYPE => GoogleApiResult::WARNING_TYPE,
            GoogleApiResult::FIELD_RESULT => ['param' => 'value']
        ];

        $result = new GoogleApiResult($params);

        $getterValues = [
            GoogleApiResult::FIELD_STATUS=> $result->getStatus(),
            GoogleApiResult::FIELD_ERROR_CODE => $result->getErrorCode(),
            GoogleApiResult::FIELD_ERROR_MESSAGE => $result->getErrorMessage(),
            GoogleApiResult::FIELD_ERROR_TYPE => $result->getErrorType(),
            GoogleApiResult::FIELD_RESULT => $result->getResult()
        ];

        $this->assertEquals($params, $getterValues);
    }
}
