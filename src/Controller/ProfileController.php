<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/cp/profile", name="profile_")
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("", methods={"GET", "POST"}, name="index")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Exception
     */
    public function index(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        TranslatorInterface $translator
    ): Response {
        $user = $this->getUser();
        $data = clone $user;
        $data->setPassword('');
        $form = $this->createForm(ProfileType::class, $data);
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
                    new FormError($translator->trans('User with this email address already exists.'))
                );
                return $this->render('profile/index.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            $user->setName($data->getName());
            $user->setEmail($data->getEmail());
            if (($password = $data->getPassword()) !== '') {
                $user->setPassword($encoder->encodePassword($user, $password));
            }
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', $translator->trans('Profile information was successfully updated.'));
        }
        return $this->render('profile/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
