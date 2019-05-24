<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class GoogleAuthenticator extends SocialAuthenticator
{
    use TargetPathTrait;

    private $em;
    private $registry;
    private $url;

    public function __construct(EntityManagerInterface $em, ClientRegistry $registry, UrlGeneratorInterface $url)
    {
        $this->em = $em;
        $this->registry = $registry;
        $this->url = $url;
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getGoogleClient());
    }

    private function getGoogleClient(): OAuth2ClientInterface
    {
        return $this->registry->getClient('google');
    }

    private function getLoginUrl(): string
    {
        return $this->url->generate('login');
    }

    public function getUser($credentials, UserProviderInterface $users): ?UserInterface
    {
        $user = $this->getGoogleClient()->fetchUserFromToken($credentials);
        $email = $user->getEmail();
        $user = $this->em->getRepository(User::class)->findOneBy(compact('email'));
        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }
        return $user;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }
        $url = $this->getLoginUrl();
        return new RedirectResponse($url);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $provider): Response
    {
        if ($target = $this->getTargetPath($request->getSession(), $provider)) {
            return new RedirectResponse($target);
        }
        return new RedirectResponse($this->url->generate('dashboard_index'));
    }

    public function start(Request $request, AuthenticationException $e = null): Response
    {
        return new RedirectResponse($this->url->generate('login'));
    }

    public function supports(Request $request): bool
    {
        return 'connect_google_check' === $request->attributes->get('_route');
    }
}
