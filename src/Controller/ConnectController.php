<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/connect", name="connect_")
 */
class ConnectController extends AbstractController
{
    /**
     * @Route("/facebook", name="facebook")
     * @param ClientRegistry $registry
     * @return Response
     */
    public function facebook(ClientRegistry $registry): Response
    {
        return $registry->getClient('facebook')->redirect(['email'], []);
    }

    /**
     * @Route("/google", name="google")
     * @param ClientRegistry $registry
     * @return Response
     */
    public function google(ClientRegistry $registry): Response
    {
        return $registry->getClient('google')->redirect(['email', 'openid', 'profile'], []);
    }
}
