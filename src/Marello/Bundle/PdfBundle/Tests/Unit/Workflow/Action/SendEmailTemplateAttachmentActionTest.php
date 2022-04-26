<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Workflow\Action;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EmailBundle\Tools\EmailAddressHelper;
use Oro\Bundle\EmailBundle\Tools\EmailOriginHelper;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Component\Action\Exception\InvalidArgumentException;
use Oro\Component\ConfigExpression\ContextAccessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SendEmailTemplateAttachmentActionTest extends TestCase
{
    protected $action;

    public function setUp(): void
    {
        /** @var Processor|\PHPUnit\Framework\MockObject\MockObject $emailProcessor */
        $emailProcessor = $this->createMock(Processor::class);
        /** @var EntityNameResolver|\PHPUnit\Framework\MockObject\MockObject $entityNameResolver */
        $entityNameResolver = $this->createMock(EntityNameResolver::class);
        /** @var EmailRenderer|\PHPUnit\Framework\MockObject\MockObject $renderer */
        $renderer = $this->createMock(EmailRenderer::class);
        /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject $managerRegistry */
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        /** @var ValidatorInterface|\PHPUnit\Framework\MockObject\MockObject $validator */
        $validator = $this->createMock(ValidatorInterface::class);
        /** @var EmailOriginHelper|\PHPUnit\Framework\MockObject\MockObject $emailOriginHelper */
        $emailOriginHelper = $this->getMockBuilder(EmailOriginHelper::class)->disableOriginalConstructor()->getMock();

        $this->action = new SendEmailTemplateAttachmentAction( // weedizp3
            new ContextAccessor(),
            $emailProcessor,
            new EmailAddressHelper(),
            $entityNameResolver,
            $managerRegistry,
            $validator,
            $emailOriginHelper,
            $renderer,
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
            'entity' => new \stdClass(),
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
