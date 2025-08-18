<?php

namespace App\Form;

use App\Entity\Competence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CompetenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la compétence',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Maçon, Électricien, etc.'
                ],
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Description détaillée de la compétence'
                ],
                'required' => false
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Gros œuvre' => 'Gros œuvre',
                    'Second œuvre' => 'Second œuvre',
                    'Finitions' => 'Finitions',
                    'Technique' => 'Technique',
                    'Spécialisée' => 'Spécialisée',
                    'Autre' => 'Autre'
                ],
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'placeholder' => 'Sélectionner une catégorie'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competence::class,
        ]);
    }
} 