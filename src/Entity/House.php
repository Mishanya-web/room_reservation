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

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAmenities(): ?string
    {
        return $this->amenities;
    }

    public function getBeds(): ?int
    {
        return $this->beds;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function getAvailable(): ?bool
    {
        return $this->available;
    }

    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    // Setters
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setAmenities(?string $amenities): self
    {
        $this->amenities = $amenities;
        return $this;
    }

    public function setBeds(int $beds): self
    {
        $this->beds = $beds;
        return $this;
    }

    public function setDistance(int $distance): self
    {
        $this->distance = $distance;
        return $this;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function setAvailable(bool $available): self
    {
        $this->available = $available;
        return $this;
    }

    // Для отображения в админке
    public function __toString(): string
    {
        return $this->name ?? 'House';
    }
}
