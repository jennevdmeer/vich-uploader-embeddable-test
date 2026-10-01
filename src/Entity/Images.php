<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Embeddable]
class Images
{
    #[Vich\UploadableField(mapping: 'thumbnail', fileNameProperty: 'thumbnailName')]
    public ?File $thumbnail = null;

    #[ORM\Column(nullable: true)]
    public ?string $thumbnailName = null;

    #[Vich\UploadableField(mapping: 'attachment', fileNameProperty: 'attachmentFile.name', size: 'attachmentFile.size', mimeType: 'attachmentFile.mimeType', originalName: 'attachmentFile.originalName', dimensions: 'attachmentFile.dimensions')]
    public ?File $attachment = null;

    #[ORM\Embedded(EmbeddedFile::class, columnPrefix: 'attachment_')]
    public EmbeddedFile $attachmentFile;

    #[ORM\Embedded(Banners::class, columnPrefix: 'banners_')]
    public Banners $banners;

    #[ORM\Column(nullable: true)]
    public DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->attachmentFile = new EmbeddedFile();
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

    public function setAttachment(?File $attachment): void
    {
        $this->attachment = $attachment;
        if ($attachment instanceof UploadedFile) {
            $this->updatedAt = new DateTimeImmutable();
        }
    }
}
