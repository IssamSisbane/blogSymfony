<?php

namespace App\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Category;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\PostType;
use App\Form\CommentType;
use App\Repository\PostRepository;


class PostController extends AbstractController
{
    /**
     * @Route("/monBlog", name="monBlog")
     * @var PostRepository $postRepository
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $posts = $this->getDoctrine()->getRepository(Post::class)->findByPublicationEffectiveLimit();

        $categoryn = $this->getDoctrine()->getRepository(Category::class)->findAll();


        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'categoryn' => $categoryn
        ]);
    }

    /**
     * @Route("/monBlog/post", name="post.list")
     *
     */
    public function list(Request $request, PaginatorInterface $paginator): Response
    {
        $donnees = $this->getDoctrine()->getRepository(Post::class)->findByPublicationEffective();

        $categoryn = $this->getDoctrine()->getRepository(Category::class)->findAll();

        $posts = $paginator->paginate(
            $donnees,
            $request->query->getInt('page',1),
            5
        );


        return $this->render('post/list.html.twig', [
            'posts' => $posts,
            'categoryn' => $categoryn
        ]);
    }

    /**
     * @Route("/monBlog/admin/post", name="adminPost")
     */
    public function listAdmin(Request $request, PaginatorInterface $paginator): Response
    {
        $donnees = $this->getDoctrine()->getRepository(Post::class)->findBy([],['publishedAt' => 'DESC']);

        $posts = $paginator->paginate(
            $donnees,
            $request->query->getInt('page',1),
            5
        );


        return $this->render('admin/post/index.html.twig', [
            'posts' => $posts,
        ]);
    }


    /**
     * @Route("/monBlog/listParCategorie/{id}", name="post.listeParCategorie")
     */
    public function listParCategorie(int $id ,Request $request, PaginatorInterface $paginator): Response
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id' => $id]);

        $categoryn = $this->getDoctrine()->getRepository(Category::class)->findAll();

        $donnees = $this->getDoctrine()->getRepository(Post::class)->findByCategorie($category);

        $posts = $paginator->paginate(
            $donnees,
            $request->query->getInt('page',1),
            5
        );


        return $this->render('post/parCatgorie.html.twig', [
            'posts' => $posts,
            'categoryn' => $categoryn,
            'categorieSelect' => $category
        ]);
    }


    /**
     * @Route("/monBlog/post/show/{slug}", name="post.show")
     */
    public function show($slug, Request $request): Response
    {
        // On récupère le `repository` en rapport avec l'entity `Post`
        $postRepository = $this->getDoctrine()->getRepository(Post::class);

        // On fait appel à la méthode générique `find` qui permet de SELECT en fonction d'un Id
        $post = $postRepository->findOneBy(['slug' => $slug]);

        if(!$post) {
            throw $this->createNotFoundException(
                "Pas de Post trouvé avec le slug ".$slug
            );
        }

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        // On recupère les données saisies
        $form->handleRequest($request);

        // On verifie si le formulaire a été envoyé et si les données sont valides
        if($form->isSubmitted() && $form->isValid())
        {
            $comment->setPost($post);

            $comment->setValid(false);

            // On instancie Doctrine
            $doctrine = $this->getDoctrine()->getManager();

            // On hydrate $comment
            $doctrine->persist($comment);

            // On ecrit dans la base de données
            $doctrine->flush();
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'formComment' => $form->createView(),
        ]);
    }

    /**
     * @Route("/monBlog/admin/post/show/{id}", name="post.Adminshow")
     */
    public function adminShow($id, Request $request): Response
    {
        // On récupère le `repository` en rapport avec l'entity `Post`
        $postRepository = $this->getDoctrine()->getRepository(Post::class);

        // On fait appel à la méthode générique `find` qui permet de SELECT en fonction d'un Id
        $post = $postRepository->find($id);

        if(!$post) {
            throw $this->createNotFoundException(
                "Pas de Post trouvé avec l'id ".$id
            );
        }

        return $this->render('admin/post/show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @Route("/monBlog/admin/post/create", name="post.create")
     */
    public function create(Request $request): Response
    {
        $post = new post();

        $form = $this->createForm(PostType::class, $post);

        // Récupère les données transmises par la requête pour les transmettre au formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $post = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('message', 'Votre Post a bien été publié');

            return $this->redirectToRoute('adminPost');

        }

        return $this->render('admin/post/update.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/monBlog/admin/post/edit/{id}", name="post.edit")
     */
    public function update($id, Request $request): Response
    {
        // On récupère le `repository` en rapport avec l'entity `Post`
        $postRepository = $this->getDoctrine()->getRepository(Post::class);

        $post = $postRepository->find($id);

        $form = $this->createForm(PostType::class, $post);

        // Récupère les données transmises par la requête pour les transmettre au formulaire
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()){

            $post = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('message', 'Votre Post a bien été modifié');

            return $this->redirectToRoute('post.Adminshow', ['id' => $post->getId()]);

        }

        return $this->render('admin/post/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/monBlog/admin/post/remove/{id}", name="post.remove")
     */
    public function remove($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $postRepository = $entityManager->getRepository(Post::class);

        $post = $postRepository->find($id);

        if(!$post) {
            throw $this->createNotFoundException(
                "Pas de Post trouvé avec l'id ".$id
            );
        }
        // On dit au manager que l'on veux supprimer cet objet en base de données
        $entityManager->remove($post);
        // On met à jour en base de données en supprimant la ligne correspondante (i.e. la requête DELETE)
        $entityManager->flush();

        $this->addFlash('message', 'Le Post a bien été supprimé');

        return $this->redirectToRoute('adminPost');
    }



}
