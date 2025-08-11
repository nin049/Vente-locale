<?php

namespace App\Form;

use App\Entity\Adresse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class AdresseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rue', TextType::class, [
                'label' => 'Adresse (rue, numéro)',
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                    'placeholder' => '123 Rue de la Paix'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une adresse.',
                    ]),
                    new Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'L\'adresse doit faire au moins {{ limit }} caractères.',
                        'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                    'placeholder' => 'Paris'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une ville.',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'La ville doit faire au moins {{ limit }} caractères.',
                        'maxMessage' => 'La ville ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('codePostal', IntegerType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                    'placeholder' => '75000'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un code postal.',
                    ]),
                    new Regex([
                        'pattern' => '/^\d{5}$/',
                        'message' => 'Le code postal doit contenir exactement 5 chiffres.',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer l\'adresse',
                'attr' => [
                    'class' => 'bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adresse::class,
        ]);
    }
}
