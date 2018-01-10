<?php


namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Shared;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Processor\SingleItemContext;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Provider\InstoreUserAuthenticationProviderInterface;
/**
 * Adds an identifier of the current logged in user to the Context.
 */
class InstoreUserVerificationCheck implements ProcessorInterface
{
    /** @var InstoreUserAuthenticationProviderInterface $authenticationProvider */
    private $authenticationProvider;

    public function __construct(InstoreUserAuthenticationProviderInterface $authenticationProvider)
    {
        $this->authenticationProvider = $authenticationProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var SingleItemContext $context */
        if ($context->getClassName() !== InstoreUserApi::class) {
           return;
        }

        $username = $password = 'instore-assistant';
        file_put_contents(
            '/var/www/app/logs/api-debug.log',
            print_r(!$context->getClassName() instanceof InstoreUserApi, true) . "\r\n",
            FILE_APPEND
        );
        if(!$this->authenticationProvider->authenticateInstoreUser($username, $password)) {
            throw new NotFoundHttpException('The User you\'re trying to authenticate is not valid');
        }
//        if (!$context->hasResult()) {
//            throw new NotFoundHttpException('Unsupported request.');
//        } elseif (null === $context->getResult()) {
//            throw new NotFoundHttpException('An entity with the requested identifier does not exist.');
//        }
    }
}
