<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BookFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $author = new Author();
        $author->setName("Bo");
        $author->setSurname("Jones");

        $book = new Book();
        $book->setTitle("Mano knygos title");
        $book->setIsbn("regrefg3443");
        $book->setPages(44);
        $book->setAbout("Apie mano knyga");
        $book->setAuthor($author);

        $manager->persist($book);

        $author2 = new Author();
        $author2->setName("Co");
        $author2->setSurname("Gones");

        $book2 = new Book();
        $book2->setTitle("Mano knygos title");
        $book2->setIsbn("regrefg3443");
        $book2->setPages(44);
        $book2->setAbout("Apie mano knyga");
        $book2->setAuthor($author2);

        $manager->persist($book2);

        $author3 = new Author();
        $author3->setName("Jo");
        $author3->setSurname("Mones");

        $book3 = new Book();
        $book3->setTitle("Mano knygos title");
        $book3->setIsbn("regrefg3443");
        $book3->setPages(44);
        $book3->setAbout("Apie mano knyga");
        $book3->setAuthor($author3);

        $manager->persist($book3);

        $manager->flush();
    }
}
