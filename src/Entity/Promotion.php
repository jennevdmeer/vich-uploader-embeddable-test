<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Promotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\Embedded(Images::class, columnPrefix: 'img_')]
    public Images $images;

    public function __construct()
    {
        $this->images = new Images();
    }
}
