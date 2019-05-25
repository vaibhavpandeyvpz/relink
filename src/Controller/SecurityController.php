<?php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use Cake\Chronos\Chronos;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/forgot-password", name="forgot_password")
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function forgotPassword(Request $request, Swift_Mailer $mailer, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $manager = $this->getDoctrine()->getManager();
            $user = $manager->getRepository(User::class)->findOneBy($data);
            if ($user === null) {
                $form->get('email')->addError(
                    new FormError($translator->trans('No user found with this email address.'))
                );
                return $this->render('security/forgot_password.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            $manager = $this->getDoctrine()->getManager();
            $token = new PasswordResetToken();
            $token->setToken(str_random(32));
            $token->setExpiresAt(Chronos::now()->addHours(24));
            $user->addPasswordResetToken($token);
            $manager->persist($token);
            $manager->flush();
            $message = (new Swift_Message($translator->trans('Link to reset your %relink% password.', ['%relink%' => $translator->trans('Relink')])))
                ->setFrom(
                    $this->getParameter('env(EMAIL_FROM_ADDRESS)'),
                    $this->getParameter('env(EMAIL_FROM_NAME)')
                )
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/password_reset.html.twig',
                        compact('token')
                    ),
                    'text/html'
                )
            ;
            $mailer->send($message);
            $this->addFlash('info', $translator->trans('Fresh link to reset your password has been sent to %email%.', ['%email%' => $user->getEmail()]));
            return $this->redirectToRoute('login');
        }
        return $this->render('security/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function login(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError();
        $last_username = $utils->getLastUsername();
        $facebook_client_id = $this->getParameter('facebook.client_id');
        $login_with_facebook = !empty($facebook_client_id);
        $google_client_id = $this->getParameter('google.client_id');
        $login_with_google = !empty($google_client_id);
        return $this->render(
            'security/login.html.twig',
            compact('error', 'last_username', 'login_with_facebook', 'login_with_google')
        );
    }

    /**
     * @Route("/forgot-password/{token}", name="reset_password")
     * @param string $token
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function resetPassword($token, Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $token = $manager->getRepository(PasswordResetToken::class)->findOneBy(compact('token'));
        if ($token === null) {
            throw $this->createNotFoundException('No pending password reset found with this token.');
        }
        if ($token->getExpiresAtChronos()->isPast()) {
            $this->addFlash('warning', 'This password reset link has expired. Please request a new one.');
            return $this->redirectToRoute('forgot_password');
        }
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $token->getUser();
            $user->setPassword($encoder->encodePassword($user, $data['password']));
            $manager->persist($user);
            $manager->remove($token);
            $manager->flush();
            $this->addFlash('success', 'Your password has been successfully updated.');
            return $this->redirectToRoute('login');
        }
        return $this->render('security/reset_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
