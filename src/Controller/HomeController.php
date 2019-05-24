<?php

namespace App\Controller;

use App\Entity\Click;
use App\Entity\Link;
use App\Entity\Setting;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use UAParser\Parser;

/**
 * @Route("/", name="home_")
 */
class HomeController extends AbstractController
{
    use Charts, Links;

    /**
     * @Route("", name="index")
     * @return Response
     */
    public function index(): Response
    {
        return $this->redirectToRoute('dashboard_index');
    }

    /**
     * @Route("/{slug}", name="open")
     * @ParamConverter("link", options={"mapping"={"slug"="slug"}})
     * @param Link $link
     * @param Request $request
     * @param CacheInterface $cache
     * @return Response
     * @throws \Exception
     * @throws \Psr\Cache\CacheException
     */
    public function open(Link $link, Request $request, CacheInterface $cache): Response
    {
        $expiry = $link->getExpiresAtChronos();
        if ($expiry && $expiry->isPast()) {
            throw $this->createNotFoundException("The link '{$link->getSlug()}' has expired.");
        }
        $ua = $request->headers->get('User-Agent');
        $ua = Parser::create()->parse($ua);
        $click = new Click();
        $click->setBrowser($ua->ua->toString());
        $click->setBrowserName($ua->ua->family);
        $click->setBrowserVersion($ua->ua->toVersion());
        $click->setPlatform($ua->os->toString());
        $click->setPlatformName($ua->os->family);
        $click->setPlatformVersion($ua->os->toVersion());
        $click->setDevice($ua->device->family);
        $click->setDeviceBrand($ua->device->brand);
        $click->setDeviceModel($ua->device->model);
        $click->setIpAddress($request->getClientIp());
        $link->addClick($click);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($click);
        $manager->flush();
        $this->clearCache($cache);
        $this->clearCache($cache, $link->getUser());
        if (($mode = $link->getMode()) === 'direct') {
            return $this->redirect($link->getTarget());
        }
        $settings = $manager->getRepository(Setting::class)->findFresh();
        return $this->render("home/$mode.html.twig", compact('link', 'settings'));
    }
}
