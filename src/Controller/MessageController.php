<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/messages')]
#[IsGranted('ROLE_USER')]
final class MessageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConversationRepository $conversationRepository,
        private MessageRepository $messageRepository,
        private ProduitRepository $produitRepository
    ) {
    }

    #[Route('/', name: 'app_messages_index')]
    public function index(): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        // Récupérer toutes les conversations de l'utilisateur
        $conversations = $this->conversationRepository->findConversationsByUtilisateur($user);
        
        // Compter les messages non lus
        $messagesNonLus = $this->conversationRepository->countMessagesNonLusByUtilisateur($user);

        return $this->render('message/index.html.twig', [
            'conversations' => $conversations,
            'messagesNonLus' => $messagesNonLus,
        ]);
    }

    #[Route('/conversation/{id}', name: 'app_messages_conversation', requirements: ['id' => '\d+'])]
    public function conversation(int $id): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        // Vérifier que l'utilisateur participe à cette conversation
        $conversation = $this->conversationRepository->findConversationAvecVerificationParticipation($id, $user);
        
        if (!$conversation) {
            $this->addFlash('error', 'Conversation introuvable ou accès non autorisé.');
            return $this->redirectToRoute('app_messages_index');
        }

        // Marquer les messages comme lus
        $this->messageRepository->marquerMessagesCommeLus($conversation, $user);
        $this->entityManager->flush();

        // Récupérer les messages de la conversation
        $messages = $this->messageRepository->findMessagesByConversation($conversation);

        return $this->render('message/conversation.html.twig', [
            'conversation' => $conversation,
            'messages' => $messages,
            'autreParticipant' => $conversation->getAutreParticipant($user),
        ]);
    }

    #[Route('/contacter/{produitId}', name: 'app_messages_contacter', requirements: ['produitId' => '\d+'])]
    public function contacter(int $produitId): Response
    {
        /** @var Utilisateur $acheteur */
        $acheteur = $this->getUser();
        
        $produit = $this->produitRepository->find($produitId);
        if (!$produit) {
            $this->addFlash('error', 'Produit introuvable.');
            return $this->redirectToRoute('app_annonces');
        }

        // Trouver le vendeur (propriétaire du produit)
        $vendeur = null;
        foreach ($produit->getAppartients() as $appartient) {
            $vendeur = $appartient->getUtilisateur();
            break;
        }

        if (!$vendeur) {
            $this->addFlash('error', 'Vendeur introuvable pour ce produit.');
            return $this->redirectToRoute('app_annonce_details', ['id' => $produitId]);
        }

        // Vérifier que l'acheteur n'est pas le vendeur
        if ($acheteur === $vendeur) {
            $this->addFlash('error', 'Vous ne pouvez pas vous contacter vous-même.');
            return $this->redirectToRoute('app_annonce_details', ['id' => $produitId]);
        }

        // Vérifier s'il existe déjà une conversation
        $conversationExistante = $this->conversationRepository->findConversationExistante($acheteur, $vendeur, $produit);
        
        if ($conversationExistante) {
            return $this->redirectToRoute('app_messages_conversation', ['id' => $conversationExistante->getId()]);
        }

        // Créer une nouvelle conversation
        $conversation = new Conversation();
        $conversation->setAcheteur($acheteur);
        $conversation->setVendeur($vendeur);
        $conversation->setProduit($produit);

        $this->entityManager->persist($conversation);

        // Créer un message initial automatique
        $messageInitial = new Message();
        $messageInitial->setConversation($conversation);
        $messageInitial->setAuteur($acheteur);
        $messageInitial->setContenu("Bonjour, je suis intéressé(e) par votre annonce : " . $produit->getNom());
        $messageInitial->setType('system');

        $this->entityManager->persist($messageInitial);
        $this->entityManager->flush();

        $this->addFlash('success', 'Conversation créée avec succès !');
        return $this->redirectToRoute('app_messages_conversation', ['id' => $conversation->getId()]);
    }

    #[Route('/envoyer', name: 'app_messages_envoyer', methods: ['POST'])]
    public function envoyerMessage(Request $request): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        $data = json_decode($request->getContent(), true);
        $conversationId = $data['conversationId'] ?? null;
        $contenu = trim($data['contenu'] ?? '');

        if (!$conversationId || !$contenu) {
            return $this->json(['success' => false, 'message' => 'Données manquantes'], 400);
        }

        // Vérifier la conversation
        $conversation = $this->conversationRepository->findConversationAvecVerificationParticipation($conversationId, $user);
        if (!$conversation) {
            return $this->json(['success' => false, 'message' => 'Conversation introuvable'], 404);
        }

        // Créer le message
        $message = new Message();
        $message->setConversation($conversation);
        $message->setAuteur($user);
        $message->setContenu($contenu);

        $this->entityManager->persist($message);
        
        // Mettre à jour la date de dernière activité de la conversation
        $conversation->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($conversation);
        
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => [
                'id' => $message->getId(),
                'contenu' => $message->getContenu(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                'auteur' => [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom()
                ]
            ]
        ]);
    }

    #[Route('/marquer-lu/{conversationId}', name: 'app_messages_marquer_lu', methods: ['POST'], requirements: ['conversationId' => '\d+'])]
    public function marquerLu(int $conversationId): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        $conversation = $this->conversationRepository->findConversationAvecVerificationParticipation($conversationId, $user);
        if (!$conversation) {
            return $this->json(['success' => false, 'message' => 'Conversation introuvable'], 404);
        }

        $messagesMarques = $this->messageRepository->marquerMessagesCommeLus($conversation, $user);
        $this->entityManager->flush();

        return $this->json(['success' => true, 'messagesMarques' => $messagesMarques]);
    }

    #[Route('/derniers-messages/{conversationId}', name: 'app_messages_derniers', methods: ['GET'], requirements: ['conversationId' => '\d+'])]
    public function derniersMessages(int $conversationId, Request $request): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        $conversation = $this->conversationRepository->findConversationAvecVerificationParticipation($conversationId, $user);
        if (!$conversation) {
            return $this->json(['success' => false, 'message' => 'Conversation introuvable'], 404);
        }

        $dernierMessageId = $request->query->get('dernierMessageId', 0);
        
        // Récupérer les messages plus récents
        $messages = $this->entityManager->createQueryBuilder()
            ->select('m', 'a')
            ->from(Message::class, 'm')
            ->leftJoin('m.auteur', 'a')
            ->where('m.conversation = :conversation')
            ->andWhere('m.id > :dernierMessageId')
            ->setParameter('conversation', $conversation)
            ->setParameter('dernierMessageId', $dernierMessageId)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        $messagesData = [];
        foreach ($messages as $message) {
            $messagesData[] = [
                'id' => $message->getId(),
                'contenu' => $message->getContenu(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                'auteur' => [
                    'id' => $message->getAuteur()->getId(),
                    'nom' => $message->getAuteur()->getNom(),
                    'prenom' => $message->getAuteur()->getPrenom()
                ],
                'lu' => $message->isLu()
            ];
        }

        return $this->json(['success' => true, 'messages' => $messagesData]);
    }
}
