<?php

declare(strict_types=1);

namespace App\Livewire\People;

use App\Livewire\Traits\TrimStringsAndConvertEmptyStringsToNull;
use App\Models\Couple;
use App\Models\Person;
use App\Rules\ParentsIdExclusive;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

final class Family extends Component
{
    use Interactions;
    use TrimStringsAndConvertEmptyStringsToNull;

    // ------------------------------------------------------------------------------
    public Person $person;

    public bool $editMode = false;

    // Form fields
    public $father_id = null;
    public $mother_id = null;
    public $parents_id = null;

    public Collection $fathers;
    public Collection $mothers;
    public Collection $parents;

    // ------------------------------------------------------------------------------
    protected $listeners = [
        'couple_deleted' => 'render',
        'person_updated' => 'render',
    ];

    // ------------------------------------------------------------------------------
    public function mount(): void
    {
        $this->loadOptions();
    }

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

    public function saveFamily(): void
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

    // ------------------------------------------------------------------------------
    public function render(): View
    {
        return view('livewire.people.family');
    }

    // -----------------------------------------------------------------------
    protected function rules(): array
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
            'father_id'  => __('person.father'),
            'mother_id'  => __('person.mother'),
            'parents_id' => __('person.parents'),
        ];
    }

    // ------------------------------------------------------------------------------
    private function loadFormData(): void
    {
        $this->father_id  = $this->person->father_id;
        $this->mother_id  = $this->person->mother_id;
        $this->parents_id = $this->person->parents_id;
    }

    private function loadOptions(): void
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
