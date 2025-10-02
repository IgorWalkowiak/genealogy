<?php

declare(strict_types=1);

namespace App\Gedcom\Import;

use App\Models\Team;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Jetstream\Contracts\CreatesTeams;
use Laravel\Jetstream\Events\AddingTeam;
use ZipArchive;

/**
 * Main GEDCOM Import orchestrator class
 */
final class Import implements CreatesTeams
{
    public User $user;

    private Team $team;

    private GedcomParser $parser;

    private IndividualImporter $individualImporter;

    private FamilyImporter $familyImporter;

    private CoupleCreator $coupleCreator;

    private MediaImporter $mediaImporter;

    private ?string $tempExtractionPath = null;

    /**
     * Initialize with user and create a new team
     */
    public function __construct(?string $teamName, ?string $teamDescription)
    {
        $this->user = auth()->user();

        // Create new team for this import
        $this->team = $this->createTeam($teamName, $teamDescription);

        // Initialize sub-components
        $this->parser             = new GedcomParser();
        $this->mediaImporter      = new MediaImporter();
        $this->individualImporter = new IndividualImporter($this->team, $this->mediaImporter);
        $this->familyImporter     = new FamilyImporter($this->team);
        $this->coupleCreator      = new CoupleCreator($this->team);
    }

    /**
     * Import GEDCOM file from path (.ged or .gdz)
     */
    public function import(string $filePath): array
    {
        // At the start of your import method, increase time and memory limits
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '512M');

        try {
            DB::beginTransaction();

            // Determine file type and extract if necessary
            $gedcomContent = $this->prepareGedcomFile($filePath);

            // Parse GEDCOM content
            $parsedData = $this->parser->parse($gedcomContent);

            Log::info('GEDCOM IMPORT: parseGedcom', [
                'gedcomData'          => $parsedData->getGedcomData(),
                'media_objects_count' => count($parsedData->getMediaObjects()),
            ]);

            // Import individuals first (with media)
            $personMap = $this->individualImporter->import($parsedData->getIndividuals());

            Log::info('GEDCOM IMPORT: importIndividuals', [
                'individuals'      => count($parsedData->getIndividuals()),
                'personMap'        => count($personMap),
                'media_statistics' => $this->mediaImporter->getStatistics(),
            ]);

            // Import families and relationships
            $familyMap = $this->familyImporter->import($parsedData->getFamilies(), $personMap);

            Log::info('GEDCOM IMPORT: importFamilies', [
                'families'  => count($parsedData->getFamilies()),
                'familyMap' => count($familyMap),
            ]);

            // Create couples from families
            $this->coupleCreator->create($familyMap, $personMap);

            Log::info('GEDCOM IMPORT: createCouples');

            DB::commit();

            // Cleanup temporary files
            $this->cleanup();

            $mediaStats = $this->mediaImporter->getStatistics();

            return [
                'success'              => true,
                'team'                 => $this->team->name,
                'individuals_imported' => count($personMap),
                'families_imported'    => count($familyMap),
                'photos_imported'      => $mediaStats['imported'],
                'photos_skipped'       => $mediaStats['skipped'],
                'message'              => 'GEDCOM file imported successfully',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            $this->cleanup(); // Clean up even on error
            Log::error('GEDCOM Import Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare GEDCOM file - extract .gdz if needed, return GEDCOM content
     */
    private function prepareGedcomFile(string $filePath): string
    {
        $extension = Str::lower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Handle .ged files directly
        if ($extension === 'ged') {
            Log::info('GEDCOM IMPORT: Processing .ged file', ['path' => $filePath]);
            return file_get_contents($filePath);
        }

        // Handle .gdz (ZIP) files
        if ($extension === 'gdz' || $extension === 'zip') {
            Log::info('GEDCOM IMPORT: Processing .gdz archive', ['path' => $filePath]);
            return $this->extractGdzArchive($filePath);
        }

        throw new Exception("Unsupported file format: {$extension}. Only .ged and .gdz files are supported.");
    }

    /**
     * Extract .gdz (ZIP) archive and return GEDCOM content
     */
    private function extractGdzArchive(string $zipPath): string
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new Exception('Failed to open GEDCOM archive (.gdz file)');
        }

        // Create temporary extraction directory
        $this->tempExtractionPath = storage_path('app/temp/gedcom_import_' . uniqid());
        if (! mkdir($this->tempExtractionPath, 0755, true)) {
            throw new Exception('Failed to create temporary extraction directory');
        }

        // Extract all files
        if (! $zip->extractTo($this->tempExtractionPath)) {
            $zip->close();
            throw new Exception('Failed to extract GEDCOM archive');
        }

        $zip->close();

        // Find the .ged file in the extracted content
        $gedcomFile = $this->findGedcomFile($this->tempExtractionPath);

        if (! $gedcomFile) {
            throw new Exception('No .ged file found in the archive');
        }

        Log::info('GEDCOM IMPORT: Archive extracted', [
            'temp_path'    => $this->tempExtractionPath,
            'gedcom_file'  => $gedcomFile,
        ]);

        // Set media path for MediaImporter
        $this->mediaImporter->setTempMediaPath($this->tempExtractionPath);

        // Read and return GEDCOM content
        return file_get_contents($gedcomFile);
    }

    /**
     * Recursively find .ged file in directory
     */
    private function findGedcomFile(string $directory): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && Str::lower($file->getExtension()) === 'ged') {
                return $file->getPathname();
            }
        }

        return null;
    }

    /**
     * Cleanup temporary extraction directory
     */
    private function cleanup(): void
    {
        if ($this->tempExtractionPath && is_dir($this->tempExtractionPath)) {
            Log::info('GEDCOM IMPORT: Cleaning up temporary files', [
                'path' => $this->tempExtractionPath,
            ]);

            $this->deleteDirectory($this->tempExtractionPath);
            $this->tempExtractionPath = null;
        }
    }

    /**
     * Recursively delete directory
     */
    private function deleteDirectory(string $dir): bool
    {
        if (! is_dir($dir)) {
            return false;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }

    /**
     * Get import statistics
     */
    public function getStatistics(): array
    {
        // Only get parsed data if parsing has been done
        $parsedData = $this->parser->getParsedData();

        return [
            'individuals_parsed'   => $parsedData ? count($parsedData->getIndividuals()) : 0,
            'families_parsed'      => $parsedData ? count($parsedData->getFamilies()) : 0,
            'individuals_imported' => count($this->individualImporter->getPersonMap()),
            'families_imported'    => count($this->familyImporter->getFamilyMap()),
        ];
    }

    /**
     * Create a new team for the import
     */
    private function createTeam(string $name, ?string $description): Team
    {
        AddingTeam::dispatch($this->user);

        $this->user->switchTeam($team = $this->user->ownedTeams()->create([
            'name'          => $name,
            'description'   => $description ?? null,
            'personal_team' => false,
        ]));

        // -----------------------------------------------------------------------
        // create team photo folder
        // -----------------------------------------------------------------------
        if (! Storage::disk('photos')->exists($team->id)) {
            Storage::disk('photos')->makeDirectory($team->id);
        }
        // -----------------------------------------------------------------------

        return $team;
    }
}
