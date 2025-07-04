<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Article;
final class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_blog')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Article::class);
        $articles = $repository->findAll();

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles,
        ]);
    }

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('blog/home.html.twig');
    }
}
