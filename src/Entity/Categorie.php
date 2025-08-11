<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /**
     * @var Collection<int, ProduitCategorie>
     */
    #[ORM\OneToMany(targetEntity: ProduitCategorie::class, mappedBy: 'categorie')]
    private Collection $produitCategories;

    public function __construct()
    {
        $this->produitCategories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function __toString(): string
    {
        return $this->libelle ?? '';
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
            $produitCategory->setCategorie($this);
        }

        return $this;
    }

    public function removeProduitCategory(ProduitCategorie $produitCategory): static
    {
        if ($this->produitCategories->removeElement($produitCategory)) {
            // set the owning side to null (unless already changed)
            if ($produitCategory->getCategorie() === $this) {
                $produitCategory->setCategorie(null);
            }
        }

        return $this;
    }
}
