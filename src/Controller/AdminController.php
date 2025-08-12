<?php

namespace App\Controller;

use App\Entity\Signale;
use App\Repository\SignaleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(SignaleRepository $signaleRepository): Response
    {
        // Statistiques pour le tableau de bord
        $stats = [
            'signalements_en_attente' => $signaleRepository->count(['statut' => 'en_attente']),
            'signalements_traites' => $signaleRepository->count(['statut' => 'traite']),
            'signalements_rejetes' => $signaleRepository->count(['statut' => 'rejete']),
            'total_signalements' => $signaleRepository->count([])
        ];

        // Derniers signalements en attente
        $derniersSignalements = $signaleRepository->findBy(
            ['statut' => 'en_attente'],
            ['dateSignalement' => 'DESC'],
            5
        );

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'derniers_signalements' => $derniersSignalements
        ]);
    }

    #[Route('/signalements', name: 'admin_signalements')]
    public function listeSignalements(Request $request, SignaleRepository $signaleRepository): Response
    {
        $statut = $request->query->get('statut');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 20;

        $criteria = [];
        if ($statut && in_array($statut, ['en_attente', 'traite', 'rejete'])) {
            $criteria['statut'] = $statut;
        }

        $signalements = $signaleRepository->findSignalementsAvecDetails($criteria, $page, $limit);
        $totalSignalements = $signaleRepository->count($criteria);
        $totalPages = ceil($totalSignalements / $limit);

        return $this->render('admin/signalements/liste.html.twig', [
            'signalements' => $signalements,
            'statut_filtre' => $statut,
            'page_courante' => $page,
            'total_pages' => $totalPages,
            'total_signalements' => $totalSignalements
        ]);
    }

    #[Route('/signalements/{id}', name: 'admin_signalement_detail', requirements: ['id' => '\d+'])]
    public function detailSignalement(Signale $signale): Response
    {
        return $this->render('admin/signalements/detail.html.twig', [
            'signale' => $signale
        ]);
    }

    #[Route('/signalements/{id}/traiter', name: 'admin_signalement_traiter', methods: ['POST'])]
    public function traiterSignalement(
        Signale $signale,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $action = $request->request->get('action');
        $reponse = $request->request->get('reponse');

        if (!in_array($action, ['traiter', 'rejeter'])) {
            $this->addFlash('error', 'Action non valide.');
            return $this->redirectToRoute('admin_signalement_detail', ['id' => $signale->getId()]);
        }

        if ($action === 'traiter') {
            $signale->marquerCommeTraite($this->getUser(), $reponse);
            $this->addFlash('success', 'Signalement marqué comme traité.');
        } else {
            $signale->marquerCommeRejete($this->getUser(), $reponse);
            $this->addFlash('success', 'Signalement rejeté.');
        }

        $entityManager->flush();

        return $this->redirectToRoute('admin_signalements');
    }

    #[Route('/signalements/{id}/supprimer-produit', name: 'admin_signalement_supprimer_produit', methods: ['POST'])]
    public function supprimerProduitSignale(
        Signale $signale,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $reponse = $request->request->get('reponse', 'Produit supprimé suite au signalement.');
        
        // Marquer le signalement comme traité
        $signale->marquerCommeTraite($this->getUser(), $reponse);
        
        // Supprimer le produit (ou le marquer comme inactif)
        $produit = $signale->getProduit();
        if ($produit) {
            // Pour l'instant, on supprime complètement le produit
            // Dans un vrai projet, il serait mieux d'ajouter un champ 'supprime' ou 'actif'
            $entityManager->remove($produit);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Produit supprimé et signalement traité.');

        return $this->redirectToRoute('admin_signalements');
    }

    #[Route('/signalements/actions-groupees', name: 'admin_signalements_actions_groupees', methods: ['POST'])]
    public function actionsGroupees(
        Request $request,
        SignaleRepository $signaleRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $action = $request->request->get('action');
        $signalementIds = $request->request->all('signalements');
        
        if (empty($signalementIds)) {
            $this->addFlash('error', 'Aucun signalement sélectionné.');
            return $this->redirectToRoute('admin_signalements');
        }

        $signalements = $signaleRepository->findBy(['id' => $signalementIds]);
        $count = 0;

        foreach ($signalements as $signale) {
            if ($signale->isEnAttente()) {
                if ($action === 'traiter') {
                    $signale->marquerCommeTraite($this->getUser(), 'Traitement en lot');
                    $count++;
                } elseif ($action === 'rejeter') {
                    $signale->marquerCommeRejete($this->getUser(), 'Rejet en lot');
                    $count++;
                }
            }
        }

        if ($count > 0) {
            $entityManager->flush();
            $this->addFlash('success', "{$count} signalement(s) traité(s).");
        } else {
            $this->addFlash('warning', 'Aucun signalement n\'a pu être traité.');
        }

        return $this->redirectToRoute('admin_signalements');
    }
}
