<?php

namespace App\Form;

use App\Entity\Link;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attributes = [
            'class' => $options['basic_form'] ? 'form-control-lg' : '',
            'placeholder' => $options['basic_form'] ? 'Target' : '',
        ];
        if (!$options['basic_form']) {
            $attributes['autofocus'] = 'autofocus';
        }
        $builder
            ->add('target', UrlType::class, [
                'attr' => $attributes,
                'help' => $options['basic_form'] ? null : 'Enter the original link or URL this short link will redirect to.',
                'label' => $options['basic_form'] ? false : null,
            ]);
        if ($options['basic_form']) {
            $builder
                ->add('mode', HiddenType::class, ['data' => 'direct']);
            return;
        }
        $builder
            ->add('slug', TextType::class, [
                'attr' => [
                    'class' => $options['basic_form'] ? 'form-control-sm' : '',
                    'placeholder' => $options['basic_form'] ? 'Slug' : '',
                ],
                'empty_data' => '',
                'help' => 'Enter a short alias for this target link. Leave blank and a random one will be generated.',
                'label' => $options['basic_form'] ? false : null,
                'required' => false,
            ])
            ->add('mode', ChoiceType::class, [
                'choices' => [
                    'Direct' => 'direct',
                    'Interstitial' => 'interstitial',
                    'IFrame' => 'iframe',
                ],
                'help' => 'Choose whether to directly redirect to the target URL or show a iframe/interstitial page.',
                'label_attr' => ['class' => 'radio-custom'],
                'expanded' => true,
            ]);
        if ($options['meta_tags']) {
            $builder
                ->add('metaTitle', TextType::class, [
                    'help' => 'Enter the meta title for iframe/interstitial page (for social sites friendliness).',
                    'label' => 'Meta title',
                    'required' => false,
                ])
                ->add('metaDescription', TextareaType::class, [
                    'help' => 'Enter the meta description for iframe/interstitial page (for social sites friendliness).',
                    'label' => 'Meta description',
                    'required' => false,
                ]);
        }
        $builder
            ->add('expiresAt', DateTimeType::class, [
                'attr' => ['data-widget' => 'datetimepicker'],
                'format' => 'yyyy-MM-dd HH:mm:ss',
                'help' => 'Select a date/time after which this short link may expire. Leave it blank to disable expiration.',
                'html5' => false,
                'label' => 'Expires',
                'required' => false,
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'basic_form' => false,
            'data_class' => Link::class,
            'meta_tags' => true,
        ]);
    }
}
