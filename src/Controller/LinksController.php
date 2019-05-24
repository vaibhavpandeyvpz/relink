<?php

namespace App\Controller;

use App\Entity\Link;
use App\Entity\User;
use App\Form\LinkType;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\DataTables;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/cp/links", name="links_")
 */
class LinksController extends AbstractController
{
    use Charts, Links;

    /**
     * @Route("", name="index")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('links/index.html.twig');
    }

    /**
     * @Route("/create", name="create")
     * @param Request $request
     * @param SlugifyInterface $slugify
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function create(Request $request, SlugifyInterface $slugify, TranslatorInterface $translator): Response
    {
        $link = new Link();
        $form = $this->createForm(LinkType::class, $link, ['meta_tags' => false]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $link->getSlug();
            if (empty($slug)) {
                $slug = $this->createUniqueSlug();
            } else {
                $slug = $slugify->slugify($slug);
                if ($this->checkIfSlugExists($slug)) {
                    $form->get('slug')->addError(
                        new FormError($translator->trans('This value is already used.'))
                    );
                    return $this->render('links/create.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }
            $link->setSlug($slug);
            $tags = $this->crawlMetaTags($link->getTarget());
            $link->setMetaTitle($tags['title']);
            $link->setMetaDescription($tags['description']);
            $link->setUser($this->getUser());
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($link);
            $manager->flush();
            $this->addFlash('success', $translator->trans('Link %slug% was successfully created.', ['%slug%' => $slug]));
            return $this->redirectToRoute('links_view', ['id' => $link->getId()]);
        }
        return $this->render('links/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/datatable", methods={"POST"}, name="datatable")
     * @param Request $request
     * @return Response
     */
    public function datatable(Request $request): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $query = $manager->getRepository(Link::class)
                ->createQueryBuilder('l')
                ->select(['l', 'u.id AS u_id', 'u.name AS u_name'])
                ->leftJoin('l.clicks', 'c')
                ->leftJoin('l.user', 'u')
                ->addSelect('COUNT(c.id) AS l_clicks');
        if (!$this->isGranted('ROLE_ADMIN')) {
            $query->where('l.user = :user')
                ->setParameter('user', $this->getUser());
        }
        $datatables = (new DataTables\Builder())
            ->withColumnAliases([
                'l_id' => 'l.id',
                'l_slug' => 'l.slug',
                'l_target' => 'l.target',
                'l_expiresAt' => 'l.expiresAt',
                'l_createdAt' => 'l.createdAt',
            ])
            ->withIndexColumn('l.id')
            ->withQueryBuilder($query->groupBy('l.id'))
            ->withRequestParams($request->request->all());
        return $this->json($datatables->getResponse());
    }

    /**
     * @Route("/{slug}/qr", name="qr")
     * @ParamConverter("link", options={"mapping"={"slug"="slug"}})
     * @param Link $link
     * @param Request $request
     * @return Response
     */
    public function qr(Link $link, Request $request): Response
    {
        $this->denyAccessUnlessGranted('view', $link);
        $qr = new QrCode($link->getTarget());
        $qr->setEncoding('UTF-8');
        $qr->setSize(512);
        $response = new QrCodeResponse($qr);
        if ($request->get('download')) {
            $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $link->getSlug().'.png');
            $response->headers->set('Content-Disposition', $disposition);
        }
        return $response;
    }

    /**
     * @Route("/remove", name="removebatch")
     * @param Request $request
     * @param CacheInterface $cache
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Exception
     * @throws \Psr\Cache\CacheException
     */
    public function removeBatch(Request $request, CacheInterface $cache, TranslatorInterface $translator): Response
    {
        $ids = (array) $request->get('ids');
        if ($ids > 0) {
            $manager = $this->getDoctrine()->getManager();
            $query = $manager->getRepository(Link::class)
                ->createQueryBuilder('l')
                ->where('l.id IN (:ids)')
                ->setParameter('ids', $ids);
            if (!$this->isGranted('ROLE_ADMIN')) {
                $query->andWhere('l.user = :user')
                    ->setParameter('user', $this->getUser());
            }
            $select = clone $query;
            $uids = $select->leftJoin('l.user', 'u')
                ->select('u.id AS id')
                ->andWhere('u.id IS NOT NULL')
                ->groupBy('u.id')
                ->getQuery()
                ->getScalarResult();
            $uids = array_map(function (array $data) {
                return $data['id'];
            }, $uids);
            $delete = clone $query;
            $deleted = (int) $delete->delete()
                ->getQuery()
                ->getSingleScalarResult();
            if (($deleted > 0) && (count($uids) > 0)) {
                $this->clearCache($cache);
                $users = $manager->getRepository(User::class)
                    ->createQueryBuilder('u')
                    ->where('u.id IN (:ids)')
                    ->setParameter('ids', $uids)
                    ->getQuery()
                    ->getResult();
                foreach ($users as $user) {
                    $this->clearCache($cache, $user);
                }
            }
        } else {
            $deleted = $ids;
        }
        $message = $translator->trans('%count% row(s) were removed from %collection%.', [
            '%collection%' => $translator->trans('Link|Links', ['%count%' => 2]),
            '%count%' => $deleted,
        ]);
        return $this->json(compact('message'));
    }

    /**
     * @Route("/{id}/remove", name="remove")
     * @param Link $link
     * @param CacheInterface $cache
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Psr\Cache\CacheException
     */
    public function remove(Link $link, CacheInterface $cache, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('remove', $link);
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($link);
        $manager->flush();
        $this->clearCache($cache);
        $this->clearCache($cache, $this->getUser());
        $this->addFlash('info', $translator->trans('Link %slug% was successfully removed.', ['%slug%' => $link->getSlug()]));
        return $this->redirectToRoute('links_index');
    }

    /**
     * @Route("/{id}/update", name="update")
     * @param Link $link
     * @param Request $request
     * @param SlugifyInterface $slugify
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Exception
     */
    public function update(
        Link $link,
        Request $request,
        SlugifyInterface $slugify,
        TranslatorInterface $translator
    ): Response {
        $this->denyAccessUnlessGranted('update', $link);
        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $link->getSlug();
            if (empty($slug)) {
                $slug = $this->createUniqueSlug();
            } else {
                $slug = $slugify->slugify($slug);
                if ($this->checkIfSlugExists($slug, $link)) {
                    $form->get('slug')->addError(
                        new FormError($translator->trans('This value is already used.'))
                    );
                    return $this->render('links/edit.html.twig', [
                        'form' => $form->createView(),
                        'link' => $link,
                    ]);
                }
            }
            $link->setSlug($slug);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($link);
            $manager->flush();
            $this->addFlash('success', $translator->trans('Link %slug% was successfully updated.', ['%slug%' => $slug]));
            return $this->redirectToRoute('links_view', ['id' => $link->getId()]);
        }
        return $this->render('links/edit.html.twig', [
            'form' => $form->createView(),
            'link' => $link,
        ]);
    }

    /**
     * @Route("/{id}", name="view")
     * @param Link $link
     * @return Response
     */
    public function view(Link $link): Response
    {
        $this->denyAccessUnlessGranted('view', $link);
        return $this->render('links/view.html.twig', compact('link'));
    }
}
