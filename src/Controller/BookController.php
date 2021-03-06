<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookController extends AbstractController
{
    /**
     * @Route("/", name="book_index", methods={"GET"})
     */
    public function index(Request $r): Response
    {
        $authors = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findAll();

        $books = $this->getDoctrine()->getRepository(Book::class);

        if ($r->query->get('sort') === 'title_az') {
            $books = $books->findBy([], ['title' => 'asc']);
        } elseif ($r->query->get('sort') === 'title_za') {
            $books = $books->findBy([], ['title' => 'desc']);
        } elseif ($r->query->get('author_id') !== null && $r->query->get('author_id') != 0) {
            $author = $this->getDoctrine()->
            getRepository(Author::class)->
            find($r->query->get('author_id'));
            $books = $author->getBooks();
        } elseif ($r->query->get('author_id') === 0) {
            $books = $books->findAll();
        } else {
            $books = $books->findAll();
        }

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'sortBy' => $r->query->get('sort') ?? 'default',
            'authors' => $authors,
            'authorId' => $r->query->get('author_id') ?? 0,
            'success' => $r->getSession()->getFlashBag()->get('success', [])
        ]);
    }

    /**
     * @Route("/book/create", name="book_create", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(Request $r): Response
    {
        $authors = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findAll();

        return $this->render('book/create.html.twig', [
            'authors' => $authors,
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }

    /**
     * @Route("/book/store", name="book_store", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function store(Request $r, ValidatorInterface $validator, UploaderHelper $uploaderHelper): Response
    {
        $submittedToken = $r->request->get('token');
        if (!$this->isCsrfTokenValid('', $submittedToken)) {
            $r->getSession()->getFlashBag()->add('errors', 'Invalid token.');
        }

        $authors = $this->getDoctrine()
            ->getRepository(Author::class)->findAll();

        if (count($authors) === 0) {
            $r->getSession()->getFlashBag()->add('errors', 'Add author first.');
            return $this->redirectToRoute('book_create');
        }

        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($r->request->get('book_author_id'));

        $book = new Book;

        $uploadedFile = $r->files->get('image');
        if ($uploadedFile) {

            $violations = $validator->validate(
                $uploadedFile,
                new File([
                    'maxSize' => '1M',
                    'mimeTypes' => [
                        'image/jpg',
                        'image/jpeg'
//                        'image/*'
                    ]
                ])
            );

            if ($violations->count() > 0) {
                $violation = $violations[0];
                $r->getSession()->getFlashBag()->add('errors', $violation->getMessage());
                return $this->redirectToRoute('book_create');
            }

            $newFilename = $uploaderHelper->uploadBookImage($uploadedFile);
            $book->setImage($newFilename);
        }

        $book
            ->setTitle($r->request->get('book_title'))
            ->setIsbn($r->request->get('book_isbn'))
            ->setPages((int)$r->request->get('book_pages'))
            ->setAbout($r->request->get('book_about'))
            ->setAuthor($author);

        $errors = $validator->validate($book);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            return $this->redirectToRoute('book_create');
        }

        if (!$this->isCsrfTokenValid('', $submittedToken)) {
            return $this->redirectToRoute('book_create');
        }


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($book);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', "Book {$book->getTitle()} was added.");

        return $this->redirectToRoute('book_index');
    }

    /**
     * @Route("/book/edit/{id}", name="book_edit", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $r, $id): Response
    {
        $book = $this->getDoctrine()
            ->getRepository(Book::class)
            ->find($id);

        $authors = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findAll();

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'authors' => $authors,
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }

    /**
     * @Route("/book/update/{id}", name="book_update", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function update(Request $r, ValidatorInterface $validator, $id, UploaderHelper $uploaderHelper): Response
    {
        $submittedToken = $r->request->get('token');
        if (!$this->isCsrfTokenValid('', $submittedToken)) {
            $r->getSession()->getFlashBag()->add('errors', 'Invalid token.');
        }

        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($r->request->get('book_author_id'));

        $book = $this->getDoctrine()
            ->getRepository(Book::class)
            ->find($id);

        $uploadedFile = $r->files->get('image');
        if ($uploadedFile) {

            $violations = $validator->validate(
                $uploadedFile,
                new File([
                    'maxSize' => '1M',
                    'mimeTypes' => [
                        'image/jpg',
                        'image/jpeg'
//                        'image/*'
                    ]
                ])
            );

            if ($violations->count() > 0) {
                $violation = $violations[0];
                $r->getSession()->getFlashBag()->add('errors', $violation->getMessage());
                return $this->redirectToRoute('book_edit', ['id' => $id]);
            }

            $newFilename = $uploaderHelper->uploadBookImage($uploadedFile);
            $book->setImage($newFilename);
        }

        $book
            ->setTitle($r->request->get('book_title'))
            ->setIsbn($r->request->get('book_isbn'))
            ->setPages((int)$r->request->get('book_pages'))
            ->setAbout($r->request->get('book_about'))
            ->setAuthor($author);

        $errors = $validator->validate($book);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            return $this->redirectToRoute('book_edit', ['id' => $id]);
        }

        if (!$this->isCsrfTokenValid('', $submittedToken)) {
            return $this->redirectToRoute('book_edit', ['id' => $id]);
        }


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($book);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', "Book {$book->getTitle()} was updated.");

        return $this->redirectToRoute('book_index');
    }

    /**
     * @Route("/book/delete/{id}", name="book_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $r, $id): Response
    {
        $book = $this->getDoctrine()
            ->getRepository(Book::class)
            ->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($book);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', "Book {$book->getTitle()} was deleted.");

        return $this->redirectToRoute('book_index');
    }

    /**
     * @Route("/book/view/{id}", name="book_view", methods={"GET"})
     */
    public function bookView($id): Response
    {
        $book = $this->getDoctrine()
            ->getRepository(Book::class)
            ->find($id);

        return $this->render('book/book.html.twig', [
            'book' => $book,
        ]);
    }
}
