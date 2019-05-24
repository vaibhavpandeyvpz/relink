<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 8,
                        'max' => 32,
                    ]),
                ],
                'first_options'  => [
                    'attr' => ['autofocus' => 'autofocus'],
                    'help' => 'Enter your desired new password.',
                    'label' => 'New password',
                ],
                'invalid_message' => 'Password confirmation does not match.',
                'second_options' => [
                    'help' => 'Repeat the password you entered above.',
                    'label' => 'Confirm password',
                ],
                'type' => PasswordType::class,
            ])
        ;
    }
}
