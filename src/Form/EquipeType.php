<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Entity\Ouvrier;
use App\Entity\Competence;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class EquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_equipe')
            ->add('competences', EntityType::class, [
                'class' => Competence::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'attr' => ['class' => 'form-check'],
                'placeholder' => 'Sélectionnez les compétences'
            ])
            ->add('ouvriers', EntityType::class, [
                'class' => Ouvrier::class,
                'choice_label' => function (Ouvrier $ouvrier) {
                    $competences = $ouvrier->getCompetences()->map(fn($c) => $c->getNom())->toArray();
                    $competencesStr = !empty($competences) ? ' - Compétences: ' . implode(', ', $competences) : ' - Aucune compétence';
                    return $ouvrier->getNomOuvrier() . $competencesStr;
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('o')
                        ->leftJoin('o.competences', 'c')
                        ->addSelect('c')
                        ->where('o.role = :role')
                        ->setParameter('role', 'Ouvrier')
                        ->orderBy('o.nom_ouvrier', 'ASC');
                },  
                'multiple' => true,               
                'expanded' => true,
                'attr' => ['class' => 'form-check'],
            ])
            // ->add('nombre')
            ->add('chef_equipe', EntityType::class, [
                'class' => Ouvrier::class,
                'choice_label' => function (Ouvrier $ouvrier) {
                    $competences = $ouvrier->getCompetences()->map(fn($c) => $c->getNom())->toArray();
                    $competencesStr = !empty($competences) ? ' - Compétences: ' . implode(', ', $competences) : ' - Aucune compétence';
                    return $ouvrier->getNomOuvrier() . $competencesStr;
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('o')
                        ->leftJoin('o.competences', 'c')
                        ->addSelect('c')
                        ->where('o.role = :role')
                        ->setParameter('role', 'Chef')
                        ->orderBy('o.nom_ouvrier', 'ASC');
                },
                'placeholder' => 'Sélectionnez un chef d\'équipe',
                'attr' => ['class' => 'form-select'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
        ]);
    }
}
