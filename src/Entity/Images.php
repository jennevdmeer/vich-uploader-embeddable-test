<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Embeddable]
#[Vich\Uploadable]
class Images
{
    #[Vich\UploadableField(mapping: 'thumbnail', fileNameProperty: 'thumbnailName')]
    public ?File $thumbnail = null;

    #[ORM\Column(nullable: true)]
    public ?string $thumbnailName = null;

    #[ORM\Embedded(Banners::class, columnPrefix: 'banners_')]
    public Banners $banners;

    #[ORM\Column(nullable: true)]
    public DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->banners = new Banners();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setThumbnail(?File $thumbnail): void
    {
        $this->thumbnail = $thumbnail;
        if ($thumbnail instanceof UploadedFile) {
            $this->updatedAt = new DateTimeImmutable();
        }
    }
}
