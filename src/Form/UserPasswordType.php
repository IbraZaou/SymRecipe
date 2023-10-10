<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class UserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_options' => [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Mot de passe actuel'
                ],
                'label' => false,
            ],
            'second_options' => [      
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Confirmation du mot de passe actuel'
                ],
                'label' => false,
            ],
            'invalid_message' => 'Les mots de passe ne correspondent pas.'
        ])
        
        ->add('newPassword', PasswordType::class, [
            'attr' => [
                'class' => 'form-control',
            'placeholder' => 'Nouveau mot de passe super secret'
            ],
            'label' => false,
            'label_attr' => ['class' => 'form-label mt-4'],
            'constraints' => [
                new Assert\NotBlank(),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Le mot de passe doit avoir au moins {{ limit }} caractères.',
                    // longueur maximale peut également être spécifiée ici
                ]),
                new Regex([
                    'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/',
                    'message' => 'Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial (@, $, !, %, *, ?, &,).',
                ]),]
        ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary mt-4'
                ],
                'label' => "Modifier mon mot de passe"
            ]);
    }
}
