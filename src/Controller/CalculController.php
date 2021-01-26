<?php
// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalculController extends AbstractController
{
    /**
     * @Route("/addition/{a}/{b}")
     */
    public function addition($a,$b)
    {
        $addition = $a + $b;
        //return new Response("$a + $b = $addition");
        return $this->render('hello/addition.html.twig', [
            'a' => $a, 'b'=> $b, 'addition' => $addition
        ]);
    }

     /**
     * @Route("/auCarre/{a}")
     */
    // http://symfony.iut/auCarre
    public function auCarre($a)
    {
        $carre = $a*$a;
        //return new Response("Le carre de $a est $carre");
        return $this->render('hello/auCarre.html.twig', [
            'a' => $a, 'carre' => $carre
        ]);
    }
}