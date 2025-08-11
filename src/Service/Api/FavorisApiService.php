<?php

namespace App\Service\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;

class FavorisApiService
{
    private const API_BASE_URL = 'http://localhost:5291/api/favoris';

    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    /**
     * Récupère tous les favoris d'un utilisateur
     *
     * @param int $utilisateurId
     * @return array
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    public function getFavorisByUtilisateur(int $utilisateurId): array
    {
        $response = $this->httpClient->request('GET', self::API_BASE_URL . "/user/{$utilisateurId}");
        
        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return $response->toArray();
    }

    /**
     * Ajoute un produit aux favoris d'un utilisateur
     *
     * @param int $utilisateurId
     * @param int $produitId
     * @return bool
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    public function ajouterFavori(int $utilisateurId, int $produitId): bool
    {
        try {
            $response = $this->httpClient->request('POST', self::API_BASE_URL, [
                'json' => [
                    'utilisateurId' => $utilisateurId,
                    'produitId' => $produitId
                ]
            ]);

            return $response->getStatusCode() === 201;
        } catch (ClientExceptionInterface $e) {
            // Gestion du conflit (409) si le favori existe déjà
            if ($e->getCode() === 409) {
                return false; // Le favori existe déjà
            }
            throw $e;
        }
    }

    /**
     * Supprime un produit des favoris d'un utilisateur
     *
     * @param int $utilisateurId
     * @param int $produitId
     * @return bool
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    public function supprimerFavori(int $utilisateurId, int $produitId): bool
    {
        $response = $this->httpClient->request(
            'DELETE', 
            self::API_BASE_URL . "/user/{$utilisateurId}/product/{$produitId}"
        );

        return $response->getStatusCode() === 204;
    }

    /**
     * Vérifie si un produit est dans les favoris d'un utilisateur
     *
     * @param int $utilisateurId
     * @param int $produitId
     * @return bool
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    public function estFavori(int $utilisateurId, int $produitId): bool
    {
        $response = $this->httpClient->request(
            'GET',
            self::API_BASE_URL . "/exists/user/{$utilisateurId}/product/{$produitId}"
        );

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $data = $response->toArray();
        return $data['exists'] ?? false;
    }

    /**
     * Récupère tous les favoris
     *
     * @return array
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    public function getTousFavoris(): array
    {
        $response = $this->httpClient->request('GET', self::API_BASE_URL);
        
        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return $response->toArray();
    }

    /**
     * Supprime un favori par son ID
     *
     * @param int $favorisId
     * @return bool
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    public function supprimerFavorisParId(int $favorisId): bool
    {
        $response = $this->httpClient->request('DELETE', self::API_BASE_URL . "/{$favorisId}");
        return $response->getStatusCode() === 204;
    }

    /**
     * Vérifie si l'API est disponible
     *
     * @return bool
     */
    public function isApiDisponible(): bool
    {
        try {
            $response = $this->httpClient->request('GET', self::API_BASE_URL, [
                'timeout' => 5
            ]);
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Récupère le nombre de favoris pour un produit spécifique
     *
     * @param int $produitId
     * @return int
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    public function getNombreFavorisParProduit(int $produitId): int
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                self::API_BASE_URL . "/count/product/{$produitId}"
            );

            if ($response->getStatusCode() !== 200) {
                return 0;
            }

            $data = $response->toArray();
            return $data['count'] ?? 0;
        } catch (\Exception $e) {
            return 0; // En cas d'erreur, retourner 0
        }
    }
}
