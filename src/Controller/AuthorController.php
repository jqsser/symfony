<?php
// src/Controller/AuthorController.php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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

    #[Route('', name: 'author_index')]
    public function index(): Response
    {
        $authors = $this->authorRepository->findAll();
        return $this->render('author/index.html.twig', [
            'authors' => $authors,
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
