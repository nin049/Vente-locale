<?php

namespace App\Repository;

use App\Entity\Signale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Signale>
 */
class SignaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Signale::class);
    }

    /**
     * Trouve les signalements avec tous les détails nécessaires
     */
    public function findSignalementsAvecDetails(array $criteria = [], int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.produit', 'p')
            ->leftJoin('s.signalement', 'sig')
            ->leftJoin('s.signalePar', 'sp')
            ->leftJoin('s.traitePar', 'tp')
            ->addSelect('p', 'sig', 'sp', 'tp')
            ->orderBy('s.dateSignalement', 'DESC');

        // Appliquer les critères de filtrage
        if (isset($criteria['statut'])) {
            $qb->andWhere('s.statut = :statut')
               ->setParameter('statut', $criteria['statut']);
        }

        // Pagination
        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve les signalements par statut
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('s.dateSignalement', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les signalements non traités les plus anciens
     */
    public function findSignalementsEnAttenteLessPlusAnciens(int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.produit', 'p')
            ->leftJoin('s.signalement', 'sig')
            ->leftJoin('s.signalePar', 'sp')
            ->addSelect('p', 'sig', 'sp')
            ->andWhere('s.statut = :statut')
            ->setParameter('statut', 'en_attente')
            ->orderBy('s.dateSignalement', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les signalements d'un produit spécifique
     */
    public function findByProduit(int $produitId): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.signalement', 'sig')
            ->leftJoin('s.signalePar', 'sp')
            ->addSelect('sig', 'sp')
            ->andWhere('s.produit = :produitId')
            ->setParameter('produitId', $produitId)
            ->orderBy('s.dateSignalement', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des signalements
     */
    public function getStatistiques(): array
    {
        $result = $this->createQueryBuilder('s')
            ->select('s.statut, COUNT(s.id) as count')
            ->groupBy('s.statut')
            ->getQuery()
            ->getArrayResult();

        $stats = [
            'en_attente' => 0,
            'traite' => 0,
            'rejete' => 0,
            'total' => 0
        ];

        foreach ($result as $row) {
            $stats[$row['statut']] = (int) $row['count'];
            $stats['total'] += (int) $row['count'];
        }

        return $stats;
    }

    /**
     * Trouve les signalements traités par un administrateur
     */
    public function findByAdmin(int $adminId): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.produit', 'p')
            ->leftJoin('s.signalement', 'sig')
            ->addSelect('p', 'sig')
            ->andWhere('s.traitePar = :adminId')
            ->setParameter('adminId', $adminId)
            ->orderBy('s.dateTraitement', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
