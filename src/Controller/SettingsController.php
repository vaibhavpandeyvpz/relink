<?php

namespace App\Controller;

use App\Entity\Setting;
use App\Form\SettingsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/cp/settings", name="settings_")
 */
class SettingsController extends AbstractController
{
    /**
     * @Route("", methods={"GET", "POST"}, name="index")
     * @param Request $request
     * @param CacheInterface $cache
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Exception
     * @throws \Psr\Cache\CacheException
     */
    public function index(Request $request, CacheInterface $cache, TranslatorInterface $translator): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $repo = $manager->getRepository(Setting::class);
        $settings = $repo->findFresh();
        $form = $this->createForm(SettingsType::class, $settings);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $settings = $form->getData();
            foreach ($settings as $name => $value) {
                $setting = $repo->findOneBy(compact('name'));
                if ($setting === null) {
                    $setting = new Setting();
                    $setting->setName($name);
                }
                $setting->setValue($value);
                $manager->persist($setting);
            }
            $manager->flush();
            $cache->delete('settings');
            $this->addFlash('success', $translator->trans('Settings were successfully updated.'));
        }
        return $this->render('settings/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
