<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\Table(name: 'conversation')]
#[ORM\Index(name: 'idx_conversation_participants', columns: ['acheteur_id', 'vendeur_id'])]
#[ORM\Index(name: 'idx_conversation_produit', columns: ['produit_id'])]
#[ORM\HasLifecycleCallbacks]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'acheteur_id', referencedColumnName: 'id', nullable: false)]
    private ?Utilisateur $acheteur = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'vendeur_id', referencedColumnName: 'id', nullable: false)]
    private ?Utilisateur $vendeur = null;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: 'produit_id', referencedColumnName: 'id', nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversation', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAcheteur(): ?Utilisateur
    {
        return $this->acheteur;
    }

    public function setAcheteur(?Utilisateur $acheteur): static
    {
        $this->acheteur = $acheteur;
        return $this;
    }

    public function getVendeur(): ?Utilisateur
    {
        return $this->vendeur;
    }

    public function setVendeur(?Utilisateur $vendeur): static
    {
        $this->vendeur = $vendeur;
        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }
        return $this;
    }

    /**
     * Obtenir le dernier message de la conversation
     */
    public function getDernierMessage(): ?Message
    {
        $messages = $this->messages->toArray();
        return empty($messages) ? null : end($messages);
    }

    /**
     * Compter les messages non lus pour un utilisateur
     */
    public function getNombreMessagesNonLus(Utilisateur $utilisateur): int
    {
        $count = 0;
        foreach ($this->messages as $message) {
            if ($message->getAuteur() !== $utilisateur && !$message->isLu()) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Obtenir l'autre participant (pas l'utilisateur courant)
     */
    public function getAutreParticipant(Utilisateur $utilisateurCourant): ?Utilisateur
    {
        if ($this->acheteur === $utilisateurCourant) {
            return $this->vendeur;
        }
        if ($this->vendeur === $utilisateurCourant) {
            return $this->acheteur;
        }
        return null;
    }

    /**
     * Vérifier si l'utilisateur participe à cette conversation
     */
    public function aParticipant(Utilisateur $utilisateur): bool
    {
        return $this->acheteur === $utilisateur || $this->vendeur === $utilisateur;
    }

    /**
     * Marquer tous les messages de la conversation comme lus pour un utilisateur
     */
    public function marquerCommeLu(Utilisateur $utilisateur): void
    {
        foreach ($this->messages as $message) {
            if ($message->getAuteur() !== $utilisateur && !$message->isLu()) {
                $message->setLu(true);
            }
        }
    }
}
