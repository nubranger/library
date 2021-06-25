<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Title should not be blank.")
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "Title must be at least {{ limit }} characters long.",
     *      maxMessage = "Title cannot be longer than {{ limit }} characters."
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank(message="ISBN should not be blank.")
     */
    private $isbn;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Positive(message="Pages should be a positive number.")
     */
    private $pages;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="About should not be blank.")
     */
    private $about;

    /**
     * @ORM\ManyToOne(targetEntity=Author::class, inversedBy="books", cascade={"persist"})
     */
    private $author;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): self
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getPages(): ?int
    {
        return $this->pages;
    }

    public function setPages(int $pages): self
    {
        $this->pages = $pages;

        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(string $about): self
    {
        $this->about = $about;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

        return $this;
    }
}
