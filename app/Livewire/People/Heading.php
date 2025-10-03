<?php

declare(strict_types=1);

namespace App\Livewire\People;

use App\Models\Person;
use Illuminate\View\View;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

final class Heading extends Component
{
    use Interactions;

    // ------------------------------------------------------------------------------
    public Person $person;

    public bool $globalEditMode = false;

    private int $savedSections = 0;
    private int $expectedSections = 2; // Profile + Family

    // ------------------------------------------------------------------------------

    public function enableGlobalEdit(): void
    {
        if (!auth()->user()->hasPermission('person:update')) {
            return;
        }

        $this->globalEditMode = true;
        $this->savedSections = 0;
        
        // Debug
        logger('Global edit mode enabled: ' . $this->globalEditMode);
        
        $this->dispatch('global-edit-mode', editMode: true);
    }

    public function saveGlobalEdit(): void
    {
        $this->dispatch('save-all-sections');
    }

    public function cancelGlobalEdit(): void
    {
        $this->globalEditMode = false;
        $this->dispatch('global-edit-mode', editMode: false);
    }

    // ------------------------------------------------------------------------------
    protected $listeners = [
        'section-saved' => 'checkAllSaved',
    ];

    public function checkAllSaved(): void
    {
        $this->savedSections++;

        if ($this->savedSections >= $this->expectedSections) {
            $this->globalEditMode = false;
            $this->savedSections = 0;
            
            $this->toast()->success(__('app.save'), __('app.saved'))->send();
        }
    }

    // ------------------------------------------------------------------------------
    public function render(): View
    {
        return view('livewire.people.heading');
    }
}
