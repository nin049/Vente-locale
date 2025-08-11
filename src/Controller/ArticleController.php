<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpClient\HttpClient;

final class ArticleController extends AbstractController
{
    // Adresse de l'API Java
    private string $apiUrl = 'http://localhost:8080/demo-api/api/articles';
 
    // Le client HTTP Symfony
    private $http;
 
    public function __construct()
    {
        // Création du client HTTP Symfony
        $this->http = HttpClient::create();
    }
    // Affiche la liste des articles
    #[Route('/articles/view', name: 'article_view')]
    public function view(): Response
    {
        // Appel de l'API Java pour récupérer les articles
        $response = $this->http->request('GET', $this->apiUrl);
        $articles = $response->toArray();
 
        // Info : normalement il faut vérifier si la réponse contient des articles
        return $this->render('article/list.html.twig', [
            'articles' => $articles
        ]);
    }

    // Affiche tous les articles
    #[Route('/articles', name: 'article_list')]
    public function index(): Response
    {
        // Appel de l'API Java pour récupérer les articles
        $response = $this->http->request('GET', $this->apiUrl);
        $articles = $response->toArray();

        return $this->render('article/list.html.twig', [
            'articles' => $articles
        ]);
    }

    // Crée un nouvel article
    #[Route('/articles/new', name: 'article_new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $articleData = [
                'title' => $request->request->get('title'),
                'content' => $request->request->get('content')
            ];
            
            // Appel de l'API Java pour créer l'article
            $response = $this->http->request('POST', $this->apiUrl, [
                'json' => $articleData
            ]);
            
            if ($response->getStatusCode() === 201) {
                $this->addFlash('success', 'Article créé avec succès!');
                return $this->redirectToRoute('article_list');
            }
        }
        return $this->render('article/new.html.twig');
    }

    // Édite un article existant
    #[Route('/articles/{id}/edit', name: 'article_edit')]
    public function edit(int $id, Request $request): Response
    {
        // Récupérer l'article à éditer
        $response = $this->http->request('GET', $this->apiUrl . '/' . $id);
        
        if ($response->getStatusCode() !== 200) {
            throw $this->createNotFoundException('Article non trouvé');
        }
        
        $article = $response->toArray();
        
        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $articleData = [
                'title' => $request->request->get('title'),
                'content' => $request->request->get('content')
            ];
            
            // Appel de l'API Java pour mettre à jour l'article
            $updateResponse = $this->http->request('PUT', $this->apiUrl . '/' . $id, [
                'json' => $articleData
            ]);
            
            if ($updateResponse->getStatusCode() === 200) {
                $this->addFlash('success', 'Article mis à jour avec succès!');
                return $this->redirectToRoute('article_list');
            }
        }
        
        return $this->render('article/edit.html.twig', [
            'article' => $article
        ]);
    }

    // Supprime un article
    #[Route('/articles/{id}/delete', name: 'article_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        // Appel de l'API Java pour supprimer l'article
        $response = $this->http->request('DELETE', $this->apiUrl . '/' . $id);
        
        if ($response->getStatusCode() === 200 || $response->getStatusCode() === 204) {
            $this->addFlash('success', 'Article supprimé avec succès!');
        } else {
            $this->addFlash('error', 'Erreur lors de la suppression de l\'article.');
        }
        
        return $this->redirectToRoute('article_list');
    }
}


