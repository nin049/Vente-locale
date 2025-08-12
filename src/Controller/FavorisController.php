<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\ProduitRepository;
use App\Service\Api\FavorisApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/favoris')]
class FavorisController extends AbstractController
{
    public function __construct(
        private FavorisApiService $favorisApiService,
        private ProduitRepository $produitRepository
    ) {
    }

    #[Route('/', name: 'app_favoris_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        $favoris = [];
        $produitsAvecFavoris = [];
        $apiDisponible = false;

        try {
            $apiDisponible = $this->favorisApiService->isApiDisponible();
            if ($apiDisponible && $user) {
                $favoris = $this->favorisApiService->getFavorisByUtilisateur($user->getId());
                
                // Récupérer les informations complètes des produits pour chaque favori
                foreach ($favoris as $favori) {
                    $produit = $this->produitRepository->find($favori['produitId']);
                    if ($produit) {
                        $produitsAvecFavoris[] = [
                            'favori' => $favori,
                            'produit' => $produit
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la récupération des favoris via l\'API .NET');
        }

        return $this->render('favoris/index.html.twig', [
            'favoris' => $favoris,
            'produits_favoris' => $produitsAvecFavoris,
            'api_disponible' => $apiDisponible,
        ]);
    }

    #[Route('/ajouter/{produitId}', name: 'app_favoris_ajouter', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function ajouter(int $produitId): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Utilisateur non connecté'], 401);
        }

        try {
            $success = $this->favorisApiService->ajouterFavori($user->getId(), $produitId);
            
            if ($success) {
                return $this->json([
                    'success' => true,
                    'message' => 'Produit ajouté aux favoris'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Le produit est déjà dans vos favoris'
                ], 409);
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout aux favoris : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/supprimer/{produitId}', name: 'app_favoris_supprimer', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function supprimer(int $produitId): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Utilisateur non connecté'], 401);
        }

        try {
            $success = $this->favorisApiService->supprimerFavori($user->getId(), $produitId);
            
            if ($success) {
                return $this->json([
                    'success' => true,
                    'message' => 'Produit retiré des favoris'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Favori non trouvé'
                ], 404);
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/toggle/{produitId}', name: 'app_favoris_toggle', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggle(int $produitId): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Utilisateur non connecté'], 401);
        }

        try {
            $estFavori = $this->favorisApiService->estFavori($user->getId(), $produitId);
            
            if ($estFavori) {
                $success = $this->favorisApiService->supprimerFavori($user->getId(), $produitId);
                $action = 'supprimé';
            } else {
                $success = $this->favorisApiService->ajouterFavori($user->getId(), $produitId);
                $action = 'ajouté';
            }

            return $this->json([
                'success' => $success,
                'action' => $action,
                'est_favori' => !$estFavori,
                'message' => $success ? "Produit {$action} des favoris" : "Erreur lors de l'opération"
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/check/{produitId}', name: 'app_favoris_check', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function check(int $produitId): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['est_favori' => false]);
        }

        try {
            $estFavori = $this->favorisApiService->estFavori($user->getId(), $produitId);
            
            return $this->json([
                'est_favori' => $estFavori,
                'utilisateur_id' => $user->getId(),
                'produit_id' => $produitId
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'est_favori' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
