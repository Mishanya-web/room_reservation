<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'houses')]
class House
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $amenities = null;

    #[ORM\Column]
    private ?int $beds = null;

    #[ORM\Column]
    private ?int $distance = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column]
    private ?bool $available = true;

    #[ORM\OneToMany(mappedBy: 'house', targetEntity: Booking::class)]
    private Collection $bookings;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

}
