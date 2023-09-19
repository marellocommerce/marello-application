<?php

namespace Marello\Bundle\POSUserBundle\Provider;

use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserInterface as OroUserInterface;
use Oro\Bundle\UserProBundle\Security\LoginAttemptsManager;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Manager\OroUserManagerInterface;

class POSUserAuthProvider implements AuthenticationProviderInterface // weedizp + service
{
    /** @var OroUserManagerInterface $userManager */
    private $userManager;

    /** @var EncoderFactory $encoderFactory */
    private $encoderFactory;

    /** @var LoginAttemptsManager $attemptsManager */
    private $attemptsManager;

    public function __construct(
        OroUserManagerInterface $userManager,
        EncoderFactory $encoderFactory,
        LoginAttemptsManager $attemptsManager
    ) {
        $this->userManager = $userManager;
        $this->encoderFactory = $encoderFactory;
        $this->attemptsManager = $attemptsManager;
    }

    public function authenticatePOSUser(string $username, string $credentials): bool
    {
        if (!$username || !$credentials) {
            return false;
        }

        /** @var User $user */
        $user = $this->getUser($username);
        if (!$userHasValidCredentials = $this->hasValidCredentials($user, $credentials)) {
            $this->attemptsManager->trackLoginFailure($user);
        } else {
            $this->attemptsManager->trackLoginSuccess($user);
        }

        return $userHasValidCredentials;
    }

    /**
     * check with the encoder whether the credentials are correct
     * @param $user
     * @param $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        /** @var PasswordEncoderInterface $userEncoder */
        $userEncoder = $this->getEncoder($user);

        return $userEncoder->isPasswordValid($user->getPassword(), $credentials, $user->getSalt());
    }

    /**
     * @param OroUserInterface|string $user
     * @return PasswordEncoderInterface
     */
    protected function getEncoder($user)
    {
        return $this->encoderFactory->getEncoder($user);
    }

    /**
     * @param $username
     * @return mixed
     * @throws UsernameNotFoundException
     */
    protected function getUser($username)
    {
        $user = $this->userManager->findUserByUsernameOrEmail($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('No user with name "%s" was found.', $username));
        }

        return $user;
    }

    /**
     * Authentication does not require a token (yet)
     * @param TokenInterface $token
     * @return bool
     */
    public function supports(TokenInterface $token)
    {
        return true;
    }

    /**
     * Authentication does not require a token (yet), therefore authenticate cannot be used
     * @param TokenInterface $token
     * @return bool
     */
    public function authenticate(TokenInterface $token)
    {
        return true;
    }
}
