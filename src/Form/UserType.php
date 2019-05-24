<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['autofocus' => 'autofocus'],
                'help' => 'Enter the name of this user.',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email address',
                'help' => 'Enter the email address of this user.',
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'User|Users' => 'ROLE_USER',
                    'Administrator|Administrators' => 'ROLE_ADMIN',
                ],
                'choice_label' => function ($choice, $key, $value) {
                    return $this->translator->trans($key, ['%count%' => 1]);
                },
                'expanded' => true,
                'help' => 'Select granted roles to restrict access for this user.',
                'label_attr' => ['class' => 'checkbox-custom'],
                'multiple' => true,
            ])
            ->add('password', PasswordType::class, [
                'empty_data' => '',
                'help' => $options['is_editing']
                    ? 'Enter a new password for this user (leave blank to leave it as is).'
                    : 'Enter the password for this user (leave blank for default password).',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_editing' => false,
            'validation_groups' => ['Default', 'ACL'],
        ]);
    }
}
