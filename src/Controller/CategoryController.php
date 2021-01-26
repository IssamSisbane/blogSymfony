<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Category;
use App\Form\CategoryType;

class CategoryController extends AbstractController
{
    /**
     * @Route("/monBlog/admin/category", name="category")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $donnees = $this->getDoctrine()->getRepository(Category::class)->findAll();

        
        $categories = $paginator->paginate(
            $donnees,
            $request->query->getInt('page',1),
            50
        );

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    

    /**
     * @Route("/monBlog/admin/category/show/{id}", name="category.show")
     */
    public function show($id): Response
    {
        // On récupère le `repository` en rapport avec l'entity `Category` 
        $categoryRepository = $this->getDoctrine()->getRepository(Category::class);

        // On fait appel à la méthode générique `find` qui permet de SELECT en fonction d'un Id
        $category = $categoryRepository->find($id);

        if(!$category) {
            throw $this->createNotFoundException(
                "Pas de Category trouvé avec l'id ".$id
            );
        }

        return $this->render('admin/category/show.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * @Route("/monBlog/admin/category/create", name="category.create")
     */
    public function create(Request $request): Response
    {
        $category = new category();

        $form = $this->createForm(CategoryType::class, $category);

        // Récupère les données transmises par la requête pour les transmettre au formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $category = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('category', []);

        }

        return $this->render('admin/category/update.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/monBlog/admin/category/edit/{id}", name="category.edit")
     */
    public function update($id, Request $request): Response
    {
        // On récupère le `repository` en rapport avec l'entity `Category` 
        $categoryRepository = $this->getDoctrine()->getRepository(Category::class);

        $category = $categoryRepository->find($id);

        $form = $this->createForm(CategoryType::class, $category);

        // Récupère les données transmises par la requête pour les transmettre au formulaire
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()){
            $category = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('category');

        }

        return $this->render('admin/category/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/monBlog/category/admin/remove/{id}", name="category.remove")
     */
    public function remove($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);

        $category = $categoryRepository->find($id);

        if(!$category) {
            throw $this->createNotFoundException(
                "Pas de Category trouvé avec l'id ".$id
            );
        }
        // On dit au manager que l'on veux supprimer cet objet en base de données
        $entityManager->remove($category);
        // On met à jour en base de données en supprimant la ligne correspondante (i.e. la requête DELETE)
        $entityManager->flush();

        return $this->redirectToRoute('category');
    }
}
