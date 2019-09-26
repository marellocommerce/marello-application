<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Workflow\Action;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EmailBundle\Tools\EmailAddressHelper;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Component\Action\Exception\InvalidArgumentException;
use Oro\Component\ConfigExpression\ContextAccessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SendEmailTemplateAttachmentActionTest extends TestCase
{
    protected $action;

    public function setUp()
    {
        /** @var Processor|\PHPUnit_Framework_MockObject_MockObject $emailProcessor */
        $emailProcessor = $this->createMock(Processor::class);
        /** @var EntityNameResolver|\PHPUnit_Framework_MockObject_MockObject $entityNameResolver */
        $entityNameResolver = $this->createMock(EntityNameResolver::class);
        /** @var EmailRenderer|\PHPUnit_Framework_MockObject_MockObject $renderer */
        $renderer = $this->createMock(EmailRenderer::class);
        /** @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject $objectManager */
        $objectManager = $this->createMock(ObjectManager::class);
        /** @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->createMock(ValidatorInterface::class);

        $this->action = new SendEmailTemplateAttachmentAction(
            new ContextAccessor(),
            $emailProcessor,
            new EmailAddressHelper(),
            $entityNameResolver,
            $renderer,
            $objectManager,
            $validator
        );
    }

    /**
     * @dataProvider initializeProvider
     */
    public function testInitialize($options, $exception = null, $exceptionMessage = null)
    {
        if ($exception !== null) {
            $this->expectException($exception);
            if ($exceptionMessage !== null) {
                $this->expectExceptionMessage($exceptionMessage);
            }
        }

        $this->action->initialize($options);
    }

    public function initializeProvider()
    {
        $baseOptions = [
            'from' => 'test@example.com',
            'to' => 'test@example.com',
            'template' => 'template-name',
            'subject' => 'subject',
            'entity' => new Invoice(),
        ];

        return [
            'valid bcc simple' => [
                'options' => array_merge($baseOptions, [
                    SendEmailTemplateAttachmentAction::OPTION_BCC => 'test@example.com',
                ]),
            ],
            'valid bcc array' => [
                'options' => array_merge($baseOptions, [
                    SendEmailTemplateAttachmentAction::OPTION_BCC => [
                        'email' => 'test@example.com',
                        'name' => 'test bcc'
                    ],
                ]),
            ],
            'invalid bcc' => [
                'options' => array_merge($baseOptions, [
                    SendEmailTemplateAttachmentAction::OPTION_BCC => ['name' => 'test bcc'],
                ]),
                'exception' => InvalidArgumentException::class,
                'exceptionMessage' => 'Email parameter is required',
            ],
            'empty_bcc' => [
                'options' => array_merge($baseOptions, [
                    SendEmailTemplateAttachmentAction::OPTION_BCC => null,
                ]),
            ],
            'attachments not array' => [
                'options' => array_merge($baseOptions, [
                    SendEmailTemplateAttachmentAction::OPTION_ATTACHMENTS => 'attachments',
                ]),
                'exception' => InvalidArgumentException::class,
                'exceptionMessage' => 'Attachments should be array',
            ],
            'attachment options not array' => [
                'options' => array_merge($baseOptions, [
                    SendEmailTemplateAttachmentAction::OPTION_ATTACHMENTS => ['attachments'],
                ]),
                'exception' => InvalidArgumentException::class,
                'exceptionMessage' => 'Attachment options invalid',
            ],
            'body or file not set' => [
                'options' => array_merge($baseOptions, [
                    SendEmailTemplateAttachmentAction::OPTION_ATTACHMENTS => [
                        []
                    ],
                ]),
                'exception' => InvalidArgumentException::class,
                'exceptionMessage' => 'Attachment option "body" or "file" should be set',
            ],
            'body and file set' => [
                'options' => array_merge($baseOptions, [
                    SendEmailTemplateAttachmentAction::OPTION_ATTACHMENTS => [
                        [
                            SendEmailTemplateAttachmentAction::OPTION_ATTACHMENT_BODY => 'body',
                            SendEmailTemplateAttachmentAction::OPTION_ATTACHMENT_FILE => 'file',
                        ]
                    ],
                ]),
                'exception' => InvalidArgumentException::class,
                'exceptionMessage' => 'Only one of options "body" and "file" should be set',
            ],
        ];
    }
}
