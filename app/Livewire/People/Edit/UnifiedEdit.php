<?php

declare(strict_types=1);

namespace App\Livewire\People\Edit;

use App\Countries;
use App\Livewire\Traits\TrimStringsAndConvertEmptyStringsToNull;
use App\Models\Couple;
use App\Models\Gender;
use App\Models\Person;
use App\Rules\DobValid;
use App\Rules\DodValid;
use App\Rules\ParentsIdExclusive;
use App\Rules\YobValid;
use App\Rules\YodValid;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

final class UnifiedEdit extends Component
{
    use Interactions;
    use TrimStringsAndConvertEmptyStringsToNull;

    // -----------------------------------------------------------------------
    public Person $person;

    // -----------------------------------------------------------------------
    // Profile fields
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

    // Contact fields
    public $street = null;

    public $number = null;

    public $postal_code = null;

    public $city = null;

    public $province = null;

    public $state = null;

    public $country = null;

    public $phone = null;

    // Death fields
    #[Validate]
    public $yod = null;

    #[Validate]
    public $dod = null;

    public $pod = null;

    public $cemetery_location_name = null;

    public $cemetery_location_address = null;

    public $cemetery_location_latitude = null;

    public $cemetery_location_longitude = null;

    // Family fields
    public $father_id = null;

    public $mother_id = null;

    public $parents_id = null;

    public Collection $fathers;

    public Collection $mothers;

    public Collection $parents;

    // -----------------------------------------------------------------------
    #[Computed(persist: true, seconds: 3600, cache: true)]
    public function genders(): Collection
    {
        return Gender::select(['id', 'name'])->orderBy('name')->get();
    }

    #[Computed(persist: true, seconds: 3600, cache: true)]
    public function countries(): Collection
    {
        return (new Countries(app()->getLocale()))->getAllCountries();
    }

    // -----------------------------------------------------------------------
    public function mount(): void
    {
        $this->loadData();
        $this->loadFamilyOptions();
    }

    public function saveProfile(): void
    {
        $validated = $this->validate($this->profileRules());

        $this->person->update($validated);

        $this->dispatch('person_updated');

        $this->toast()->success(__('app.save'), __('app.saved'))->send();
    }

    public function saveContact(): void
    {
        $validated = $this->validate($this->contactRules());

        $this->person->update($validated);

        $this->dispatch('person_updated');

        $this->toast()->success(__('app.save'), __('app.saved'))->send();
    }

    public function saveDeath(): void
    {
        $validated = $this->validate($this->deathRules());

        $this->person->update([
            'yod' => $this->yod ?? null,
            'dod' => $this->dod ?? null,
            'pod' => $this->pod ?? null,
        ]);

        // update or create metadata
        $this->person->updateMetadata(
            collect($validated)
                ->forget(['yod', 'dod', 'pod'])
                ->filter(fn ($value, $key): bool => $value !== $this->person->getMetadataValue($key))
        );

        $this->dispatch('person_updated');

        $this->toast()->success(__('app.save'), __('app.saved'))->send();
    }

    public function saveFamily(): void
    {
        $validated = $this->validate($this->familyRules());

        $this->person->update($validated);

        $this->dispatch('person_updated');

        $this->toast()->success(__('app.save'), __('app.saved'))->send();
    }

    // ------------------------------------------------------------------------------
    public function render(): View
    {
        return view('livewire.people.edit.unified-edit');
    }

    // -----------------------------------------------------------------------
    protected function profileRules(): array
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

    protected function contactRules(): array
    {
        return [
            'street'      => ['nullable', 'string', 'max:100'],
            'number'      => ['nullable', 'string', 'max:20'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city'        => ['nullable', 'string', 'max:100'],
            'province'    => ['nullable', 'string', 'max:100'],
            'state'       => ['nullable', 'string', 'max:100'],
            'country'     => ['nullable', 'string', 'max:2'],
            'phone'       => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function deathRules(): array
    {
        return [
            'yod'                         => ['nullable', 'integer', 'min:1', 'max:' . date('Y'), new YodValid],
            'dod'                         => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today', new DodValid],
            'pod'                         => ['nullable', 'string', 'max:255'],
            'cemetery_location_name'      => ['nullable', 'string', 'max:255'],
            'cemetery_location_address'   => ['nullable', 'string', 'max:255'],
            'cemetery_location_latitude'  => ['nullable', 'numeric', 'decimal:0,13', 'min:-90', 'max:90', 'required_with:cemetery_location_longitude'],
            'cemetery_location_longitude' => ['nullable', 'numeric', 'decimal:0,13', 'min:-180', 'max:180', 'required_with:cemetery_location_latitude'],
        ];
    }

    protected function familyRules(): array
    {
        return [
            'father_id'  => ['nullable', 'integer'],
            'mother_id'  => ['nullable', 'integer'],
            'parents_id' => [
                'nullable',
                'integer',
                new ParentsIdExclusive($this->father_id, $this->mother_id),
            ],
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

            'street'      => __('person.street'),
            'number'      => __('person.number'),
            'postal_code' => __('person.postal_code'),
            'city'        => __('person.city'),
            'province'    => __('person.province'),
            'state'       => __('person.state'),
            'country'     => __('person.country'),
            'phone'       => __('person.phone'),

            'yod'                         => __('person.yod'),
            'dod'                         => __('person.dod'),
            'pod'                         => __('person.pod'),
            'cemetery_location_name'      => __('metadata.location_name'),
            'cemetery_location_address'   => __('metadata.address'),
            'cemetery_location_latitude'  => __('metadata.latitude'),
            'cemetery_location_longitude' => __('metadata.longitude'),

            'father_id'  => __('person.father'),
            'mother_id'  => __('person.mother'),
            'parents_id' => __('person.parents'),
        ];
    }

    // ------------------------------------------------------------------------------
    private function loadData(): void
    {
        // Profile
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

        // Contact
        $this->street      = $this->person->street;
        $this->number      = $this->person->number;
        $this->postal_code = $this->person->postal_code;
        $this->city        = $this->person->city;
        $this->province    = $this->person->province;
        $this->state       = $this->person->state;
        $this->country     = $this->person->country;
        $this->phone       = $this->person->phone;

        // Death
        $this->yod                         = $this->person->yod;
        $this->dod                         = $this->person->dod ? Carbon::parse($this->person->dod)->format('Y-m-d') : null;
        $this->pod                         = $this->person->pod;
        $this->cemetery_location_name      = $this->person->getMetadataValue('cemetery_location_name');
        $this->cemetery_location_address   = $this->person->getMetadataValue('cemetery_location_address');
        $this->cemetery_location_latitude  = $this->person->getMetadataValue('cemetery_location_latitude');
        $this->cemetery_location_longitude = $this->person->getMetadataValue('cemetery_location_longitude');

        // Family
        $this->father_id  = $this->person->father_id;
        $this->mother_id  = $this->person->mother_id;
        $this->parents_id = $this->person->parents_id;
    }

    private function loadFamilyOptions(): void
    {
        $persons = Person::where('id', '!=', $this->person->id)
            ->olderThan($this->person->dob, $this->person->yob)
            ->orderBy('firstname')->orderBy('surname')
            ->get();

        $this->fathers = $persons->where('sex', 'm')->map(fn ($p): array => [
            'id'   => $p->id,
            'name' => $p->name . ($p->birth_formatted ? ' (' . $p->birth_formatted . ')' : ''),
        ])->values();

        $this->mothers = $persons->where('sex', 'f')->map(fn ($p): array => [
            'id'   => $p->id,
            'name' => $p->name . ($p->birth_formatted ? ' (' . $p->birth_formatted . ')' : ''),
        ])->values();

        $this->parents = Couple::with(['person1', 'person2'])
            ->olderThan($this->person->birth_year)
            ->get()
            ->sortBy('name')
            ->map(fn ($couple): array => [
                'id'     => $couple->id,
                'couple' => $couple->name . ($couple->date_start ? ' (' . $couple->date_start_formatted . ')' : ''),
            ])->values();
    }
}

