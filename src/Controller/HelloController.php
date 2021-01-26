<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    /**
     * @Route("/hello/{name}", name="hello")
     */
    public function hello($name = "world")
    {
        return new Response("<h1>Hello $name</h1>");
    }

     /**
     * @Route("/name/{name}")
     */
    public function name($name)
    {
        return $this->render('hello/name.html.twig', [
            'name' => $name
        ]);
    }

}

?>