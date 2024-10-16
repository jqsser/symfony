<?php
namespace App\Controller;
use App\Entity\Book;
use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AuthorSearchType;

#[Route('/authors')]
class AuthorController extends AbstractController
{
    private AuthorRepository $authorRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(AuthorRepository $authorRepository, EntityManagerInterface $entityManager)
    {
        $this->authorRepository = $authorRepository;
        $this->entityManager = $entityManager;
    }



    #[Route('/author/search', name: 'author_search')]
    public function search(Request $request): Response
    {
        $form = $this->createForm(AuthorSearchType::class);
        $form->handleRequest($request);

        return $this->render('author/search.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/', name: 'author_index')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(AuthorSearchType::class);
        $form->handleRequest($request);

        $authors = $this->authorRepository->findAll(); 

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $minBooks = $data['minBooks'] ?? 0;
            $maxBooks = $data['maxBooks'] ?? PHP_INT_MAX;

            $authors = $this->entityManager->createQuery(
                'SELECT a FROM App\Entity\Author a LEFT JOIN a.books b GROUP BY a.id HAVING COUNT(b.id) BETWEEN :min AND :max'
            )
            ->setParameter('min', $minBooks)
            ->setParameter('max', $maxBooks)
            ->getResult();
        }

        return $this->render('author/index.html.twig', [
            'authors' => $authors,
            'searchForm' => $form->createView(), 
        ]);
    }

    #[Route('/new', name: 'author_new')]
    public function new(Request $request): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($author);
            $this->entityManager->flush();

            return $this->redirectToRoute('author_index');
        }

        return $this->render('author/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/delete-empty', name: 'delete_empty_authors')]
    public function deleteEmptyAuthors(): Response
    {
        $this->deleteAuthorsWithNoBooks();

        return $this->redirectToRoute('author_index');
    }
    private function deleteAuthorsWithNoBooks(): void
{
    // Step 1: Find authors with no books
    $authorsWithNoBooks = $this->entityManager->createQueryBuilder()
        ->select('a.id')
        ->from(Author::class, 'a')
        ->leftJoin('a.books', 'b') // Ensure you have the relationship set up
        ->groupBy('a.id')
        ->having('COUNT(b.id) = 0')
        ->getQuery()
        ->getResult();

    // Step 2: Extract the IDs
    $authorIds = array_map(fn($author) => $author['id'], $authorsWithNoBooks);

    // Step 3: Delete authors with no books if there are any
    if (!empty($authorIds)) {
        $this->entityManager->createQueryBuilder()
            ->delete(Author::class, 'a')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $authorIds)
            ->getQuery()
            ->execute();
    }
}

    

    #[Route('/{id}', name: 'author_show')]
    public function show(int $id): Response
    {
        $author = $this->authorRepository->find($id);

        if (!$author) {
            throw $this->createNotFoundException('Author not found');
        }

        return $this->render('author/show.html.twig', [
            'author' => $author,
        ]);
    }

    #[Route('/{id}/edit', name: 'author_edit')]
    public function edit(Request $request, int $id): Response
    {
        $author = $this->authorRepository->find($id);

        if (!$author) {
            throw $this->createNotFoundException('Author not found');
        }

        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('author_index');
        }

        return $this->render('author/edit.html.twig', [
            'form' => $form->createView(),
            'author' => $author,
        ]);
    }

    #[Route('/{id}/delete', name: 'author_delete')]
    public function delete(Request $request, int $id): Response
    {
        $author = $this->authorRepository->find($id);

        if (!$author) {
            throw $this->createNotFoundException('Author not found');
        }

        if ($this->isCsrfTokenValid('delete' . $author->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($author);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('author_index');
    }
}



