<?php

declare(strict_types=1);

namespace App\Gedcom\Import;

use App\Models\Person;
use App\PersonPhotos;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Media importer - handles importing media files (photos) from GEDCOM archives
 */
class MediaImporter
{
    private ?string $tempMediaPath = null;

    private int $importedCount = 0;

    private int $skippedCount = 0;

    /**
     * Set the temporary path where media files are extracted
     */
    public function setTempMediaPath(?string $path): void
    {
        $this->tempMediaPath = $path;
    }

    /**
     * Import media files for a person
     *
     * @param  Person  $person  The person to import media for
     * @param  array  $individualData  The parsed GEDCOM individual data
     * @return int Number of successfully imported photos
     */
    public function importForPerson(Person $person, array $individualData): int
    {
        if (! $this->tempMediaPath || ! isset($individualData['data'])) {
            return 0;
        }

        $mediaFiles = $this->extractMediaFiles($individualData['data']);

        if (empty($mediaFiles)) {
            return 0;
        }

        $personPhotos = new PersonPhotos($person);
        $importedPhotos = [];

        foreach ($mediaFiles as $mediaFile) {
            try {
                $filePath = $this->findMediaFile($mediaFile['file']);

                if (! $filePath) {
                    Log::warning("GEDCOM Media Import: File not found", [
                        'person_id' => $person->id,
                        'file'      => $mediaFile['file'],
                    ]);
                    $this->skippedCount++;
                    continue;
                }

                // Check if file is an image
                if (! $this->isImageFile($filePath)) {
                    Log::info("GEDCOM Media Import: Skipping non-image file", [
                        'person_id' => $person->id,
                        'file'      => $mediaFile['file'],
                    ]);
                    $this->skippedCount++;
                    continue;
                }

                $importedPhotos[] = $filePath;
                $this->importedCount++;
            } catch (Exception $e) {
                Log::error("GEDCOM Media Import Error: {$e->getMessage()}", [
                    'person_id' => $person->id,
                    'file'      => $mediaFile['file'],
                ]);
                $this->skippedCount++;
            }
        }

        // Import all photos at once
        if (! empty($importedPhotos)) {
            $personPhotos->save($importedPhotos);
            Log::info("GEDCOM Media Import: Photos imported", [
                'person_id' => $person->id,
                'count'     => count($importedPhotos),
            ]);
        }

        return count($importedPhotos);
    }

    /**
     * Extract media file references from GEDCOM individual data
     */
    private function extractMediaFiles(array $data): array
    {
        $mediaFiles = [];

        foreach ($data as $field) {
            if (! is_array($field) || ! isset($field['tag'])) {
                continue;
            }

            // Handle OBJE tag with inline FILE
            if ($field['tag'] === 'OBJE' && isset($field['data'])) {
                foreach ($field['data'] as $subField) {
                    if ($subField['tag'] === 'FILE') {
                        $mediaFiles[] = [
                            'file'  => $subField['value'],
                            'title' => $this->extractTitle($field['data']),
                        ];
                    }
                }
            }

            // Handle OBJE tag with reference to media object (e.g., @M1@)
            if ($field['tag'] === 'OBJE' && str_starts_with($field['value'] ?? '', '@')) {
                // This would require resolving the media object reference
                // For now, we'll skip these and only handle inline OBJE
                Log::debug("GEDCOM Media Import: Skipping OBJE reference", [
                    'reference' => $field['value'],
                ]);
            }
        }

        return $mediaFiles;
    }

    /**
     * Extract title from media object data
     */
    private function extractTitle(array $data): ?string
    {
        foreach ($data as $field) {
            if ($field['tag'] === 'TITL') {
                return $field['value'] ?? null;
            }
        }

        return null;
    }

    /**
     * Find media file in the temporary extraction directory
     */
    private function findMediaFile(string $relativePath): ?string
    {
        // Normalize path separators
        $relativePath = str_replace('\\', '/', $relativePath);

        // Try exact path first
        $fullPath = $this->tempMediaPath . '/' . $relativePath;
        if (file_exists($fullPath) && is_file($fullPath)) {
            return $fullPath;
        }

        // Try case-insensitive search
        $filename = basename($relativePath);
        $directory = dirname($relativePath);

        // Search in the directory
        $searchPath = $this->tempMediaPath;
        if ($directory !== '.') {
            $searchPath .= '/' . $directory;
        }

        if (is_dir($searchPath)) {
            $files = scandir($searchPath);
            foreach ($files as $file) {
                if (strcasecmp($file, $filename) === 0) {
                    $foundPath = $searchPath . '/' . $file;
                    if (is_file($foundPath)) {
                        return $foundPath;
                    }
                }
            }
        }

        // Try searching in the root of temp directory
        if (is_dir($this->tempMediaPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->tempMediaPath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && strcasecmp($file->getFilename(), $filename) === 0) {
                    return $file->getPathname();
                }
            }
        }

        return null;
    }

    /**
     * Check if file is an image based on extension and MIME type
     */
    private function isImageFile(string $filePath): bool
    {
        $extension = Str::lower(pathinfo($filePath, PATHINFO_EXTENSION));
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'tif'];

        if (! in_array($extension, $imageExtensions)) {
            return false;
        }

        // Additional MIME type check
        $mimeType = mime_content_type($filePath);
        return str_starts_with($mimeType, 'image/');
    }

    /**
     * Get import statistics
     */
    public function getStatistics(): array
    {
        return [
            'imported' => $this->importedCount,
            'skipped'  => $this->skippedCount,
        ];
    }
}

