<?php

namespace App\Controller;

use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthorController extends AbstractController
{
    /**
     * @Route("/author", name="author_index", methods={"GET"})
     */
    public function index(Request $r): Response
    {
        $authors = $this->getDoctrine()
            ->getRepository(Author::class);

        if ($r->query->get('sort') == 'name_az')
            $authors = $authors->findBy([], ['name' => 'asc']);
        elseif ($r->query->get('sort') == 'name_za')
            $authors = $authors->findBy([], ['name' => 'desc']);
        elseif ($r->query->get('sort') == 'surname_az')
            $authors = $authors->findBy([], ['surname' => 'asc']);
        elseif ($r->query->get('sort') == 'surname_za')
            $authors = $authors->findBy([], ['surname' => 'desc']);
        else $authors = $authors->findAll();

        return $this->render('author/index.html.twig', [
            'authors' => $authors,
            'sortBy' => $r->query->get('sort') ?? 'default',
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'success' => $r->getSession()->getFlashBag()->get('success', [])
        ]);
    }

    /**
     * @Route("/author/create", name="author_create", methods={"GET"})
     */
    public function create(Request $r): Response
    {
        return $this->render('author/create.html.twig', [
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }

    /**
     * @Route("/author/store", name="author_store", methods={"POST"})
     */
    public function store(Request $r, ValidatorInterface $validator): Response
    {
        $author = new Author;
        $author->
        setName($r->request->get('author_name'))->
        setSurname($r->request->get('author_surname'));

        $errors = $validator->validate($author);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            return $this->redirectToRoute('author_create');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', "Author {$author->getName()} {$author->getSurname()} was created.");

        return $this->redirectToRoute('author_index');
    }

    /**
     * @Route("/author/edit/{id}", name="author_edit", methods={"GET"})
     */
    public function edit(Request $r, $id): Response
    {
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id);

        return $this->render('author/edit.html.twig', [
            'author' => $author,
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }

    /**
     * @Route("/author/update/{id}", name="author_update", methods={"POST"})
     */
    public function update(Request $r, ValidatorInterface $validator, $id): Response
    {
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id);

        $author->
        setName($r->request->get('author_name'))->
        setSurname($r->request->get('author_surname'));


        $errors = $validator->validate($author);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            return $this->redirectToRoute('author_edit', ['id' => $id]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', "Author {$author->getName()} {$author->getSurname()} was updated.");

        return $this->redirectToRoute('author_index');
    }

    /**
     * @Route("/author/delete/{id}", name="author_delete", methods={"POST"})
     */
    public function delete(Request $r, $id)
    {
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id);

        if ($author->getBooks()->count() > 0) {
            $r->getSession()->getFlashBag()->add('errors', "Can't delete: Author {$author->getName()} {$author->getSurname()} has {$author->getBooks()->count()} books.");
            return $this->redirectToRoute('author_index');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($author);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', "Author {$author->getName()} {$author->getSurname()} was deleted.");

        return $this->redirectToRoute('author_index');
    }

    /**
     * @Route("/author/books/{id}", name="author_books", methods={"GET"})
     */
    public function authorBooks($id): Response
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
