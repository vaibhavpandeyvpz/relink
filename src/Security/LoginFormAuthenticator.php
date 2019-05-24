<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $csrf;
    private $em;
    private $encoder;
    private $url;

    public function __construct(
        CsrfTokenManagerInterface $csrf,
        UserPasswordEncoderInterface $encoder,
        EntityManagerInterface $em,
        UrlGeneratorInterface $url
    ) {
        $this->csrf = $csrf;
        $this->em = $em;
        $this->encoder = $encoder;
        $this->url = $url;
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->encoder->isPasswordValid($user, $credentials['password']);
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );
        return $credentials;
    }

    protected function getLoginUrl(): string
    {
        return $this->url->generate('login');
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrf->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }
        return $user;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $provider): Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $provider)) {
            return new RedirectResponse($targetPath);
        }
        return new RedirectResponse($this->url->generate('dashboard_index'));
    }

    public function supports(Request $request): bool
    {
        return ('login' === $request->attributes->get('_route'))
            && $request->isMethod('POST');
    }
}
