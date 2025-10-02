<?php

declare(strict_types=1);

namespace App\Gedcom\Import;

/**
 * Data Transfer Object for parsed GEDCOM data
 */
class GedcomData
{
    private array $gedcomData = [];

    private array $individuals = [];

    private array $families = [];

    private array $mediaObjects = [];

    public function setGedcomData(array $gedcomData): void
    {
        $this->gedcomData = $gedcomData;
    }

    public function setIndividuals(array $individuals): void
    {
        $this->individuals = $individuals;
    }

    public function setFamilies(array $families): void
    {
        $this->families = $families;
    }

    public function setMediaObjects(array $mediaObjects): void
    {
        $this->mediaObjects = $mediaObjects;
    }

    public function getGedcomData(): array
    {
        return $this->gedcomData;
    }

    public function getIndividuals(): array
    {
        return $this->individuals;
    }

    public function getFamilies(): array
    {
        return $this->families;
    }

    public function getMediaObjects(): array
    {
        return $this->mediaObjects;
    }
}
