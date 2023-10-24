<?php

namespace Marello\Bundle\POSUserBundle\Provider;

use Oro\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Security\UserLoginAttemptLogger;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class POSUserAuthProvider implements AuthenticationManagerInterface
{
    public function __construct(
        private UserManager $userManager,
        private EncoderFactoryInterface $encoderFactory,
        private UserLoginAttemptLogger $loginLogger
    ) {
    }

    public function authenticatePOSUser(string $username, string $credentials): bool
    {
        if (!$username || !$credentials) {
            return false;
        }

        $user = $this->getUser($username);
        $encoder = $this->encoderFactory->getEncoder($user);
        if ($userHasValidCredentials = $encoder->isPasswordValid($user->getPassword(), $credentials, $user->getSalt())) {
            $this->loginLogger->logSuccessLoginAttempt($user, 'API');
        } else {
            $this->loginLogger->logFailedLoginAttempt($user, 'API');
        }

        return $userHasValidCredentials;
    }

    /**
     * find a user by username or email
     * @param string $username
     * @return UserInterface|null
     */
    protected function getUser(string $username): ?UserInterface
    {
        $user = $this->userManager->findUserByUsernameOrEmail($username);
        if (!$user) {
            throw new UserNotFoundException(sprintf('No user with name "%s" was found.', $username));
        }

        return $user;
    }

    /**
     * Authentication does not require a token (yet), therefore authenticate cannot be used
     */
    public function authenticate(TokenInterface $token): bool
    {
        return true;
    }
}
