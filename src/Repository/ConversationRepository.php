<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Utilisateur;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * Trouver une conversation existante entre deux utilisateurs pour un produit
     */
    public function findConversationExistante(Utilisateur $acheteur, Utilisateur $vendeur, Produit $produit): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->where('c.acheteur = :acheteur')
            ->andWhere('c.vendeur = :vendeur')
            ->andWhere('c.produit = :produit')
            ->andWhere('c.isActive = true')
            ->setParameter('acheteur', $acheteur)
            ->setParameter('vendeur', $vendeur)
            ->setParameter('produit', $produit)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Obtenir toutes les conversations d'un utilisateur (triées par date de dernière activité)
     */
    public function findConversationsByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.messages', 'm')
            ->leftJoin('c.acheteur', 'a')
            ->leftJoin('c.vendeur', 'v')
            ->leftJoin('c.produit', 'p')
            ->addSelect('m', 'a', 'v', 'p')
            ->where('c.acheteur = :utilisateur OR c.vendeur = :utilisateur')
            ->andWhere('c.isActive = true')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter le nombre total de messages non lus pour un utilisateur
     */
    public function countMessagesNonLusByUtilisateur(Utilisateur $utilisateur): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(m.id)')
            ->leftJoin('c.messages', 'm')
            ->where('(c.acheteur = :utilisateur OR c.vendeur = :utilisateur)')
            ->andWhere('c.isActive = true')
            ->andWhere('m.auteur != :utilisateur')
            ->andWhere('m.lu = false')
            ->setParameter('utilisateur', $utilisateur);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Trouver les conversations avec des messages non lus pour un utilisateur
     */
    public function findConversationsAvecMessagesNonLus(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.messages', 'm')
            ->leftJoin('c.acheteur', 'a')
            ->leftJoin('c.vendeur', 'v')
            ->leftJoin('c.produit', 'p')
            ->addSelect('m', 'a', 'v', 'p')
            ->where('(c.acheteur = :utilisateur OR c.vendeur = :utilisateur)')
            ->andWhere('c.isActive = true')
            ->andWhere('m.auteur != :utilisateur')
            ->andWhere('m.lu = false')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver une conversation par ID avec vérification de participation de l'utilisateur
     */
    public function findConversationAvecVerificationParticipation(int $conversationId, Utilisateur $utilisateur): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.messages', 'm')
            ->leftJoin('c.acheteur', 'a')
            ->leftJoin('c.vendeur', 'v')
            ->leftJoin('c.produit', 'p')
            ->addSelect('m', 'a', 'v', 'p')
            ->where('c.id = :conversationId')
            ->andWhere('(c.acheteur = :utilisateur OR c.vendeur = :utilisateur)')
            ->andWhere('c.isActive = true')
            ->setParameter('conversationId', $conversationId)
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouver toutes les conversations pour un produit spécifique
     */
    public function findConversationsByProduit(Produit $produit): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.acheteur', 'a')
            ->leftJoin('c.vendeur', 'v')
            ->addSelect('a', 'v')
            ->where('c.produit = :produit')
            ->andWhere('c.isActive = true')
            ->setParameter('produit', $produit)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(Conversation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Conversation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
