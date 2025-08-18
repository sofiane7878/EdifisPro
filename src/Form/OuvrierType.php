<?php

namespace App\Form;

use App\Entity\Ouvrier;
use App\Entity\Competence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class OuvrierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_ouvrier', TextType::class, [
                'label' => 'Nom de l\'ouvrier',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom de famille']
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'mapped' => false, // Ce champ n'est pas mappé à l'entité Ouvrier
                'attr' => [
                    'class' => 'form-control', 
                    'placeholder' => 'Prénom',
                    'data-email-generator' => 'true'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email du compte utilisateur',
                'required' => false,
                'mapped' => false, // Ce champ n'est pas mappé à l'entité Ouvrier
                'attr' => [
                    'class' => 'form-control', 
                    'placeholder' => 'nom.prenom@btp.com',
                    'readonly' => 'readonly'
                ]
            ])
            ->add('createUserAccount', CheckboxType::class, [
                'label' => 'Créer un compte utilisateur automatiquement',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-check-input'],
                'data' => true // Coché par défaut
            ])
            ->add('competences', EntityType::class, [
                'class' => Competence::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'attr' => ['class' => 'form-check'],
                'placeholder' => 'Sélectionnez les compétences'
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Ouvrier' => 'Ouvrier',
                    'Chef' => 'Chef',
                ],
                'expanded' => false, // Menu déroulant
                'multiple' => false, // Un seul choix possible
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Choisissez un rôle',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ouvrier::class,
        ]);
    }
}
