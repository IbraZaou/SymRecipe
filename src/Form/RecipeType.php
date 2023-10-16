<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Entity\Category;
use App\Entity\Ustensil;
use App\Repository\IngredientRepository;
use App\Repository\UstensilRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RecipeType extends AbstractType
{
    private $token;


    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'attr' => [
                'class' => 'form-control',
                'minlength' => '2',
                'maxlength' => '50',
                'placeholder' => 'Nom'
            ],
            'label' => false,
            'label_attr' => [
                'class' => 'form-label mt-4'
            ],
            'constraints' => [
                new Assert\Length(['min' => 2, 'max' => 50]),
                new Assert\NotBlank()
            ]
            ])
            ->add('time', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 1440,
                'placeholder' => 'Temps (en minutes)'
                ],
                'label' => false,
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'required' => false,
                'constraints' => [
                    new Assert\Positive(),
                    new Assert\LessThan(1441)
            ]
            ])
            ->add('nbPeople', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 50,
                'placeholder' => 'Nombres de personnes'
                ],
                'required' => false,
                'label' => false,
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Positive(),
                    new Assert\LessThan(51)
            ]])
            ->add('difficulty', RangeType::class, [
                'attr' => [
                    'class' => 'form-range',
                    'min' => 1,
                    'max' => 5
                ],
                'required' => false,
                'label' => 'Niveau de difficulté sur 5',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Positive(),
                    new Assert\LessThan(5)
            ]
            ])
            ->add('description', TextareaType::class,
            [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 5,
                'placeholder' => 'Description de la recette'
                ],
                'label' => false,
                'label_attr' => [
                    'class' => 'form-range mt-4'
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('price', MoneyType::class, [
                'attr' => [
                    'class' => 'form-control',
                'placeholder' => 'Prix €'
                ],
                'required' => false,
                'label' => false,
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Positive(),
                    new Assert\LessThan(1001)
            ]
        ])
            ->add('isFavorite', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'required' => false,
                'label' => "Favoris",
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'constraints' => [
                    new Assert\NotNull()
            ]
            ])
            ->add('isPublic', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'required' => false,
                'label' => "Public",
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'constraints' => [
                    new Assert\NotNull()
            ]
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image de la recette',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ]
            ])
            ->add('ingredients', EntityType::class, [
                'class' => Ingredient::class,
                'query_builder' => function(IngredientRepository $r) {
                    return $r->createQueryBuilder('i')
                    ->where('i.user = :user')
                    ->orderBy('i.name', 'ASC')
                    ->setParameter('user', $this->token->getToken()->getUser());
                },
                'label' => "Les ingrédients",
                'label_attr' => [
                    'class' => 'form-label mt-4',
                    'id' => 'choix_ingredient'
                ],
                'choice_label' => 'name',
                'multiple' => 'true',
                'expanded' => 'true'
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',  
                'placeholder' => 'Sélectionnez une catégorie', 
                'attr' => [
                    'class' => 'form-control',
                ],
                'label' => 'Catégorie',
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
            ])
            ->add('ustensils', EntityType::class, [
                'class' => Ustensil::class,
                'query_builder' => function(UstensilRepository $r) {
                    return $r->createQueryBuilder('i')
                    ->orderBy('i.name', 'ASC');
                },
                'label' => "Les ustensils",
                'label_attr' => [
                    'class' => 'form-label mt-4',
                    'id' => 'choix_ingredient'
                ],
                'choice_label' => 'name',
                'multiple' => 'true',
                'expanded' => 'true'
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary mt-4'
                ],
                'label' => "Créer ma recette"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
