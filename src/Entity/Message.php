<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'message')]
#[ORM\Index(name: 'idx_message_conversation', columns: ['conversation_id'])]
#[ORM\Index(name: 'idx_message_auteur', columns: ['auteur_id'])]
#[ORM\Index(name: 'idx_message_lu', columns: ['lu'])]
#[ORM\Index(name: 'idx_message_created_at', columns: ['created_at'])]
#[ORM\HasLifecycleCallbacks]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Conversation::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(name: 'conversation_id', referencedColumnName: 'id', nullable: false)]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'auteur_id', referencedColumnName: 'id', nullable: false)]
    private ?Utilisateur $auteur = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $lu = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $luAt = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isEdited = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $editedAt = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['default' => 'text'])]
    private string $type = 'text'; // text, image, file, system

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        if ($this->isEdited) {
            $this->editedAt = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;
        return $this;
    }

    public function getAuteur(): ?Utilisateur
    {
        return $this->auteur;
    }

    public function setAuteur(?Utilisateur $auteur): static
    {
        $this->auteur = $auteur;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function isLu(): bool
    {
        return $this->lu;
    }

    public function setLu(bool $lu): static
    {
        $this->lu = $lu;
        if ($lu && !$this->luAt) {
            $this->luAt = new \DateTime();
        }
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

    public function getLuAt(): ?\DateTimeInterface
    {
        return $this->luAt;
    }

    public function setLuAt(?\DateTimeInterface $luAt): static
    {
        $this->luAt = $luAt;
        return $this;
    }

    public function isEdited(): bool
    {
        return $this->isEdited;
    }

    public function setIsEdited(bool $isEdited): static
    {
        $this->isEdited = $isEdited;
        return $this;
    }

    public function getEditedAt(): ?\DateTimeInterface
    {
        return $this->editedAt;
    }

    public function setEditedAt(?\DateTimeInterface $editedAt): static
    {
        $this->editedAt = $editedAt;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Obtenir le temps écoulé depuis la création du message
     */
    public function getTempsEcoule(): string
    {
        $now = new \DateTime();
        $diff = $now->diff($this->createdAt);

        if ($diff->days > 0) {
            return $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        } elseif ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        } else {
            return 'À l\'instant';
        }
    }

    /**
     * Vérifier si le message peut être modifié (dans les 5 minutes)
     */
    public function peutEtreModifie(): bool
    {
        $now = new \DateTime();
        $diff = $now->diff($this->createdAt);
        return $diff->i < 5 && $diff->h === 0 && $diff->days === 0;
    }

    /**
     * Marquer le message comme lu par un utilisateur spécifique
     */
    public function marquerCommeLu(Utilisateur $utilisateur): void
    {
        // Ne marquer comme lu que si l'utilisateur n'est pas l'auteur
        if ($this->auteur !== $utilisateur && !$this->lu) {
            $this->setLu(true);
        }
    }
}
