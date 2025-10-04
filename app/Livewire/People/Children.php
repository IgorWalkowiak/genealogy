<?php

declare(strict_types=1);

namespace App\Livewire\People;

use App\Models\Person;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

final class Children extends Component
{
    use Interactions;

    // ------------------------------------------------------------------------------
    public Person $person;

    public bool $editMode = false;

    public array $selected_persons = [];

    // ------------------------------------------------------------------------------
    public Collection $children;

    // ------------------------------------------------------------------------------
    #[Computed(cache: true)]
    public function availablePersons(): Collection
    {
        return Person::where('id', '!=', $this->person->id)
            ->whereNull($this->person->sex === 'm' ? 'father_id' : 'mother_id')
            ->youngerThan($this->person->dob, $this->person->yob)
            ->olderThan($this->person->dod, $this->person->yod)
            ->orderBy('firstname')
            ->orderBy('surname')
            ->select(['id', 'firstname', 'surname', 'sex', 'dob', 'yob'])
            ->get()
            ->map(fn ($p): array => [
                'id'   => $p->id,
                'name' => trim($p->firstname . ' ' . $p->surname) . ' [' . ($p->sex === 'm' ? __('app.male') : __('app.female')) . ']' . ($p->dob || $p->yob ? ' (' . ($p->dob ?: $p->yob) . ')' : ''),
            ]);
    }

    // ------------------------------------------------------------------------------
    public function mount(): void
    {
        $this->children = $this->person->childrenNaturalAll();
    }

    // -----------------------------------------------------------------------
    public function enableEditMode(): void
    {
        if (!auth()->user()->hasPermission('person:update')) {
            return;
        }

        $this->editMode = true;
        $this->selected_persons = [];
    }

    public function cancelEdit(): void
    {
        $this->editMode = false;
        $this->selected_persons = [];
        $this->resetValidation();
    }

    public function addChildren(): void
    {
        if (!auth()->user()->hasPermission('person:create')) {
            return;
        }

        if (empty($this->selected_persons)) {
            return;
        }

        $this->validate([
            'selected_persons' => ['required', 'array', 'min:1'],
            'selected_persons.*' => ['integer', 'exists:people,id'],
        ]);

        $key = $this->person->sex === 'm' ? 'father_id' : 'mother_id';
        
        // Batch update for better performance
        Person::whereIn('id', $this->selected_persons)
            ->update([$key => $this->person->id]);

        $this->selected_persons = [];

        // Refresh only the necessary relations instead of loading the entire model
        $this->person->unsetRelation('children');
        $this->children = $this->person->childrenNaturalAll();
        // unset($this->availablePersons); // Clear cache to refresh list

        // Don't dispatch person_updated - it triggers unnecessary re-renders in other components
        $this->dispatch('person_updated');
    }

    public function disconnect(int $child_id): void
    {
        $child = Person::findOrFail($child_id);

        $key = $this->person->sex === 'm' ? 'father_id' : 'mother_id';
        $child->update([$key => null]);

        // Refresh only the necessary relations instead of loading the entire model
        $this->person->unsetRelation('children');
        $this->children = $this->person->childrenNaturalAll();
        // unset($this->availablePersons); // Clear cache to refresh list

        // Don't dispatch person_updated - it triggers unnecessary re-renders in other components
        $this->dispatch('person_updated');
    }

    // ------------------------------------------------------------------------------
    public function render(): View
    {
        return view('livewire.people.children');
    }

    // ------------------------------------------------------------------------------
    protected function validationAttributes(): array
    {
        return [
            'selected_persons' => __('person.children'),
        ];
    }
}
