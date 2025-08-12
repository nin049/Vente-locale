<?php

namespace App\Controller;

use App\Entity\Appartient;
use App\Entity\Etat;
use App\Entity\Produit;
use App\Entity\ProduitCategorie;
use App\Form\ProduitType;
use App\Service\ImageUploadService;
use App\Service\Api\FavorisApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AnnonceController extends AbstractController
{
    public function __construct(
        private FavorisApiService $favorisApiService
    ) {
    }
    #[Route('/annonces', name: 'app_annonces')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération des paramètres de filtrage
        $recherche = $request->query->get('recherche', '');
        $categorieId = $request->query->get('categorie', '');
        $etatId = $request->query->get('etat', '');
        $prixMin = $request->query->get('prix_min', '');
        $prixMax = $request->query->get('prix_max', '');
        $tri = $request->query->get('tri', 'recent');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 12; // Nombre d'annonces par page

        // Construction de la requête avec filtres
        $qb = $entityManager->getRepository(Produit::class)->createQueryBuilder('p')
            ->leftJoin('p.etat', 'e')
            ->leftJoin('p.produitCategories', 'pc')
            ->leftJoin('pc.categorie', 'c')
            ->leftJoin('p.appartients', 'a')
            ->leftJoin('a.utilisateur', 'u')
            ->addSelect('e', 'pc', 'c', 'a', 'u');

        // Filtre par recherche (nom ou description)
        if (!empty($recherche)) {
            $qb->andWhere('p.nom LIKE :recherche OR p.description LIKE :recherche')
               ->setParameter('recherche', '%' . $recherche . '%');
        }

        // Filtre par catégorie
        if (!empty($categorieId) && is_numeric($categorieId)) {
            $qb->andWhere('c.id = :categorieId')
               ->setParameter('categorieId', $categorieId);
        }

        // Filtre par état
        if (!empty($etatId) && is_numeric($etatId)) {
            $qb->andWhere('e.id = :etatId')
               ->setParameter('etatId', $etatId);
        }

        // Filtre par prix minimum
        if (!empty($prixMin) && is_numeric($prixMin)) {
            $qb->andWhere('p.prixInitial >= :prixMin')
               ->setParameter('prixMin', (float) $prixMin);
        }

        // Filtre par prix maximum
        if (!empty($prixMax) && is_numeric($prixMax)) {
            $qb->andWhere('p.prixInitial <= :prixMax')
               ->setParameter('prixMax', (float) $prixMax);
        }

        // Ordre selon le tri choisi
        switch ($tri) {
            case 'prix_asc':
                $qb->orderBy('p.prixInitial', 'ASC');
                break;
            case 'prix_desc':
                $qb->orderBy('p.prixInitial', 'DESC');
                break;
            case 'nom':
                $qb->orderBy('p.nom', 'ASC');
                break;
            case 'recent':
            default:
                $qb->orderBy('p.id', 'DESC');
                break;
        }

        // Compte total pour la pagination
        $totalQuery = clone $qb;
        $totalItems = count($totalQuery->select('p.id')->getQuery()->getResult());
        $totalPages = ceil($totalItems / $limit);

        // Application de la pagination
        $produits = $qb->setFirstResult(($page - 1) * $limit)
                       ->setMaxResults($limit)
                       ->getQuery()
                       ->getResult();

        // Récupération des données pour les filtres
        $categories = $entityManager->getRepository(\App\Entity\Categorie::class)->findAll();
        $etats = $entityManager->getRepository(\App\Entity\Etat::class)->findAll();

        // Calcul des statistiques de prix pour les suggestions
        $prixStats = $entityManager->getRepository(Produit::class)->createQueryBuilder('p')
            ->select('MIN(p.prixInitial) as prix_min, MAX(p.prixInitial) as prix_max, AVG(p.prixInitial) as prix_moyen')
            ->getQuery()
            ->getSingleResult();

        return $this->render('annonce/annonces.html.twig', [
            'produits' => $produits,
            'categories' => $categories,
            'etats' => $etats,
            'prix_stats' => $prixStats,
            'filtres' => [
                'recherche' => $recherche,
                'categorie' => $categorieId,
                'etat' => $etatId,
                'prix_min' => $prixMin,
                'prix_max' => $prixMax,
                'tri' => $tri,
            ],
            'pagination' => [
                'page_courante' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'limit' => $limit,
            ],
        ]);
    }

    #[Route('/annonces/creer', name: 'app_annonce_creer')]
    #[IsGranted('ROLE_USER')]
    public function creer(Request $request, EntityManagerInterface $entityManager, ImageUploadService $imageUploadService): Response
    {
        $produit = new Produit();
        
        // Définir l'état par défaut à ID 1 comme demandé
        $etat = $entityManager->getRepository(Etat::class)->find(1);
        if ($etat) {
            $produit->setEtat($etat);
        }
        
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                // Le formulaire a des erreurs de validation (ex: fichiers trop gros)
                // Récupérer les erreurs pour les afficher via flash messages
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                
                // Rediriger pour éviter l'erreur Turbo Drive
                return $this->redirectToRoute('app_annonce_creer');
            }
            
            // Le formulaire est valide, on peut continuer le traitement
            // Vérifier que l'utilisateur est connecté
            $user = $this->getUser();
            if (!$user) {
                $this->addFlash('error', 'Vous devez être connecté pour créer une annonce.');
                return $this->redirectToRoute('app_login');
            }

            // Récupérer la catégorie sélectionnée
            $categorie = $form->get('categories')->getData();

            // Gérer l'upload des images
            $imageFiles = $form->get('images')->getData();
            $imageNames = [];
            
            if ($imageFiles) {
                // Vérifier le nombre d'images
                if (count($imageFiles) > 3) {
                    $this->addFlash('error', 'Vous ne pouvez télécharger que 3 images maximum.');
                    return $this->redirectToRoute('app_annonce_creer');
                }
                
                // Valider et uploader les images
                $uploadErrors = [];
                foreach ($imageFiles as $imageFile) {
                    if ($imageFile) {
                        // Vérifier la taille du fichier
                        if ($imageFile->getSize() > 5 * 1024 * 1024) { // 5MB
                            $uploadErrors[] = 'Le fichier "' . $imageFile->getClientOriginalName() . '" est trop volumineux (max 5MB).';
                            continue;
                        }
                        
                        // Vérifier le type MIME
                        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) {
                            $uploadErrors[] = 'Le fichier "' . $imageFile->getClientOriginalName() . '" n\'est pas une image valide (JPEG, PNG, GIF, WEBP requis).';
                            continue;
                        }
                        
                        try {
                            $fileName = $imageUploadService->upload($imageFile);
                            $imageNames[] = $fileName;
                            
                            // Limiter à 3 images maximum
                            if (count($imageNames) >= 3) {
                                break;
                            }
                        } catch (\Exception $e) {
                            $uploadErrors[] = 'Erreur lors de l\'upload de "' . $imageFile->getClientOriginalName() . '" : ' . $e->getMessage();
                        }
                    }
                }
                
                // Si il y a eu des erreurs d'upload, on affiche les erreurs et on redirige
                if (!empty($uploadErrors)) {
                    foreach ($uploadErrors as $error) {
                        $this->addFlash('error', $error);
                    }
                    return $this->redirectToRoute('app_annonce_creer');
                }
            }

            // Assigner les images au produit
            $produit->setImages($imageNames);

            // Sauvegarder le produit
            $entityManager->persist($produit);
            $entityManager->flush();

            // Créer la relation Appartient (utilisateur propriétaire du produit)
            $appartient = new Appartient();
            $appartient->setUtilisateur($user);
            $appartient->setProduit($produit);
            $appartient->setDateAjout(new \DateTime());
            $appartient->setDateModification(new \DateTime());
            $entityManager->persist($appartient);
            
            // Créer la relation ProduitCategorie si une catégorie est sélectionnée
            if ($categorie) {
                $produitCategorie = new ProduitCategorie();
                $produitCategorie->setProduit($produit);
                $produitCategorie->setCategorie($categorie);
                $entityManager->persist($produitCategorie);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre annonce a été créée avec succès !');
            
            return $this->redirectToRoute('app_annonces');
        }

        return $this->render('annonce/creer.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/annonces/{id}', name: 'app_annonce_details')]
    public function details(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le produit avec toutes ses relations, y compris le propriétaire
        $produit = $entityManager->getRepository(Produit::class)->createQueryBuilder('p')
            ->leftJoin('p.etat', 'e')
            ->leftJoin('p.produitCategories', 'pc')
            ->leftJoin('pc.categorie', 'c')
            ->leftJoin('p.appartients', 'a')
            ->leftJoin('a.utilisateur', 'u')
            ->addSelect('e', 'pc', 'c', 'a', 'u')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$produit) {
            throw $this->createNotFoundException('Cette annonce n\'existe pas.');
        }

        // Récupérer le propriétaire (premier appartient trouvé)
        $proprietaire = null;
        if (!$produit->getAppartients()->isEmpty()) {
            $proprietaire = $produit->getAppartients()->first()->getUtilisateur();
        }

        return $this->render('annonce/details.html.twig', [
            'produit' => $produit,
            'proprietaire' => $proprietaire,
        ]);
    }

    #[Route('/annonces/{id}/modifier', name: 'app_annonce_modifier')]
    #[IsGranted('ROLE_USER')]
    public function modifier(int $id, Request $request, EntityManagerInterface $entityManager, ImageUploadService $imageUploadService): Response
    {
        // Récupérer le produit
        $produit = $entityManager->getRepository(Produit::class)->find($id);
        
        if (!$produit) {
            throw $this->createNotFoundException('Cette annonce n\'existe pas.');
        }

        // Vérifier que l'utilisateur est le propriétaire de l'annonce
        $user = $this->getUser();
        $appartient = $entityManager->getRepository(Appartient::class)->findOneBy([
            'produit' => $produit,
            'utilisateur' => $user
        ]);

        if (!$appartient) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cette annonce.');
            return $this->redirectToRoute('app_annonces');
        }

        // Récupérer la catégorie actuelle du produit
        $categorieActuelle = null;
        foreach ($produit->getProduitCategories() as $produitCategorie) {
            $categorieActuelle = $produitCategorie->getCategorie();
            break; // Prendre seulement la première catégorie
        }

        $form = $this->createForm(ProduitType::class, $produit);
        
        // Pré-remplir la catégorie dans le formulaire
        $form->get('categories')->setData($categorieActuelle);
        
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                // Le formulaire a des erreurs de validation (ex: fichiers trop gros)
                // Récupérer les erreurs pour les afficher via flash messages
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                
                // Rediriger pour éviter l'erreur Turbo Drive
                return $this->redirectToRoute('app_annonce_modifier', ['id' => $produit->getId()]);
            }
            
            // Le formulaire est valide, on peut continuer le traitement
            // Récupérer la nouvelle catégorie
            $nouvelleCategorie = $form->get('categories')->getData();

            // Gérer l'upload des nouvelles images si présentes
            $imageFiles = $form->get('images')->getData();
            
            if ($imageFiles && count($imageFiles) > 0) {
                // Vérifier le nombre d'images
                if (count($imageFiles) > 3) {
                    $this->addFlash('error', 'Vous ne pouvez télécharger que 3 images maximum.');
                    return $this->redirectToRoute('app_annonce_modifier', ['id' => $produit->getId()]);
                }
                
                // Valider les images avant de supprimer les anciennes
                $validImageFiles = [];
                foreach ($imageFiles as $imageFile) {
                    if ($imageFile) {
                        // Vérifier la taille du fichier
                        if ($imageFile->getSize() > 5 * 1024 * 1024) { // 5MB
                            $this->addFlash('error', 'Le fichier "' . $imageFile->getClientOriginalName() . '" est trop volumineux (max 5MB).');
                            return $this->redirectToRoute('app_annonce_modifier', ['id' => $produit->getId()]);
                        }
                        
                        // Vérifier le type MIME
                        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) {
                            $this->addFlash('error', 'Le fichier "' . $imageFile->getClientOriginalName() . '" n\'est pas une image valide (JPEG, PNG, GIF, WEBP requis).');
                            return $this->redirectToRoute('app_annonce_modifier', ['id' => $produit->getId()]);
                        }
                        
                        $validImageFiles[] = $imageFile;
                        
                        if (count($validImageFiles) >= 3) {
                            break;
                        }
                    }
                }
                
                // Si aucune image valide, ne pas continuer
                if (empty($validImageFiles)) {
                    $this->addFlash('error', 'Aucune image valide n\'a été trouvée.');
                    return $this->redirectToRoute('app_annonce_modifier', ['id' => $produit->getId()]);
                }
                
                // Maintenant on peut supprimer les anciennes images
                $anciensImages = $produit->getImages();
                if ($anciensImages) {
                    $imageUploadService->deleteImages($anciensImages);
                }
                
                // Upload des nouvelles images
                $imageNames = [];
                $uploadErrors = [];
                
                foreach ($validImageFiles as $imageFile) {
                    try {
                        $fileName = $imageUploadService->upload($imageFile);
                        $imageNames[] = $fileName;
                    } catch (\Exception $e) {
                        $uploadErrors[] = 'Erreur lors de l\'upload de "' . $imageFile->getClientOriginalName() . '" : ' . $e->getMessage();
                    }
                }
                
                // Si il y a eu des erreurs d'upload, on affiche les erreurs et on redirige
                if (!empty($uploadErrors)) {
                    foreach ($uploadErrors as $error) {
                        $this->addFlash('error', $error);
                    }
                    return $this->redirectToRoute('app_annonce_modifier', ['id' => $produit->getId()]);
                }
                
                // Mettre à jour les images du produit seulement si tout s'est bien passé
                if (!empty($imageNames)) {
                    $produit->setImages($imageNames);
                }
            }

            // Supprimer les anciennes relations ProduitCategorie
            foreach ($produit->getProduitCategories() as $ancienProduitCategorie) {
                $entityManager->remove($ancienProduitCategorie);
            }

            // Créer la nouvelle relation ProduitCategorie si une catégorie est sélectionnée
            if ($nouvelleCategorie) {
                $produitCategorie = new ProduitCategorie();
                $produitCategorie->setProduit($produit);
                $produitCategorie->setCategorie($nouvelleCategorie);
                $entityManager->persist($produitCategorie);
            }

            // Mettre à jour la date de modification dans Appartient
            $appartient->setDateModification(new \DateTime());
            $entityManager->persist($appartient);

            $entityManager->flush();

            $this->addFlash('success', 'Votre annonce a été modifiée avec succès !');
            
            return $this->redirectToRoute('app_annonce_details', ['id' => $produit->getId()]);
        }

        return $this->render('annonce/modifier.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }

    #[Route('/annonces/{id}/supprimer', name: 'app_annonce_supprimer', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function supprimer(int $id, Request $request, EntityManagerInterface $entityManager, ImageUploadService $imageUploadService): Response
    {
        // Récupérer le produit
        $produit = $entityManager->getRepository(Produit::class)->find($id);
        
        if (!$produit) {
            throw $this->createNotFoundException('Cette annonce n\'existe pas.');
        }

        // Vérifier que l'utilisateur est le propriétaire de l'annonce
        $user = $this->getUser();
        $appartient = $entityManager->getRepository(Appartient::class)->findOneBy([
            'produit' => $produit,
            'utilisateur' => $user
        ]);

        if (!$appartient) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette annonce.');
            return $this->redirectToRoute('app_annonces');
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('delete-annonce-' . $produit->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_annonce_details', ['id' => $id]);
        }

        // Supprimer les images du système de fichiers
        $images = $produit->getImages();
        if ($images) {
            $imageUploadService->deleteImages($images);
        }

        // Supprimer les relations ProduitCategorie
        foreach ($produit->getProduitCategories() as $produitCategorie) {
            $entityManager->remove($produitCategorie);
        }

        // Supprimer les relations Appartient
        foreach ($produit->getAppartients() as $appartientRelation) {
            $entityManager->remove($appartientRelation);
        }

        // Supprimer le produit
        $entityManager->remove($produit);
        $entityManager->flush();

        $this->addFlash('success', 'Votre annonce a été supprimée avec succès !');
        
        return $this->redirectToRoute('app_mon_compte');
    }

    #[Route('/api/favoris/count/{produitId}', name: 'api_favoris_count', methods: ['GET'])]
    public function getFavorisCount(int $produitId): JsonResponse
    {
        try {
            $count = $this->favorisApiService->getNombreFavorisParProduit($produitId);
            return new JsonResponse(['count' => $count]);
        } catch (\Exception $e) {
            return new JsonResponse(['count' => 0, 'error' => 'Erreur lors de la récupération du compteur'], 500);
        }
    }

    #[Route('/annonces/{id}/signaler', name: 'app_annonce_signaler')]
    #[IsGranted('ROLE_USER')]
    public function signaler(Produit $produit, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur ne signale pas sa propre annonce
        if ($produit->getProprietaire() === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas signaler votre propre annonce.');
            return $this->redirectToRoute('app_annonce_details', ['id' => $produit->getId()]);
        }

        // Vérifier si l'utilisateur a déjà signalé cette annonce
        $signalementExistant = $entityManager->getRepository(\App\Entity\Signale::class)
            ->findOneBy([
                'produit' => $produit,
                'signalePar' => $this->getUser()
            ]);

        if ($signalementExistant) {
            $this->addFlash('warning', 'Vous avez déjà signalé cette annonce.');
            return $this->redirectToRoute('app_annonce_details', ['id' => $produit->getId()]);
        }

        $signale = new \App\Entity\Signale();
        $signale->setProduit($produit);
        $signale->setSignalePar($this->getUser());

        $form = $this->createForm(\App\Form\SignalementType::class, $signale);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($signale);
            $entityManager->flush();

            $this->addFlash('success', 'Votre signalement a été envoyé. Il sera examiné par notre équipe.');
            return $this->redirectToRoute('app_annonce_details', ['id' => $produit->getId()]);
        }

        return $this->render('annonce/signaler.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }
}
