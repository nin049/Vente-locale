<?php

namespace App\Twig;

use App\Entity\Utilisateur;
use App\Entity\Produit;
use App\Service\Api\FavorisApiService;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FavorisTwigExtension extends AbstractExtension
{
    public function __construct(
        private FavorisApiService $favorisApiService,
        private Security $security
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_favori', [$this, 'isFavori']),
            new TwigFunction('api_favoris_disponible', [$this, 'isApiDisponible']),
            new TwigFunction('nombre_favoris', [$this, 'getNombreFavoris']),
            new TwigFunction('is_mon_produit', [$this, 'isMonProduit']),
        ];
    }

    public function isFavori(int $produitId): bool
    {
        $user = $this->security->getUser();
        
        if (!$user instanceof Utilisateur) {
            return false;
        }

        try {
            return $this->favorisApiService->estFavori($user->getId(), $produitId);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isApiDisponible(): bool
    {
        try {
            return $this->favorisApiService->isApiDisponible();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getNombreFavoris(int $produitId): int
    {
        try {
            return $this->favorisApiService->getNombreFavorisParProduit($produitId);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function isMonProduit($produit): bool
    {
        $user = $this->security->getUser();
        
        if (!$user instanceof Utilisateur) {
            return false;
        }

        // Vérifier si l'utilisateur est propriétaire du produit via la relation Appartient
        foreach ($produit->getAppartients() as $appartient) {
            if ($appartient->getUtilisateur() === $user) {
                return true;
            }
        }

        return false;
    }
}
