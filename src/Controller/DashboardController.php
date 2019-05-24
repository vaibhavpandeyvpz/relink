<?php

namespace App\Controller;

use App\Entity\Link;
use App\Form\LinkType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @Route("/cp/dashboard", name="dashboard_")
 */
class DashboardController extends AbstractController
{
    use Charts;

    /**
     * @Route("", name="index")
     * @return Response
     */
    public function index(): Response
    {
        $form = $this->createForm(LinkType::class, new Link(), ['basic_form' => true]);
        return $this->render('dashboard/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/clicks", name="clicks")
     * @param CacheInterface $cache
     * @return Response
     * @throws \Exception
     */
    public function clicks(CacheInterface $cache)
    {
        $data = $this->createSummaryChart($cache);
        $response = $this->json($data);
        $response->setEncodingOptions(JSON_PRETTY_PRINT);
        return $response;
    }

    /**
     * @Route("/popular", name="popular")
     * @param CacheInterface $cache
     * @return Response
     * @throws \Exception
     */
    public function popular(CacheInterface $cache)
    {
        $data = $this->createPopularChart($cache);
        $response = $this->json($data);
        $response->setEncodingOptions(JSON_PRETTY_PRINT);
        return $response;
    }
}
