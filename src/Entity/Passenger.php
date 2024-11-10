<?php

namespace App\Entity;

use App\Repository\PassengerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PassengerRepository::class)]
class Passenger extends User
{
    // Add any Passenger-specific properties here if needed
}
