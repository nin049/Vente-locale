<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Utilisateur;
use App\Form\AdresseType;
use App\Form\EditUtilisateurForm;
use App\Repository\AppartientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class CompteController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_mon_compte')]
    public function monCompte(AppartientRepository $appartientRepository): Response
    {
        $user = $this->getUser();
        
        // Récupérer les annonces de l'utilisateur via la table Appartient
        $appartientsUser = $appartientRepository->findBy(
            ['utilisateur' => $user],
            ['dateAjout' => 'DESC'] // Trier par date d'ajout, les plus récentes en premier
        );
        
        return $this->render('compte/mon_compte.html.twig', [
            'user' => $user,
            'appartients' => $appartientsUser,
        ]);
    }

    #[Route('/mon-compte/modifier', name: 'app_compte_modifier')]
    public function modifier(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        $form = $this->createForm(EditUtilisateurForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    // Si un nouveau mot de passe est fourni
                    $plainPassword = $form->get('plainPassword')->getData();
                    if ($plainPassword) {
                        $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                    }

                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
                    return $this->redirectToRoute('app_mon_compte');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la sauvegarde : ' . $e->getMessage());
                }
            } else {
                // Afficher les erreurs détaillées
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('compte/modifier.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/mon-compte/supprimer', name: 'app_compte_supprimer', methods: ['POST'])]
    public function supprimer(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        // Vérification du token CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete-account', $request->request->get('_token'))) {
            // Supprimer l'utilisateur
            $entityManager->remove($user);
            $entityManager->flush();

            // Déconnecter l'utilisateur
            $security->logout(false);

            $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('error', 'Une erreur est survenue lors de la suppression de votre compte.');
        return $this->redirectToRoute('app_mon_compte');
    }

    #[Route('/mon-compte/adresse', name: 'app_compte_adresse')]
    public function adresse(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        // Récupérer l'adresse existante ou en créer une nouvelle
        $adresse = $user->getAdresse();
        if (!$adresse) {
            $adresse = new Adresse();
        }

        $form = $this->createForm(AdresseType::class, $adresse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Sauvegarder l'adresse
                $entityManager->persist($adresse);
                
                // Associer l'adresse à l'utilisateur si ce n'est pas déjà fait
                if (!$user->getAdresse()) {
                    $user->setAdresse($adresse);
                    $entityManager->persist($user);
                }
                
                $entityManager->flush();

                $this->addFlash('success', 'Votre adresse a été enregistrée avec succès.');
                return $this->redirectToRoute('app_mon_compte');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la sauvegarde de l\'adresse : ' . $e->getMessage());
            }
        }

        return $this->render('compte/adresse.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'adresse' => $adresse,
        ]);
    }

    #[Route('/mon-compte/adresse/supprimer', name: 'app_compte_adresse_supprimer', methods: ['POST'])]
    public function supprimerAdresse(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete-address', $request->request->get('_token'))) {
            $adresse = $user->getAdresse();
            
            if ($adresse) {
                // Dissocier l'adresse de l'utilisateur
                $user->setAdresse(null);
                $entityManager->persist($user);
                
                // Supprimer l'adresse
                $entityManager->remove($adresse);
                $entityManager->flush();

                $this->addFlash('success', 'Votre adresse a été supprimée avec succès.');
            }
        } else {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression de votre adresse.');
        }

        return $this->redirectToRoute('app_mon_compte');
    }

    #[Route('/parametres', name: 'app_parametres')]
    public function parametres(): Response
    {
        return $this->render('compte/parametres.html.twig');
    }
}
