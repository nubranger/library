<?php

namespace App\Controller;

use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    /**
     * @Route("/author", name="author_index", methods={"GET"})
     */
    public function index(): Response
    {
        $authors = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findAll();

        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
            'authors' => $authors
        ]);
    }

    /**
     * @Route("/author/create", name="author_create", methods={"GET"})
     */
    public function create(): Response
    {
        return $this->render('author/create.html.twig', []);
    }

    /**
     * @Route("/author/store", name="author_store", methods={"POST"})
     */
    public function store(request $r): Response
    {
        $author = new Author;
        $author->
        setName($r->request->get('author_name'))->
        setSurname($r->request->get('author_surname'));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();

        return $this->redirectToRoute('author_index');
    }

    /**
     * @Route("/author/edit/{id}", name="author_edit", methods={"GET"})
     */
    public function edit(int $id): Response
    {
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id);

        return $this->render('author/edit.html.twig', [
            'author' => $author
        ]);
    }

    /**
     * @Route("/author/update/{id}", name="author_update", methods={"POST"})
     */
    public function update(request $r, $id): Response
    {
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id);

        $author->
        setName($r->request->get('author_name'))->
        setSurname($r->request->get('author_surname'));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();

        return $this->redirectToRoute('author_index');
    }

    /**
     * @Route("/author/delete/{id}", name="author_delete", methods={"POST"})
     */
    public function delete($id): Response
    {
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($author);
        $entityManager->flush();

        return $this->redirectToRoute('author_index');
    }

    /**
     * @Route("/author/books/{id}", name="author_books", methods={"GET"})
     */
    public function authorBooks(int $id): Response
    {
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id);

        $books = $author->getBooks();

        return $this->render('author/books.html.twig', [
            'books' => $books,
            'author' => $author
        ]);
    }

}
