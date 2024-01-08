<?php

namespace Marello\Bundle\POSBundle\Api\Processor;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Processor\Create\CreateContext;

use Marello\Bundle\POSBundle\Api\Model\Login;

/**
 * Checks whether the login credentials are valid,
 * and if so, sets API access key of authenticated pos user to the model.
 */
class HandleLogin implements ProcessorInterface
{
    /** @var string */
    private $authenticationProviderKey;

    /** @var AuthenticationManagerInterface */
    private $authenticationProvider;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param string                          $authenticationProviderKey
     * @param AuthenticationManagerInterface $authenticationProvider
     * @param TranslatorInterface             $translator
     */
    public function __construct(
        string $authenticationProviderKey,
        AuthenticationManagerInterface $authenticationProvider,
        TranslatorInterface $translator
    ) {
        $this->authenticationProviderKey = $authenticationProviderKey;
        $this->authenticationProvider = $authenticationProvider;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var CreateContext $context */
        $model = $context->getResult();
        if (!$model instanceof Login) {
            // the request is already handled
            return;
        }

        $token = $this->authenticate($model);
        $authenticatedUser = $token->getUser();
        if (!$authenticatedUser instanceof User) {
            throw new AccessDeniedException('The login via API is not supported for this user.');
        }

        $apiKey = $this->getApiKey($authenticatedUser);
        $model->setRoles($token->getRoleNames());
        $model->setApiKey($apiKey);
    }

    /**
     * @param Login $model
     *
     * @return TokenInterface
     */
    private function authenticate(Login $model)
    {
        $token = new UsernamePasswordToken(
            $model->getUser(),
            $model->getPassword(),
            $this->authenticationProviderKey
        );

        try {
            return $this->authenticationProvider->authenticate($token);
        } catch (AuthenticationException $e) {
            throw new AccessDeniedException(sprintf(
                'The user authentication fails. Reason: %s',
                $this->translator->trans($e->getMessageKey(), $e->getMessageData(), 'security')
            ));
        }
    }

    /**
     * @param User $user
     *
     * @return string|null
     */
    private function getApiKey(User $user)
    {
        $apiKey = $user->getApiKeys()->first();
        if (!$apiKey) {
            return null;
        }

        return $apiKey->getApiKey();
    }
}
