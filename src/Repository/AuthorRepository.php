<?php

namespace App\Repository;
use App\Entity\Book;
use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    public function deleteAuthorsWithNoBooks(): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager = $this->_em;
        // Correct the query to properly reference the author id
        $query = $entityManager->createQuery(
            'DELETE FROM App\Entity\Author a WHERE a.id NOT IN (
                SELECT b.author FROM App\Entity\Book b
            )'
        );
    
        $query->execute();
    }

}
