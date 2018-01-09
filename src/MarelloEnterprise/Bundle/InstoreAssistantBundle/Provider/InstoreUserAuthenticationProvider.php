<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Provider;

use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;

use Oro\Bundle\UserBundle\Entity\OrganizationAwareUserInterface;
use Oro\Bundle\UserBundle\Entity\UserInterface as OroUserInterface;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Manager\OroUserManagerInterface;

/**
 * Implementing the authentication provider leaves the option open to make use of
 * tokens if necessary without BC breaks.
 *
 * Class InstoreUserAuthenticationProvider
 * @package MarelloEnterprise\Bundle\InstoreAssistantBundle\Manager
 */
class InstoreUserAuthenticationProvider implements AuthenticationProviderInterface
{
    /** @var OroUserManagerInterface $userManager */
    private $userManager;

    /** @var EncoderFactory $encoderFactory */
    private $encoderFactory;

    public function __construct(
        OroUserManagerInterface $userManager,
        EncoderFactory $encoderFactory
    )
    {
        $this->userManager = $userManager;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * authenticate credentials instore user
     * @param $username
     * @param $credentials
     * @return bool
     */
    public function authenticateInstoreUser($username, $credentials)
    {
        if (!$username || !$credentials) {
            return false;
        }

        $user = $this->getInstoreUser($username);
        return $this->hasValidCredentials($user, $credentials);
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
    protected function getInstoreUser($username)
    {
        $user = $this->userManager->findUserByUsernameOrEmail($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('No user with name "%s" was found.', $username));
        }

        return $user;
    }
//
//    /**
//     * Get InstoreUser Api
//     * @param OrganizationAwareUserInterface $user
//     * @return mixed
//     */
//    protected function getInstoreUserApi(OrganizationAwareUserInterface $user)
//    {
//        return $this->userManager->getApi($user, $user->getOrganization());
//    }

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
