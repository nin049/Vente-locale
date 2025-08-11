<?php

namespace App\Controller;

use App\Service\Api\FavorisApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/test-favoris')]
class TestFavorisApiController extends AbstractController
{
    public function __construct(
        private FavorisApiService $favorisApiService
    ) {
    }

    #[Route('/status', name: 'test_favoris_api_status', methods: ['GET'])]
    public function checkApiStatus(): JsonResponse
    {
        $isAvailable = $this->favorisApiService->isApiDisponible();
        
        return $this->json([
            'api_disponible' => $isAvailable,
            'message' => $isAvailable ? 'API Favoris disponible' : 'API Favoris indisponible'
        ]);
    }

    #[Route('/user/{userId}/favoris', name: 'test_get_favoris_user', methods: ['GET'])]
    public function getFavorisUtilisateur(int $userId): JsonResponse
    {
        try {
            $favoris = $this->favorisApiService->getFavorisByUtilisateur($userId);
            
            return $this->json([
                'success' => true,
                'utilisateur_id' => $userId,
                'favoris' => $favoris,
                'total' => count($favoris)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/add', name: 'test_add_favori', methods: ['POST'])]
    public function ajouterFavori(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['utilisateur_id']) || !isset($data['produit_id'])) {
            return $this->json([
                'success' => false,
                'error' => 'utilisateur_id et produit_id requis'
            ], 400);
        }

        try {
            $success = $this->favorisApiService->ajouterFavori(
                $data['utilisateur_id'],
                $data['produit_id']
            );
            
            return $this->json([
                'success' => $success,
                'message' => $success ? 'Favori ajouté avec succès' : 'Le favori existe déjà ou erreur lors de l\'ajout'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/remove', name: 'test_remove_favori', methods: ['DELETE'])]
    public function supprimerFavori(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['utilisateur_id']) || !isset($data['produit_id'])) {
            return $this->json([
                'success' => false,
                'error' => 'utilisateur_id et produit_id requis'
            ], 400);
        }

        try {
            $success = $this->favorisApiService->supprimerFavori(
                $data['utilisateur_id'],
                $data['produit_id']
            );
            
            return $this->json([
                'success' => $success,
                'message' => $success ? 'Favori supprimé avec succès' : 'Favori non trouvé ou erreur lors de la suppression'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/check/{userId}/{produitId}', name: 'test_check_favori', methods: ['GET'])]
    public function verifierFavori(int $userId, int $produitId): JsonResponse
    {
        try {
            $estFavori = $this->favorisApiService->estFavori($userId, $produitId);
            
            return $this->json([
                'success' => true,
                'utilisateur_id' => $userId,
                'produit_id' => $produitId,
                'est_favori' => $estFavori
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/all', name: 'test_get_all_favoris', methods: ['GET'])]
    public function getTousFavoris(): JsonResponse
    {
        try {
            $favoris = $this->favorisApiService->getTousFavoris();
            
            return $this->json([
                'success' => true,
                'favoris' => $favoris,
                'total' => count($favoris)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
