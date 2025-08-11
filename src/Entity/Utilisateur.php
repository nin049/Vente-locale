<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    private ?Avis $avis = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Adresse $adresse = null;

    /**
     * @var Collection<int, Alerte>
     */
    #[ORM\OneToMany(targetEntity: Alerte::class, mappedBy: 'utilisateur')]
    private Collection $alertes;

    /**
     * @var Collection<int, Paiement>
     */
    #[ORM\OneToMany(targetEntity: Paiement::class, mappedBy: 'utilisateur')]
    private Collection $paiements;

    /**
     * @var Collection<int, Possede>
     */
    #[ORM\OneToMany(targetEntity: Possede::class, mappedBy: 'utilisateur', cascade: ['persist'])]
    private Collection $possedes;

    /**
     * @var Collection<int, Appartient>
     */
    #[ORM\OneToMany(targetEntity: Appartient::class, mappedBy: 'utilisateur')]
    private Collection $appartients;

    /**
     * @var Collection<int, Favoris>
     */
    #[ORM\OneToMany(targetEntity: Favoris::class, mappedBy: 'utilisateur')]
    private Collection $favoris;

    public function __construct()
    {
        $this->alertes = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->possedes = new ArrayCollection();
        $this->appartients = new ArrayCollection();
        $this->favoris = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = ['ROLE_USER']; // Rôle par défaut
        
        // Vérifier si la collection possedes est initialisée
        if ($this->possedes !== null && !$this->possedes->isEmpty()) {
            $customRoles = $this->possedes->map(function (Possede $possede) {
                return $possede->getRole()?->getNom();
            })->toArray();
            
            $roles = array_merge($roles, array_filter($customRoles));
        }

        return array_unique($roles);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getAvis(): ?Avis
    {
        return $this->avis;
    }

    public function setAvis(?Avis $avis): static
    {
        $this->avis = $avis;

        return $this;
    }

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * @return Collection<int, Alerte>
     */
    public function getAlertes(): Collection
    {
        return $this->alertes;
    }

    public function addAlerte(Alerte $alerte): static
    {
        if (!$this->alertes->contains($alerte)) {
            $this->alertes->add($alerte);
            $alerte->setUtilisateur($this);
        }

        return $this;
    }

    public function removeAlerte(Alerte $alerte): static
    {
        if ($this->alertes->removeElement($alerte)) {
            // set the owning side to null (unless already changed)
            if ($alerte->getUtilisateur() === $this) {
                $alerte->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): static
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setUtilisateur($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): static
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getUtilisateur() === $this) {
                $paiement->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Possede>
     */
    public function getPossedes(): Collection
    {
        return $this->possedes;
    }

    public function addPossede(Possede $possede): static
    {
        if (!$this->possedes->contains($possede)) {
            $this->possedes->add($possede);
            $possede->setUtilisateur($this);
        }

        return $this;
    }

    public function removePossede(Possede $possede): static
    {
        if ($this->possedes->removeElement($possede)) {
            // set the owning side to null (unless already changed)
            if ($possede->getUtilisateur() === $this) {
                $possede->setUtilisateur(null);
            }
        }

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
            $appartient->setUtilisateur($this);
        }

        return $this;
    }

    public function removeAppartient(Appartient $appartient): static
    {
        if ($this->appartients->removeElement($appartient)) {
            // set the owning side to null (unless already changed)
            if ($appartient->getUtilisateur() === $this) {
                $appartient->setUtilisateur(null);
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
            $favori->setUtilisateur($this);
        }

        return $this;
    }

    public function removeFavori(Favoris $favori): static
    {
        if ($this->favoris->removeElement($favori)) {
            // set the owning side to null (unless already changed)
            if ($favori->getUtilisateur() === $this) {
                $favori->setUtilisateur(null);
            }
        }

        return $this;
    }
}
