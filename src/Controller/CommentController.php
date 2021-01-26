<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;

class CommentController extends AbstractController
{
    /**
     * @Route("/monBlog/admin/comment", name="comment")
     */
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    /**
     * @Route("/monBlog/admin/allcomments", name="allcomments")
     */
    public function list(Request $request, PaginatorInterface $paginator): Response
    {
        $donnees = $this->getDoctrine()->getRepository(Comment::class)->findAll();
        
        $comments = $paginator->paginate(
            $donnees,
            $request->query->getInt('page',1),
            10
        );
        

        return $this->render('admin/comment/list.html.twig', [
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/monBlog/admin/comment/show/{id}", name="comment.show")
     */
    public function show($id): Response
    {
        // On récupère le `repository` en rapport avec l'entity `Comment` 
        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);

        // On fait appel à la méthode générique `find` qui permet de SELECT en fonction d'un Id
        $comment = $commentRepository->find($id);

        if(!$comment) {
            throw $this->createNotFoundException(
                "Pas de Comment trouvé avec l'id ".$id
            );
        }

        return $this->render('admin/comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    /**
     * @Route("/monBlog/admin/comment/create", name="comment.create")
     */
    public function create(Request $request): Response
    {
        $comment = new comment();

        $form = $this->createForm(CommentType::class, $comment);

        // Récupère les données transmises par la requête pour les transmettre au formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $comment = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('message', 'Le commentaire a bien été crée');

            return $this->redirectToRoute('comment', []);

        }

        return $this->render('comment/update.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/monBlog/admin/comment/edit/{id}", name="comment.edit")
     */
    public function update($id, Request $request): Response
    {
        // On récupère le `repository` en rapport avec l'entity `Comment` 
        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);

        $comment = $commentRepository->find($id);

        $form = $this->createForm(CommentType::class, $comment);

        // Récupère les données transmises par la requête pour les transmettre au formulaire
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()){
            $comment = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('message', 'Le commentaire a bien été modifé');

            return $this->redirectToRoute('comment.show', ['id' => $comment->getId()]);

        }

        return $this->render('admin/comment/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/monBlog/admin/comment/remove/{id}", name="comment.remove")
     */
    public function remove($id, Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $commentRepository = $entityManager->getRepository(Comment::class);

        $comment = $commentRepository->find($id);

        if(!$comment) {
            throw $this->createNotFoundException(
                "Pas de Comment trouvé avec l'id ".$id
            );
        }
        // On dit au manager que l'on veux supprimer cet objet en base de données
        $entityManager->remove($comment);
        // On met à jour en base de données en supprimant la ligne correspondante (i.e. la requête DELETE)
        $entityManager->flush();

        $this->addFlash('message', 'Le commentaire a bien été supprimé');

        return $this->redirectToRoute('allcomments');
    }

    /**
     * @Route("/monBlog/admin/allInvalidsComments", name="comment.invalides")
     */
    public function listInvalides(Request $request, PaginatorInterface $paginator): Response
    {
        $donnees = $this->getDoctrine()->getRepository(Comment::class)->findBy(['valid' => '']);
        
        $comments = $paginator->paginate(
            $donnees,
            $request->query->getInt('page',1),
            10
        );
        

        return $this->render('admin/comment/list.html.twig', [
            'comments' => $comments,
            'invalide' => ''
        ]);
    }

    
    /**
     * @Route("/monBlog/admin/comment/valider/{id}", name="comment.valider")
     */
    public function valider($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $commentRepository = $entityManager->getRepository(Comment::class);

        $comment = $commentRepository->find($id);

        if(!$comment) {
            throw $this->createNotFoundException(
                "Pas de Comment trouvé avec l'id ".$id
            );
        }

        $comment->setValid(true);

        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash('message', 'Le commentaire a bien été validé');

        return $this->redirectToRoute('comment.invalides');

    }

    public function recentComment(int $max = 5)
    {
        $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(['valid' => 1],['createdAt' => 'desc'],6);

        return $this->render('comment/_recent_comment.html.twig', [
            'comments' => $comments
        ]);
    }
}
