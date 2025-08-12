<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\Conversation;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Obtenir les messages d'une conversation avec pagination
     */
    public function findMessagesByConversation(Conversation $conversation, int $page = 1, int $limit = 50): array
    {
        $offset = ($page - 1) * $limit;

        return $this->createQueryBuilder('m')
            ->leftJoin('m.auteur', 'a')
            ->addSelect('a')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.createdAt', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtenir les derniers messages d'une conversation
     */
    public function findDerniersMessages(Conversation $conversation, int $limit = 20): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.auteur', 'a')
            ->addSelect('a')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter les messages non lus dans une conversation pour un utilisateur
     */
    public function countMessagesNonLus(Conversation $conversation, Utilisateur $utilisateur): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.conversation = :conversation')
            ->andWhere('m.auteur != :utilisateur')
            ->andWhere('m.lu = false')
            ->setParameter('conversation', $conversation)
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Marquer tous les messages d'une conversation comme lus pour un utilisateur
     */
    public function marquerMessagesCommeLus(Conversation $conversation, Utilisateur $utilisateur): int
    {
        return $this->createQueryBuilder('m')
            ->update()
            ->set('m.lu', true)
            ->set('m.luAt', ':now')
            ->where('m.conversation = :conversation')
            ->andWhere('m.auteur != :utilisateur')
            ->andWhere('m.lu = false')
            ->setParameter('conversation', $conversation)
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute();
    }

    /**
     * Obtenir les messages plus récents qu'une date donnée pour une conversation
     */
    public function findMessagesDepuis(Conversation $conversation, \DateTimeInterface $depuis): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.auteur', 'a')
            ->addSelect('a')
            ->where('m.conversation = :conversation')
            ->andWhere('m.createdAt > :depuis')
            ->setParameter('conversation', $conversation)
            ->setParameter('depuis', $depuis)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Rechercher des messages par contenu dans une conversation
     */
    public function rechercherMessages(Conversation $conversation, string $terme): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.auteur', 'a')
            ->addSelect('a')
            ->where('m.conversation = :conversation')
            ->andWhere('m.contenu LIKE :terme')
            ->setParameter('conversation', $conversation)
            ->setParameter('terme', '%' . $terme . '%')
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtenir le nombre total de messages dans une conversation
     */
    public function countMessagesInConversation(Conversation $conversation): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouver les messages modifiés dans une conversation
     */
    public function findMessagesModifies(Conversation $conversation): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.auteur', 'a')
            ->addSelect('a')
            ->where('m.conversation = :conversation')
            ->andWhere('m.isEdited = true')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.editedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtenir les statistiques de messagerie pour un utilisateur
     */
    public function getStatistiquesUtilisateur(Utilisateur $utilisateur): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select([
                'COUNT(m.id) as totalMessages',
                'COUNT(CASE WHEN m.lu = false AND m.auteur != :utilisateur THEN 1 END) as messagesNonLus',
                'COUNT(CASE WHEN m.auteur = :utilisateur THEN 1 END) as messagesEnvoyes',
                'COUNT(CASE WHEN m.auteur != :utilisateur THEN 1 END) as messagesRecus'
            ])
            ->leftJoin('m.conversation', 'c')
            ->where('c.acheteur = :utilisateur OR c.vendeur = :utilisateur')
            ->andWhere('c.isActive = true')
            ->setParameter('utilisateur', $utilisateur);

        return $qb->getQuery()->getSingleResult();
    }

    public function save(Message $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Message $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
