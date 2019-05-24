<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\DataTables;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/cp/users", name="users_")
 */
class UsersController extends AbstractController
{
    use Charts;

    /**
     * @Route("", name="index")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('users/index.html.twig');
    }

    /**
     * @Route("/create", name="create")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function create(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        TranslatorInterface $translator
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['validation_groups' => ['Default', 'ACL', 'Creation']]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (($password = $user->getPassword()) === '') {
                $password = '88888888';
                $this->addFlash('info', $translator->trans('Default password %password% is set for this user.', ['%password%' => $password]));
            }
            $user->setPassword($encoder->encodePassword($user, $password));
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', $translator->trans('User %name% was successfully created.', ['%name%' => $user->getName()]));
            return $this->redirectToRoute('users_view', ['id' => $user->getId()]);
        }
        return $this->render('users/create.html.twig', [
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
        $datatables = (new DataTables\Builder())
            ->withColumnAliases([
                'u_id' => 'u.id',
                'u_name' => 'u.name',
                'u_email' => 'u.email',
                'u_createdAt' => 'u.createdAt',
            ])
            ->withIndexColumn('u.id')
            ->withQueryBuilder(
                $manager->getRepository(User::class)
                    ->createQueryBuilder('u')
                    ->select('u')
                    ->leftJoin('u.links', 'l')
                    ->addSelect('COUNT(l.id) AS u_links')
                    ->addSelect("'' AS u_password")
                    ->groupBy('u.id'))
            ->withRequestParams($request->request->all());
        return $this->json($datatables->getResponse());
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
            $deleted = (int) $manager->getRepository(User::class)
                ->createQueryBuilder('u')
                ->delete()
                ->where('u.id != :id')
                ->andWhere('u.id IN (:ids)')
                ->setParameters([
                    'id' => $this->getUser()->getId(),
                    'ids' => $ids,
                ])
                ->getQuery()
                ->getSingleScalarResult();
        } else {
            $deleted = $ids;
        }
        if ($deleted > 0) {
            $this->clearCache($cache);
        }
        $message = $translator->trans('%count% row(s) were removed from %collection%.', [
            '%collection%' => $translator->trans('User|Users', ['%count%' => 2]),
            '%count%' => $deleted,
        ]);
        return $this->json(compact('message'));
    }

    /**
     * @Route("/{id}/remove", name="remove")
     * @param User $user
     * @param Request $request
     * @param CacheInterface $cache
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Psr\Cache\CacheException
     */
    public function remove(User $user, Request $request, CacheInterface $cache, TranslatorInterface $translator): Response
    {
        if ($user->getId() === $this->getUser()->getId()) {
            $this->addFlash('warning', $translator->trans('Your cannot remove yourself from system.'));
            return $this->redirect($request->headers->get('Referer'));
        }
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($user);
        $manager->flush();
        $this->clearCache($cache);
        $this->addFlash('info', $translator->trans('User %name% was successfully removed.', ['%name%' => $user->getName()]));
        return $this->redirectToRoute('users_index');
    }

    /**
     * @Route("/{id}/update", name="update")
     * @param User $user
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Exception
     */
    public function update(
        User $user,
        Request $request,
        UserPasswordEncoderInterface $encoder,
        TranslatorInterface $translator
    ): Response {
        $data = clone $user;
        $data->setPassword('');
        $form = $this->createForm(UserType::class, $data, ['is_editing' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $repo = $manager->getRepository(User::class);
            $count = (int) $repo->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.id != :id')
                ->andWhere('u.email = :email')
                ->setParameters([
                    'id' => $user->getId(),
                    'email' => $data->getEmail(),
                ])
                ->getQuery()
                ->getSingleScalarResult();
            if ($count !== 0) {
                $form->get('email')->addError(
                    new FormError($translator->trans('This value is already used.'))
                );
                return $this->render('users/edit.html.twig', [
                    'form' => $form->createView(),
                    'user' => $user,
                ]);
            }
            $user->setName($data->getName());
            $user->setEmail($data->getEmail());
            if (($password = $data->getPassword()) !== '') {
                $user->setPassword($encoder->encodePassword($user, $password));
            }
            if ($user->getId() === $this->getUser()->getId()) {
                $roles = $data->getRoles();
                $roles[] = 'ROLE_ADMIN';
                $data->setRoles($roles);
            }
            $user->setRoles($data->getRoles());
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', $translator->trans('User %name% was successfully updated.', ['%name%' => $user->getName()]));
            return $this->redirectToRoute('users_view', ['id' => $user->getId()]);
        }
        return $this->render('users/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}", name="view")
     * @param User $user
     * @return Response
     */
    public function view(User $user): Response
    {
        return $this->render('users/view.html.twig', compact('user'));
    }
}
