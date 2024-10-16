<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Psr\Log\LoggerInterface;

#[Route('/books')]
class BookController extends AbstractController
{
    private BookRepository $bookRepository;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger; // Optional for logging

    public function __construct(BookRepository $bookRepository, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->bookRepository = $bookRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger; // Optional logger for debugging purposes
    }

    #[Route('', name: 'book_index')]
    public function index(): Response
    {
        $allBooks = $this->bookRepository->findAll();
        $enabledBooks = $this->bookRepository->findBy(['enabled' => true]);

        $romanceBooks = $this->entityManager->createQuery('SELECT b FROM App\Entity\Book b WHERE b.category LIKE :category')
            ->setParameter('category', '%Romance%')
            ->getResult();

        return $this->render('book/index.html.twig', [
            'allBooks' => $allBooks,
            'enabledBooks' => $enabledBooks,
            'romanceBooks' => $romanceBooks,
        ]);
    }

    #[Route('/published_between', name: 'book_published_between')]
    public function findBooksPublishedBetween(): Response
    {
        $startDate = new \DateTime('2014-01-01');
        $endDate = new \DateTime('2018-12-31');

        $query = $this->entityManager->createQuery(
            'SELECT b FROM App\Entity\Book b WHERE b.publicationDate BETWEEN :start AND :end'
        )
        ->setParameter('start', $startDate)
        ->setParameter('end', $endDate);

        $books = $query->getResult();

        return $this->render('book/published_between.html.twig', [
            'books' => $books,
        ]);
    }
    
    #[Route('/new', name: 'book_new')]
    public function new(Request $request): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($book);
            $this->entityManager->flush();

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'book_show')]
    public function show(int $id): Response
    {
        $book = $this->bookRepository->find($id);

        if (!$book) {
            throw $this->createNotFoundException('No book found for id ' . $id);
        }

        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/{id}/edit', name: 'book_edit')]
    public function edit(Request $request, Book $book): Response
    {
        if (!$book) {
            throw $this->createNotFoundException('No book found for id ' . $book->getId());
        }

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/edit.html.twig', [
            'form' => $form->createView(),
            'book' => $book,
        ]);
    }

    #[Route('/{id}/delete', name: 'book_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book): Response
    {
        if (!$book) {
            $this->logger->error('No book found for id ' . $book->getId());
            throw $this->createNotFoundException('No book found for id ' . $book->getId());
        }

        if ($this->isCsrfTokenValid('delete' . $book->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($book);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('book_index');
    }

    #[Route('/category/romance', name: 'book_by_category')]
    public function findBookByCategory(): Response
    {
        $books = $this->entityManager->createQuery('SELECT b FROM App\Entity\Book b WHERE b.category LIKE :category')
            ->setParameter('category', '%Romance%')
            ->getResult();

        return $this->render('book/index.html.twig', [
            'books' => $books,
        ]);
    }


}
