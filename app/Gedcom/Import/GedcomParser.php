<?php

declare(strict_types=1);

namespace App\Gedcom\Import;

use Illuminate\Support\Facades\Log;

/**
 * GEDCOM file parser - handles parsing raw GEDCOM content into structured data
 */
class GedcomParser
{
    private GedcomData $parsedData;

    public function __construct()
    {
        $this->parsedData = new GedcomData();
    }

    /**
     * Parse GEDCOM content into structured data
     */
    public function parse(string $content): GedcomData
    {
        $gedcomData   = [];
        $individuals  = [];
        $families     = [];
        $mediaObjects = [];

        $lines         = explode("\n", str_replace(["\r\n", "\r"], "\n", $content));
        $currentRecord = null;

        // Add progress tracking
        $totalLines     = mb_substr_count($content, "\n");
        $processedLines = 0;
        
        // Track the hierarchy path for nested structures
        $levelStack = [];

        foreach ($lines as $lineNumber => $line) {
            // Progress logging
            if ($processedLines % 1000 === 0) {
                Log::debug("GEDCOM parsing progress: {$processedLines}/{$totalLines} lines");
            }
            $processedLines++;

            $line = mb_trim($line);
            if (empty($line)) {
                continue;
            }

            // Parse GEDCOM line more efficiently
            $parts = explode(' ', $line, 4);
            if (count($parts) < 2) {
                continue;
            }

            $level = (int) $parts[0];

            // Handle level 0 records (new records)
            if ($level === 0) {
                // Reset level stack for new record
                $levelStack = [];
                
                if (count($parts) >= 3) {
                    $possibleId = $parts[1];
                    $tag        = $parts[2];
                    $value      = $parts[3] ?? '';

                    // Check if this is an ID record (starts and ends with @)
                    if (str_starts_with($possibleId, '@') && str_ends_with($possibleId, '@')) {
                        $id = mb_trim($possibleId, '@');

                        if ($tag === 'INDI') {
                            $individuals[$id] = [
                                'id'   => $id,
                                'type' => 'INDI',
                                'data' => [],
                            ];
                            $currentRecord   = &$individuals[$id];
                            $currentRecordId = $id;
                        } elseif ($tag === 'FAM') {
                            $families[$id] = [
                                'id'   => $id,
                                'type' => 'FAM',
                                'data' => [],
                            ];
                            $currentRecord   = &$families[$id];
                            $currentRecordId = $id;
                        } elseif ($tag === 'OBJE') {
                            $mediaObjects[$id] = [
                                'id'   => $id,
                                'type' => 'OBJE',
                                'data' => [],
                            ];
                            $currentRecord   = &$mediaObjects[$id];
                            $currentRecordId = $id;
                        } else {
                            // Other ID records (SOUR, etc.)
                            $currentRecord   = null;
                            $currentRecordId = null;
                        }
                    } else {
                        // Non-ID level 0 records (HEAD, TRLR)
                        $tag             = $parts[1];
                        $value           = $parts[2] ?? '';
                        $gedcomData[]    = ['type' => $tag, 'value' => mb_trim($value)];
                        $currentRecord   = null;
                        $currentRecordId = null;
                    }
                }
            } elseif ($level >= 1 && $currentRecord !== null && isset($currentRecord['data'])) {
                // Handle nested levels dynamically
                $tag   = $parts[1];
                $value = implode(' ', array_slice($parts, 2));
                
                // Trim level stack to current level
                while (count($levelStack) > 0 && end($levelStack)['level'] >= $level) {
                    array_pop($levelStack);
                }
                
                // Create new data node
                $newNode = [
                    'tag'   => $tag,
                    'value' => mb_trim($value),
                    'level' => $level,
                    'data'  => [],
                ];
                
                // Add to appropriate parent
                if (empty($levelStack)) {
                    // Level 1 - add directly to record
                    $currentRecord['data'][] = $newNode;
                    $levelStack[] = [
                        'level' => $level,
                        'ref'   => &$currentRecord['data'][count($currentRecord['data']) - 1],
                    ];
                } else {
                    // Nested level - add to parent's data array
                    $parent = &$levelStack[count($levelStack) - 1]['ref'];
                    if (!isset($parent['data'])) {
                        $parent['data'] = [];
                    }
                    $parent['data'][] = $newNode;
                    $levelStack[] = [
                        'level' => $level,
                        'ref'   => &$parent['data'][count($parent['data']) - 1],
                    ];
                }
            }
        }

        // Remove references to avoid memory issues
        unset($currentRecord);

        // Debug logging to help identify the issue
        Log::info('GEDCOM Parse Complete', [
            'individuals_count'   => count($individuals),
            'families_count'      => count($families),
            'media_objects_count' => count($mediaObjects),
        ]);

        // Set parsed data
        $this->parsedData->setGedcomData($gedcomData);
        $this->parsedData->setIndividuals($individuals);
        $this->parsedData->setFamilies($families);
        $this->parsedData->setMediaObjects($mediaObjects);

        return $this->parsedData;
    }

    /**
     * Get the parsed data
     */
    public function getParsedData(): ?GedcomData
    {
        return $this->parsedData;
    }
}
