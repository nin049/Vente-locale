<?php

namespace App\Entity;

use App\Repository\SignalementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SignalementRepository::class)]
class Signalement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $natureSignalement = null;

    /**
     * @var Collection<int, Signale>
     */
    #[ORM\OneToMany(targetEntity: Signale::class, mappedBy: 'signalement')]
    private Collection $signales;

    public function __construct()
    {
        $this->signales = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNatureSignalement(): ?string
    {
        return $this->natureSignalement;
    }

    public function setNatureSignalement(string $natureSignalement): static
    {
        $this->natureSignalement = $natureSignalement;

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
            $signale->setSignalement($this);
        }

        return $this;
    }

    public function removeSignale(Signale $signale): static
    {
        if ($this->signales->removeElement($signale)) {
            // set the owning side to null (unless already changed)
            if ($signale->getSignalement() === $this) {
                $signale->setSignalement(null);
            }
        }

        return $this;
    }
}
