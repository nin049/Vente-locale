<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Etat;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\File;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                    'placeholder' => 'Entrez le nom du produit'
                ]
            ])
            ->add('libelle', TextType::class, [
                'label' => 'Libellé',
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                    'placeholder' => 'Entrez le libellé du produit'
                ]
            ])
            ->add('prixInitial', NumberType::class, [
                'label' => 'Prix initial (€)',
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                    'placeholder' => '0.00',
                    'step' => '0.01',
                    'min' => '0'
                ]
            ])
            ->add('categories', EntityType::class, [
                'label' => 'Catégorie',
                'class' => Categorie::class,
                'choice_label' => 'libelle',
                'multiple' => false,
                'expanded' => false,
                'mapped' => false,
                'placeholder' => 'Choisir une catégorie',
                'attr' => [
                    'class' => 'hidden-categories-select',
                    'style' => 'display: none;'
                ]
            ])
            ->add('images', FileType::class, [
                'label' => 'Photos (2 à 3 maximum)',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100',
                    'accept' => 'image/*',
                    'multiple' => true
                ],
                'constraints' => [
                    new Count([
                        'max' => 5, // Un peu plus permissif pour éviter les rejets trop stricts
                        'maxMessage' => 'Vous ne pouvez télécharger que {{ limit }} images maximum.',
                    ]),
                    new All([
                        new File([
                            'maxSize' => '10M', // Plus permissif, la validation stricte se fait dans le contrôleur
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'image/webp'
                            ],
                            'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, GIF, WEBP)',
                            'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). La taille maximale autorisée est de {{ limit }} {{ suffix }}.'
                        ])
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                    'rows' => 5,
                    'placeholder' => 'Décrivez votre produit...'
                ]
            ])
            ->add('etat', EntityType::class, [
                'label' => 'Disponibilité',
                'class' => Etat::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Choisissez la disponibilité',
                'required' => false,
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer l\'annonce',
                'attr' => [
                    'class' => 'bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
