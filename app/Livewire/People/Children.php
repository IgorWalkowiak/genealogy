<?php

declare(strict_types=1);

namespace App\Livewire\People;

use App\Models\Person;
use Illuminate\Support\Collection;
use Illuminate\View\View;
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
    public Collection $availablePersons;

    // ------------------------------------------------------------------------------
    public function mount(): void
    {
        $this->children = $this->person->childrenNaturalAll();
        $this->loadAvailablePersons();
    }

    // -----------------------------------------------------------------------
    public function enableEditMode(): void
    {
        if (!auth()->user()->hasPermission('person:update')) {
            return;
        }

        $this->editMode = true;
        $this->selected_persons = [];
        $this->loadAvailablePersons();
    }

    public function cancelEdit(): void
    {
        $this->editMode = false;
        $this->selected_persons = [];
        $this->resetValidation();
    }

    public function addChildren(): void
    {
        // Debug
        logger('addChildren called', [
            'selected_persons' => $this->selected_persons,
            'count' => count($this->selected_persons),
        ]);

        if (!auth()->user()->hasPermission('person:create')) {
            $this->toast()->error(__('app.error'), 'Brak uprawnień')->send();
            return;
        }

        if (empty($this->selected_persons)) {
            $this->toast()->error(__('app.error'), 'Nie wybrano żadnych osób')->send();
            return;
        }

        $this->validate([
            'selected_persons' => ['required', 'array', 'min:1'],
            'selected_persons.*' => ['integer', 'exists:people,id'],
        ]);

        $count = 0;
        foreach ($this->selected_persons as $person_id) {
            $child = Person::findOrFail($person_id);

            $child->update([
                $this->person->sex === 'm' ? 'father_id' : 'mother_id' => $this->person->id,
            ]);
            $count++;
        }

        $message = $count === 1 
            ? __('person.existing_person_linked_as_child')
            : trans_choice('person.children_linked', $count, ['count' => $count]);

        $this->toast()->success(__('app.save'), $message)->send();

        $this->selected_persons = [];

        $this->children = $this->person->fresh()->childrenNaturalAll();
        $this->loadAvailablePersons();

        $this->dispatch('person_updated');
    }

    public function confirm(int $child_id): void
    {
        $this->dialog()
            ->question(__('app.attention') . '!', __('app.are_you_sure'))
            ->confirm(__('app.delete_yes'))
            ->cancel(__('app.cancel'))
            ->hook([
                'ok' => [
                    'method' => 'disconnect',
                    'params' => $child_id,
                ],
            ])
            ->send();
    }

    public function disconnect(int $child_id): void
    {
        $child = Person::findOrFail($child_id);

        $key = $this->person->sex === 'm' ? 'father_id' : 'mother_id';
        $child->update([$key => null]);

        $this->toast()->success(__('app.disconnect'), e($child->name) . ' ' . __('app.disconnected') . '.')->send();

        $this->children = $this->person->fresh()->childrenNaturalAll();
        $this->loadAvailablePersons();

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

    // ------------------------------------------------------------------------------
    private function loadAvailablePersons(): void
    {
        $this->availablePersons = Person::where('id', '!=', $this->person->id)
            ->whereNull($this->person->sex === 'm' ? 'father_id' : 'mother_id')
            ->youngerThan($this->person->dob, $this->person->yob)
            ->olderThan($this->person->dod, $this->person->yod)
            ->orderBy('firstname')
            ->orderBy('surname')
            ->get()
            ->map(fn ($p): array => [
                'id'   => $p->id,
                'name' => $p->name . ' [' . ($p->sex === 'm' ? __('app.male') : __('app.female')) . '] ' . ($p->birth_formatted ? ' (' . $p->birth_formatted . ')' : ''),
            ]);
    }
}
