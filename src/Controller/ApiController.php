<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


#[Route('/api', name: 'api_')]
class ApiController extends AbstractController

{
    #[Route('/articles/liste', name: 'liste', methods: ['GET'])]
    public function liste(ArticleRepository $articleRepo): Response
    {
        // On récupère la liste des articles
        $articles = $articleRepo->apiFindAll();

        // On spécifie qu'on utilise un encodeur en json
        $encoders = [ new JsonEncoder()];

        // On instancie le "normaliseur" pour convertir la collection en tableau
        $normalizers = [ new ObjectNormalizer()];

        // On fait la conversion en json
        // On instancie le convertisseur
        $serializer = new Serializer($normalizers, $encoders);

        // On convertit en json
        $jsonContent = $serializer->serialize($articles, 'json', [
            'circular_reference_handler' => function($object){
            return $object->getId();
            }
        ]);

        // On instancie la réponse
        $response = new Response($jsonContent);

        // On ajoute l'entete HTTP
        $response->headers->set('Content-type', 'application/json');

        // On envoie la réponse
        return $response;

    }

    #[Route('/article/lire/{id}', name: 'lire', methods: ['GET'])]
    public function getArticle(Article $article): Response
    {
        // On spécifie qu'on utilise un encodeur en json
        $encoders = [ new JsonEncoder()];

        // On instancie le "normaliseur" pour convertir la collection en tableau
        $normalizers = [ new ObjectNormalizer()];

        // On fait la conversion en json
        // On instancie le convertisseur
        $serializer = new Serializer($normalizers, $encoders);

        // On convertit en json
        $jsonContent = $serializer->serialize($article, 'json', [
            'circular_reference_handler' => function($object){
                return $object->getId();
            }
        ]);

        // On instancie la réponse
        $response = new Response($jsonContent);

        // On ajoute l'entete HTTP
        $response->headers->set('Content-type', 'application/json');

        // On envoie la réponse
        return $response;

    }

    #[Route('/article/ajout', name: 'ajout', methods: ['POST'])]
    public function addArticle(Request $request,EntityManagerInterface $em)
    {
        // On vérifie si on a une requete XMLHttpRequest
        //if($request->isXmlHttpRequest()){
            // On vérifie les données après les avoir décodées
            $données = json_decode($request->getContent());

            // On instancie un nouvel article
            $article = new Article();

            // On hydrate notre article
            $article->setTitle($données->title);
            $article->setContent($données->content);

            // On sauvegarde en base de données
            $em->persist($article);
            $em->flush();

            // On retourne la confirmation
            return new Response('OK', 201);

        //}

       // return new Response('Erreur', 404);
    }

    #[Route('/article/editer/{id}', name: 'editer', methods: ['PUT'])]
    public function editArticle(?Article $article,Request $request,EntityManagerInterface $em)
    {
        // On vérifie si on a une requete XMLHttpRequest
        //if($request->isXmlHttpRequest()){
        // On vérifie les données après les avoir décodées
        $données = json_decode($request->getContent());

        $code = 200;

        // Si on n'a pas d'article
        if(!$article){
            // On instancie un nouvel article
            $article = new Article();

            // On met le code 201
            $code = 201;
        }
        // On hydrate notre article
        $article->setTitle($données->title);
        $article->setContent($données->content);

        // On sauvegarde en base de données
        $em->persist($article);
        $em->flush();

        // On retourne la confirmation
        return new Response('OK', $code);

        //}
        // return new Response('Erreur', 404);
    }

    #[Route('/article/supprimer/{id}', name: 'supprimer', methods: ['DELETE'])]
    public function removeArticle(?Article $article,Request $request,EntityManagerInterface $em)
    {
        $em->remove($article);
        $em->flush();

        return new Response('OK');
    }



}
