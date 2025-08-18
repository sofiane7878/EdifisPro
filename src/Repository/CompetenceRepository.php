<?php

namespace App\Repository;

use App\Entity\Competence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competence>
 *
 * @method Competence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Competence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Competence[]    findAll()
 * @method Competence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competence::class);
    }

    public function save(Competence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Competence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve les compétences par catégorie
     */
    public function findByCategorie(string $categorie): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les compétences utilisées par au moins un ouvrier
     */
    public function findUtilisees(): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.ouvriers', 'o')
            ->groupBy('c.id')
            ->having('COUNT(o.id) > 0')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les compétences non utilisées
     */
    public function findNonUtilisees(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.ouvriers', 'o')
            ->groupBy('c.id')
            ->having('COUNT(o.id) = 0')
            ->getQuery()
            ->getResult();
    }
} 