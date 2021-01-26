<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

     /**
      * @return Post[] Returns an array of Post objects
      */
    public function findByPublicationEffective()
    {
        $date = new \DateTime('now');
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Post p
            WHERE p.publishedAt < :date
            ORDER BY p.publishedAt DESC'
        )->setParameter('date', $date);

        // returns an array of Product objects
        return $query->getResult();
        ;
    }

    /**
      * @return Post[] Returns an array of Post objects
      */
      public function findByPublicationEffectiveLimit()
      {
          $date = new \DateTime('now');
          $entityManager = $this->getEntityManager();
  
          $query = $entityManager->createQuery(
              'SELECT p
              FROM App\Entity\Post p
              WHERE p.publishedAt < :date
              ORDER BY p.publishedAt DESC'
          )
          ->setParameter('date', $date)
          ->setMaxResults(5);
  
          // returns an array of Product objects
          return $query->getResult();
          ;
      }

    /**
      * @return Post[] Returns an array of Post objects
      */
      public function findByCategorie(Category $category)
      {
          $date = new \DateTime('now');
          return $this->createQueryBuilder('p')
            ->join('p.categories', 'c')
            ->Where('c.id = :idC and p.publishedAt < :date')
            ->setParameter('idC', $category->getId())
            ->setParameter('date', $date)
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult()
          ;
      }
    

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
