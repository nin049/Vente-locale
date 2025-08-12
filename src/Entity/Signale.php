<?php

namespace App\Entity;

use App\Repository\SignaleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SignaleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Signale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'signales')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(inversedBy: 'signales')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Signalement $signalement = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $signalePar = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSignalement = null;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['default' => 'en_attente'])]
    private string $statut = 'en_attente';

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateTraitement = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Utilisateur $traitePar = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reponseAdmin = null;

    public function __construct()
    {
        $this->dateSignalement = new \DateTime();
        $this->statut = 'en_attente';
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if ($this->dateSignalement === null) {
            $this->dateSignalement = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSignalement(): ?Signalement
    {
        return $this->signalement;
    }

    public function setSignalement(?Signalement $signalement): static
    {
        $this->signalement = $signalement;
        return $this;
    }

    public function getSignalePar(): ?Utilisateur
    {
        return $this->signalePar;
    }

    public function setSignalePar(?Utilisateur $signalePar): static
    {
        $this->signalePar = $signalePar;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getDateSignalement(): ?\DateTimeInterface
    {
        return $this->dateSignalement;
    }

    public function setDateSignalement(\DateTimeInterface $dateSignalement): static
    {
        $this->dateSignalement = $dateSignalement;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateTraitement(): ?\DateTimeInterface
    {
        return $this->dateTraitement;
    }

    public function setDateTraitement(?\DateTimeInterface $dateTraitement): static
    {
        $this->dateTraitement = $dateTraitement;
        return $this;
    }

    public function getTraitePar(): ?Utilisateur
    {
        return $this->traitePar;
    }

    public function setTraitePar(?Utilisateur $traitePar): static
    {
        $this->traitePar = $traitePar;
        return $this;
    }

    public function getReponseAdmin(): ?string
    {
        return $this->reponseAdmin;
    }

    public function setReponseAdmin(?string $reponseAdmin): static
    {
        $this->reponseAdmin = $reponseAdmin;
        return $this;
    }

    /**
     * Méthodes utilitaires pour les statuts
     */
    public function isEnAttente(): bool
    {
        return $this->statut === 'en_attente';
    }

    public function isTraite(): bool
    {
        return $this->statut === 'traite';
    }

    public function isRejete(): bool
    {
        return $this->statut === 'rejete';
    }

    public function marquerCommeTraite(Utilisateur $admin, ?string $reponse = null): static
    {
        $this->statut = 'traite';
        $this->dateTraitement = new \DateTime();
        $this->traitePar = $admin;
        if ($reponse) {
            $this->reponseAdmin = $reponse;
        }
        return $this;
    }

    public function marquerCommeRejete(Utilisateur $admin, ?string $reponse = null): static
    {
        $this->statut = 'rejete';
        $this->dateTraitement = new \DateTime();
        $this->traitePar = $admin;
        if ($reponse) {
            $this->reponseAdmin = $reponse;
        }
        return $this;
    }

    /**
     * Obtenir le statut formaté pour l'affichage
     */
    public function getStatutFormate(): string
    {
        return match($this->statut) {
            'en_attente' => 'En attente',
            'traite' => 'Traité',
            'rejete' => 'Rejeté',
            default => 'Inconnu'
        };
    }

    /**
     * Obtenir la classe CSS pour le statut
     */
    public function getStatutCssClass(): string
    {
        return match($this->statut) {
            'en_attente' => 'warning',
            'traite' => 'success',
            'rejete' => 'danger',
            default => 'secondary'
        };
    }
}
