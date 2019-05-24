<?php

namespace App\Controller;

use App\Entity\Click;
use Doctrine\DataTables;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cp/clicks", name="clicks_")
 */
class ClicksController extends AbstractController
{
    /**
     * @Route("", name="index")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('clicks/index.html.twig');
    }

    /**
     * @Route("/datatable", methods={"POST"}, name="datatable")
     * @param Request $request
     * @return Response
     */
    public function datatable(Request $request): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $query = $manager->getRepository(Click::class)
            ->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('c.link', 'l')
            ->addSelect('l');
        if (!$this->isGranted('ROLE_ADMIN')) {
            $query->where('l.user = :user')
                ->setParameter('user', $this->getUser());
        }
        $datatables = (new DataTables\Builder())
            ->withColumnAliases([
                'c_id' => 'c.id',
                'c_createdAt' => 'c.createdAt',
            ])
            ->withIndexColumn('l.id')
            ->withQueryBuilder($query)
            ->withRequestParams($request->request->all());
        return $this->json($datatables->getResponse());
    }

    /**
     * @Route("/{id}", name="view")
     * @param Click $click
     * @return Response
     */
    public function view(Click $click): Response
    {
        $this->denyAccessUnlessGranted('view', $click);
        return $this->render('clicks/view.html.twig', compact('click'));
    }
}
