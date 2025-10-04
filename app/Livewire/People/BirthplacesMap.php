<?php

declare(strict_types=1);

namespace App\Livewire\People;

use App\Models\Person;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

final class BirthplacesMap extends Component
{
    public array $birthplaces = [];

    public int $totalPeople = 0;

    public int $peopleWithBirthplace = 0;

    // ------------------------------------------------------------------------------
    public function mount(): void
    {
        $this->loadBirthplaces();
    }

    // ------------------------------------------------------------------------------
    public function render(): View
    {
        return view('livewire.people.birthplaces-map');
    }

    // ------------------------------------------------------------------------------
    private function loadBirthplaces(): void
    {
        // Pobierz wszystkie miejsca urodzenia z liczbą osób
        $places = Person::select('pob', DB::raw('COUNT(*) as count'))
            ->whereNotNull('pob')
            ->where('pob', '!=', '')
            ->groupBy('pob')
            ->get();

        $this->totalPeople = Person::count();
        $this->peopleWithBirthplace = Person::whereNotNull('pob')->where('pob', '!=', '')->count();

        // Przygotuj dane dla mapy
        $this->birthplaces = $places->map(function ($place) {
            $people = Person::where('pob', $place->pob)
                ->select('id', 'firstname', 'surname', 'dob', 'yob')
                ->orderBy('surname')
                ->orderBy('firstname')
                ->get();

            return [
                'place' => $place->pob,
                'count' => $place->count,
                'people' => $people->map(fn ($person) => [
                    'id' => $person->id,
                    'name' => $person->name,
                    'birth' => $person->birth_formatted,
                ])->toArray(),
            ];
        })->toArray();
    }
}

