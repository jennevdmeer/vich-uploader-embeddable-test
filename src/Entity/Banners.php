<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Embeddable]
#[Vich\Uploadable]
class Banners
{
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?DateTimeImmutable $modifiedAt = null;

    #[Vich\UploadableField(mapping: 'large_rectangle', fileNameProperty: 'largeRectangleName')]
    #[Assert\Image(allowLandscape: true, allowPortrait: false)]
    private ?File $largeRectangle = null;

    #[ORM\Column(nullable: true)]
    public ?string $largeRectangleName = null;

    public function setLargeRectangle(?File $largeRectangle): void
    {
        $this->largeRectangle = $largeRectangle;

        if ($largeRectangle instanceof UploadedFile) {
            $this->modifiedAt = new DateTimeImmutable();
        }
    }

    public function getLargeRectangle(): ?File
    {
        return $this->largeRectangle;
    }

    /// Large Skyscraper

    #[Vich\UploadableField(mapping: 'large_skyscraper', fileNameProperty: 'largeSkyscraperName')]
    #[Assert\Image(allowLandscape: false, allowPortrait: true)]
    private ?File $largeSkyscraper = null;

    #[ORM\Column(nullable: true)]
    public ?string $largeSkyscraperName = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?DateTimeImmutable $largeSkyscraperModifiedAt = null;

    public function setLargeSkyscraper(?File $largeSkyscraper): void
    {
        $this->largeSkyscraper = $largeSkyscraper;

        if ($largeSkyscraper instanceof UploadedFile) {
            $this->modifiedAt = new DateTimeImmutable();
        }
    }

    public function getLargeSkyscraper(): ?File
    {
        return $this->largeSkyscraper;
    }
}
