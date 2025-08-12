<?php

namespace App\Form;

use App\Entity\Signale;
use App\Entity\Signalement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SignalementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('signalement', EntityType::class, [
                'class' => Signalement::class,
                'choice_label' => 'natureSignalement',
                'label' => 'Nature du signalement',
                'placeholder' => 'Choisissez la nature du signalement',
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner la nature du signalement.'
                    ])
                ]
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire (optionnel)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Décrivez en détail le problème rencontré avec cette annonce...'
                ],
                'constraints' => [
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Le commentaire ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Signaler cette annonce',
                'attr' => [
                    'class' => 'btn btn-danger'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Signale::class,
        ]);
    }
}
