<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Article;
class ArticlesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $article = new Article();
            $article->setTitle('Article ' . $i);
            $article->setContent('<p>Content of article ' . $i . '</p>');
            $article->setImage('https://picsum.photos/200/300?random=' . $i);
            $article->setCreatedAt(new \DateTime());
            $manager->persist($article);
        }
        $manager->flush();
    }
}
