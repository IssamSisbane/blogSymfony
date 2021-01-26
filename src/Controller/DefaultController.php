<?php
// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/hello/{name}")
     */
    public function index($name)
    {
        //return new Response("Hello $name !!!");
        return $this->render('hello/name.html.twig', [
            'name' => $name
        ]);

    }

     /**
     * @Route("/simplicity")
     */
    // http://symfony.iut/simplicity
    public function simple()
    {
        return new Response('Simple! Easy! Great!');
    }
}