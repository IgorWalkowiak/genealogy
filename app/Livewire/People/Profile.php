<?php

declare(strict_types=1);

namespace App\Livewire\People;

use App\Livewire\Traits\TrimStringsAndConvertEmptyStringsToNull;
use App\Models\Gender;
use App\Models\Person;
use App\Rules\DobValid;
use App\Rules\YobValid;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

final class Profile extends Component
{
    use Interactions;
    use TrimStringsAndConvertEmptyStringsToNull;

    // -----------------------------------------------------------------------
    public Person $person;

    public bool $editMode = false;

    // Form fields
    public $firstname = null;
    public $surname = null;
    public $birthname = null;
    public $nickname = null;
    public $sex = null;
    public $gender_id = null;

    #[Validate]
    public $yob = null;

    #[Validate]
    public $dob = null;

    public $pob = null;
    public $summary = null;

    // -----------------------------------------------------------------------
    protected $listeners = [
        'person_updated' => 'render',
        'couple_deleted' => 'render',
    ];

    // -----------------------------------------------------------------------
    #[Computed(persist: true, seconds: 3600, cache: true)]
    public function genders(): Collection
    {
        return Gender::select(['id', 'name'])->orderBy('name')->get();
    }

    // -----------------------------------------------------------------------
    public function enableEditMode(): void
    {
        if (!auth()->user()->hasPermission('person:update')) {
            return;
        }

        $this->editMode = true;
        $this->loadFormData();
    }

    public function cancelEdit(): void
    {
        $this->editMode = false;
        $this->resetValidation();
    }

    public function saveProfile(): void
    {
        if (!auth()->user()->hasPermission('person:update')) {
            return;
        }

        $validated = $this->validate();

        $this->person->update($validated);

        $this->toast()->success(__('app.save'), __('app.saved'))->send();

        $this->editMode = false;

        $this->dispatch('person_updated');
    }

    // -----------------------------------------------------------------------
    public function confirm(): void
    {
        $this->dialog()
            ->question(__('app.attention') . '!', __('app.are_you_sure'))
            ->confirm(__('app.delete_yes'))
            ->cancel(__('app.cancel'))
            ->hook([
                'ok' => [
                    'method' => 'delete',
                ],
            ])
            ->send();
    }

    public function delete(): void
    {
        if ($this->person->isDeletable()) {
            $this->deletePersonPhotos();

            $this->person->delete();

            $this->toast()->success(__('app.delete'), e($this->person->name) . ' ' . __('app.deleted') . '.')->flash()->send();

            $this->redirect('/search');
        }
    }

    // ------------------------------------------------------------------------------
    public function render(): View
    {
        return view('livewire.people.profile');
    }

    // ------------------------------------------------------------------------------
    protected function rules(): array
    {
        return [
            'firstname' => ['nullable', 'string', 'max:255'],
            'surname'   => ['required', 'string', 'max:255'],
            'birthname' => ['nullable', 'string', 'max:255'],
            'nickname'  => ['nullable', 'string', 'max:255'],

            'sex'       => ['required', 'string', 'max:1', 'in:m,f'],
            'gender_id' => ['nullable', 'integer'],

            'yob' => [
                'nullable',
                'integer',
                'min:1',
                'max:' . date('Y'),
                new YobValid,
            ],
            'dob' => [
                'nullable',
                'date_format:Y-m-d',
                'before_or_equal:today',
                new DobValid,
            ],
            'pob' => ['nullable', 'string', 'max:255'],

            'summary' => ['nullable', 'string', 'max:65535'],
        ];
    }

    protected function messages(): array
    {
        return [];
    }

    protected function validationAttributes(): array
    {
        return [
            'firstname' => __('person.firstname'),
            'surname'   => __('person.surname'),
            'birthname' => __('person.birthname'),
            'nickname'  => __('person.nickname'),

            'sex'       => __('person.sex'),
            'gender_id' => __('person.gender'),

            'yob' => __('person.yob'),
            'dob' => __('person.dob'),
            'pob' => __('person.pob'),

            'summary' => __('person.summary'),
        ];
    }

    // ------------------------------------------------------------------------------
    private function loadFormData(): void
    {
        $this->firstname = $this->person->firstname;
        $this->surname   = $this->person->surname;
        $this->birthname = $this->person->birthname;
        $this->nickname  = $this->person->nickname;
        $this->sex       = $this->person->sex;
        $this->gender_id = $this->person->gender_id;
        $this->yob       = $this->person->yob ?? null;
        $this->dob       = $this->person->dob ? Carbon::parse($this->person->dob)->format('Y-m-d') : null;
        $this->pob       = $this->person->pob;
        $this->summary   = $this->person->summary;
    }

    private function deletePersonPhotos(): void
    {
        defer(function (): void {
            $disk       = Storage::disk('photos');
            $personPath = $this->person->team_id . '/' . $this->person->id;

            // Check if the person's directory exists
            if (! $disk->exists($personPath)) {
                return;
            }

            // Get all files in the person's directory
            $files = $disk->files($personPath);

            // Filter to only image files belonging to this person
            $personFiles = collect($files)->filter(function ($file): bool {
                $filename = basename($file);
                $personId = $this->person->id;

                // Check if filename starts with personId_ and has valid image extension
                if (! str_starts_with($filename, $personId . '_')) {
                    return false;
                }

                // Get valid extensions from config
                $acceptedFormats = config('app.upload_photo_accept', []);
                $validExtensions = collect($acceptedFormats)->map(function ($label, $mimeType) {
                    // Convert MIME types to file extensions
                    return match ($mimeType) {
                        'image/bmp'     => 'bmp',
                        'image/gif'     => 'gif',
                        'image/jpeg'    => ['jpg', 'jpeg'],
                        'image/png'     => 'png',
                        'image/svg+xml' => 'svg',
                        'image/tiff'    => ['tiff', 'tif'],
                        'image/webp'    => 'webp',
                        default         => null,
                    };
                })->filter()->flatten()->toArray();

                $extension = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                return in_array($extension, $validExtensions);
            });

            // Delete the files
            if ($personFiles->isNotEmpty()) {
                $disk->delete($personFiles->toArray());
            }

            // Remove the person's directory if it's now empty
            $remainingFiles = $disk->files($personPath);
            if (empty($remainingFiles)) {
                $disk->deleteDirectory($personPath);
            }
        });
    }
}
