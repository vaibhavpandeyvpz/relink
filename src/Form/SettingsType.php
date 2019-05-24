<?php

namespace App\Form;

use App\Entity\Setting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(Setting::INTERSTITIAL_DELAY, NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 180]),
                    new Assert\Regex('/^\d+$/'),
                ],
                'help' => 'Enter a delay (in seconds) before target redirection occurs on interstitial pages.',
                'label' => 'Delay',
            ])
            ->add(Setting::TRACKING_SCRIPTS_HEAD, TextareaType::class, [
                'attr' => ['data-widget' => 'ace'],
                'constraints' => new Assert\Length(['max' => 1024]),
                'help' => 'Enter any scripts you would like to be included on interstitial & iframe page (inside <head></head> tags).',
                'label' => 'Scripts (head)',
                'required' => false,
            ])
            ->add(Setting::TRACKING_SCRIPTS_BODY, TextareaType::class, [
                'attr' => ['data-widget' => 'ace'],
                'constraints' => new Assert\Length(['max' => 1024]),
                'help' => 'Enter any scripts you would like to be included on interstitial & iframe page (inside <body></body> tags).',
                'label' => 'Scripts (body)',
                'required' => false,
            ])
        ;
    }
}
