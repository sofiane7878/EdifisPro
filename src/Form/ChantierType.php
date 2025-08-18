<?php

namespace App\Form;

use App\Entity\Chantier;
use App\Entity\Ouvrier;
use App\Entity\Competence;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\ORM\EntityRepository;

class ChantierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du Chantier',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Construction immeuble'],
                'required' => true
            ])
            ->add('competencesRequises', EntityType::class, [
                'class' => Competence::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'attr' => ['class' => 'form-check'],
                'placeholder' => 'Sélectionnez les compétences prérequises'
            ])
            ->add('effectif_requis', IntegerType::class, [
                'label' => 'Effectif Requis',
                'attr' => ['class' => 'form-control', 'min' => 1],
                'required' => true
            ])
            ->add('date_debut', DateType::class, [
                'label' => 'Date de Début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'required' => true
            ])
            ->add('date_fin', DateType::class, [
                'label' => 'Date de Fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'required' => true
            ])
            ->add('chef_chantier', EntityType::class, [
                'class' => Ouvrier::class,
                'choice_label' => function (Ouvrier $ouvrier) {
                    $competences = $ouvrier->getCompetences()->map(fn($c) => $c->getNom())->toArray();
                    $competencesStr = !empty($competences) ? ' - Compétences: ' . implode(', ', $competences) : ' - Aucune compétence';
                    return $ouvrier->getNomOuvrier() . $competencesStr;
                },
                'label' => 'Chef de Chantier',
                'placeholder' => 'Sélectionnez un chef',
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('o')
                        ->leftJoin('o.competences', 'c')
                        ->addSelect('c')
                        ->where('o.role = :role')
                        ->setParameter('role', 'Chef')
                        ->orderBy('o.nom_ouvrier', 'ASC');
                },
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du Chantier',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, WebP)',
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chantier::class,
        ]);
    }
}
