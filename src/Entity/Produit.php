<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?float $prixInitial = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $images = [];

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Etat $etat = null;

    /**
     * @var Collection<int, Appartient>
     */
    #[ORM\OneToMany(targetEntity: Appartient::class, mappedBy: 'produit')]
    private Collection $appartients;

    /**
     * @var Collection<int, Signale>
     */
    #[ORM\OneToMany(targetEntity: Signale::class, mappedBy: 'produit')]
    private Collection $signales;

    /**
     * @var Collection<int, ProduitCategorie>
     */
    #[ORM\OneToMany(targetEntity: ProduitCategorie::class, mappedBy: 'produit')]
    private Collection $produitCategories;

    /**
     * @var Collection<int, Favoris>
     */
    #[ORM\OneToMany(targetEntity: Favoris::class, mappedBy: 'produit')]
    private Collection $favoris;

    /**
     * @var Collection<int, Conversation>
     */
    #[ORM\OneToMany(targetEntity: Conversation::class, mappedBy: 'produit')]
    private Collection $conversations;

    public function __construct()
    {
        $this->appartients = new ArrayCollection();
        $this->signales = new ArrayCollection();
        $this->produitCategories = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->images = $this->images ?? [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrixInitial(): ?float
    {
        return $this->prixInitial;
    }

    public function setPrixInitial(float $prixInitial): static
    {
        $this->prixInitial = $prixInitial;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, Appartient>
     */
    public function getAppartients(): Collection
    {
        return $this->appartients;
    }

    public function addAppartient(Appartient $appartient): static
    {
        if (!$this->appartients->contains($appartient)) {
            $this->appartients->add($appartient);
            $appartient->setProduit($this);
        }

        return $this;
    }

    public function removeAppartient(Appartient $appartient): static
    {
        if ($this->appartients->removeElement($appartient)) {
            // set the owning side to null (unless already changed)
            if ($appartient->getProduit() === $this) {
                $appartient->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Signale>
     */
    public function getSignales(): Collection
    {
        return $this->signales;
    }

    public function addSignale(Signale $signale): static
    {
        if (!$this->signales->contains($signale)) {
            $this->signales->add($signale);
            $signale->setProduit($this);
        }

        return $this;
    }

    public function removeSignale(Signale $signale): static
    {
        if ($this->signales->removeElement($signale)) {
            // set the owning side to null (unless already changed)
            if ($signale->getProduit() === $this) {
                $signale->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProduitCategorie>
     */
    public function getProduitCategories(): Collection
    {
        return $this->produitCategories;
    }

    public function addProduitCategory(ProduitCategorie $produitCategory): static
    {
        if (!$this->produitCategories->contains($produitCategory)) {
            $this->produitCategories->add($produitCategory);
            $produitCategory->setProduit($this);
        }

        return $this;
    }

    public function removeProduitCategory(ProduitCategorie $produitCategory): static
    {
        if ($this->produitCategories->removeElement($produitCategory)) {
            // set the owning side to null (unless already changed)
            if ($produitCategory->getProduit() === $this) {
                $produitCategory->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Favoris>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(Favoris $favori): static
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
            $favori->setProduit($this);
        }

        return $this;
    }

    public function removeFavori(Favoris $favori): static
    {
        if ($this->favoris->removeElement($favori)) {
            // set the owning side to null (unless already changed)
            if ($favori->getProduit() === $this) {
                $favori->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return array|null
     */
    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): static
    {
        $this->images = $images;

        return $this;
    }

    public function addImage(string $imagePath): static
    {
        if ($this->images === null) {
            $this->images = [];
        }
        
        if (!in_array($imagePath, $this->images) && count($this->images) < 3) {
            $this->images[] = $imagePath;
        }

        return $this;
    }

    public function removeImage(string $imagePath): static
    {
        if ($this->images !== null) {
            $key = array_search($imagePath, $this->images);
            if ($key !== false) {
                unset($this->images[$key]);
                $this->images = array_values($this->images); // Réindexer le tableau
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
            $conversation->setProduit($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversations->removeElement($conversation)) {
            if ($conversation->getProduit() === $this) {
                $conversation->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * Obtenir le nombre de conversations actives pour ce produit
     */
    public function getNombreConversations(): int
    {
        return $this->conversations->filter(function (Conversation $conversation) {
            return $conversation->isActive();
        })->count();
    }

    /**
     * Vérifier si un utilisateur a déjà une conversation pour ce produit
     */
    public function aConversationAvec(Utilisateur $utilisateur): bool
    {
        foreach ($this->conversations as $conversation) {
            if ($conversation->isActive() && 
                ($conversation->getAcheteur() === $utilisateur || $conversation->getVendeur() === $utilisateur)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Obtenir le propriétaire du produit
     */
    public function getProprietaire(): ?Utilisateur
    {
        foreach ($this->appartients as $appartient) {
            return $appartient->getUtilisateur();
        }
        return null;
    }
}
