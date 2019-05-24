<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ForgotPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['autofocus' => 'autofocus'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
                'help' => 'Enter your registered email address.',
                'label' => 'Email address',
            ])
        ;
    }
}
